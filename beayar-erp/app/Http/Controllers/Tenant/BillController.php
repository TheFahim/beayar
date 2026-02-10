<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdvanceBillRequest;
use App\Http\Requests\StoreRegularBillRequest;
use App\Http\Requests\StoreRunningBillRequest;
use App\Http\Requests\UpdateAdvanceBillRequest;
use App\Http\Requests\UpdateRegularBillRequest;
use App\Models\Bill;
use App\Models\Challan;
use App\Models\Quotation;
use App\Services\BillingService;
use App\Services\InvoiceNumberGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Controller responsible for billing operations including listing, creation,
 * smart bill workflow from quotations, and CRUD management for bills.
 */
class BillController extends Controller
{
    protected $billingService;

    protected $invoiceNumberGenerator;

    public function __construct(BillingService $billingService, InvoiceNumberGenerator $invoiceNumberGenerator)
    {
        $this->billingService = $billingService;
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $bills = Bill::with(['quotation', 'user'])->latest()->paginate(15);

            $bills->load(['quotation.revisions' => function ($query) {
                $query->where('is_active', true)->latest()->limit(1);
            }]);

            // Calculate metrics based on all bills, not just the paginated ones
            // This might be expensive, so we might want to cache it or optimize it
            $allBills = Bill::select('id', 'quotation_id', 'bill_date', 'total_amount', 'due')
                ->withSum('receivedBills as paid', 'amount')
                ->get();

            $latestByQuotation = $allBills
                ->sortByDesc('id')
                ->sortByDesc('bill_date')
                ->groupBy('quotation_id')
                ->map(function ($group) {
                    return optional($group->first())->id;
                });

            $totalAmountUniqueByQuotation = $allBills->groupBy('quotation_id')->map(function ($group) use ($latestByQuotation) {
                $qid = optional($group->first())->quotation_id;
                $latestId = $qid !== null ? ($latestByQuotation[$qid] ?? null) : null;
                $latest = $latestId ? $group->firstWhere('id', $latestId) : $group->sortByDesc('bill_date')->sortByDesc('id')->first();

                return (float) ($latest->total_amount ?? 0);
            })->sum();

            $totalDueUniqueByQuotation = $allBills->groupBy('quotation_id')->map(function ($group) use ($latestByQuotation) {
                $qid = optional($group->first())->quotation_id;
                $latestId = $qid !== null ? ($latestByQuotation[$qid] ?? null) : null;
                $latest = $latestId ? $group->firstWhere('id', $latestId) : $group->sortByDesc('bill_date')->sortByDesc('id')->first();

                return (float) ($latest->due ?? 0);
            })->sum();

            $metrics = [
                'total_bills' => (int) $allBills->count(),
                'total_amount_unique_by_quotation' => (float) $totalAmountUniqueByQuotation,
                'total_paid' => (float) $allBills->sum('paid'),
                'total_due' => (float) $allBills->sum('due'),
                'total_due_unique_by_quotation' => (float) $totalDueUniqueByQuotation,
            ];

            return view('tenant.bills.index', compact('bills', 'latestByQuotation', 'metrics'));
        } catch (\Throwable $e) {
            Log::error('Failed to load bills index', [
                'message' => $e->getMessage(),
            ]);

            $bills = collect();
            $latestByQuotation = collect();
            $metrics = [
                'total_bills' => 0,
                'total_amount_unique_by_quotation' => 0,
                'total_paid' => 0,
                'total_due' => 0,
                'total_due_unique_by_quotation' => 0,
            ];

            return view('tenant.bills.index', compact('bills', 'latestByQuotation', 'metrics'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $quotationId = $request->query('quotation_id');
        $parentBillId = $request->query('parent_bill_id');

        if (! $quotationId) {
            abort(404, 'Quotation ID is required');
        }

        $quotation = Quotation::with(['customer.company', 'revisions' => function ($query) {
            $query->with(['products.product', 'products.specification', 'products.challanProducts', 'products.brandOrigin']);
        }])->findOrFail($quotationId);

        $latestBill = Bill::where('quotation_id', $quotationId)
            ->orderBy('bill_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        if ($latestBill && (float) ($latestBill->due ?? 0) <= 0) {
            return redirect()->route('tenant.quotations.index')
                ->with('error', 'Billing is complete for this quotation. No further bills can be created.');
        }

        $activeRevision = $this->getActiveRevision($quotation);
        if (! $activeRevision || ($activeRevision->saved_as ?? null) !== 'quotation') {
            return redirect()->route('tenant.quotations.index')
                ->with('error', 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.');
        }
        $challans = $this->getRevisionChallans($activeRevision);
        $this->calculateRemainingQuantitiesForChallanProducts($challans);

        $existingAdvanceBill = Bill::where('quotation_id', $quotationId)
            ->where('bill_type', 'advance')
            ->latest()
            ->first();

        if ($parentBillId) {
            $parentBill = Bill::findOrFail($parentBillId);
            if ($parentBill->quotation_id != $quotationId) {
                abort(404, 'Parent bill does not belong to this quotation');
            }
            if ($parentBill->bill_type !== 'advance') {
                abort(404, 'Parent bill must be an advance bill');
            }

            return $this->createRunningView($quotation, $parentBill);
        }

        // Regular billing view selection is based solely on advance bill existence

        if ($challans->isNotEmpty()) {
            return $this->createRegularView($quotation, $activeRevision, $challans);
        }

        return $this->createAdvanceView($quotation, $activeRevision, null);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegularBillRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->billingService->createBill(array_merge($validated, [
                'bill_type' => 'regular',
            ]));

            return redirect()->route('tenant.bills.index')->with('success', 'Bill created successfully.');

        } catch (ValidationException $e) {
            Log::error(''.$e->getMessage());

            return redirect()->back()->withInput()->withErrors($e->errors());

        } catch (\Throwable $e) {
            Log::error('Failed to create bill', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => optional(auth())->id(),
                'input' => Arr::except($validated, []),
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to create bill. '.$e->getMessage());
        }
    }

    private function getActiveRevision(Quotation $quotation)
    {
        return $quotation->revisions()->where('is_active', true)->with(['products.product'])->first();
    }

    private function getRevisionChallans($activeRevision)
    {
        if (! $activeRevision) {
            return collect();
        }

        return Challan::with([
            'products.quotationProduct.product',
            'revision.products.product',
        ])->where('quotation_revision_id', $activeRevision->id)->get();
    }

    public function createAdvanceView(Quotation $quotation, $activeRevision, $existingAdvanceBill = null)
    {
        $nextInvoiceNo = $this->invoiceNumberGenerator->generate($quotation);

        return view('tenant.bills.create-advance', compact('quotation', 'activeRevision', 'existingAdvanceBill', 'nextInvoiceNo'));
    }

    public function createRegularView(Quotation $quotation, $activeRevision, $challans)
    {
        $this->calculateRemainingQuantitiesForChallanProducts($challans);
        $quotation->load('bills');

        $nextInvoiceNo = $this->invoiceNumberGenerator->generate($quotation);

        return view('tenant.bills.create-regular', compact('quotation', 'activeRevision', 'challans', 'nextInvoiceNo'));
    }

    public function createRunningView(Quotation $quotation, Bill $parentBill)
    {
        $parentBill->load('children');
        $quotation->load('bills');

        $nextInvoiceNo = $this->invoiceNumberGenerator->generate($quotation);

        return view('tenant.bills.create-running', compact('quotation', 'parentBill', 'nextInvoiceNo'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        $bill->load([
            'quotation.customer.company',
            'quotationRevision',
            'parent',
            'children',
            'challans.products.quotationProduct.product',
            'items.quotationProduct.product',
            'items.challanProduct.quotationProduct.product',
        ]);

        $histories = Bill::where('quotation_id', $bill->quotation_id)
            ->where('id', '<=', $bill->id)
            ->get();

        $subtotal = $bill->items->sum('bill_price');

        return view('tenant.bills.show', compact('bill', 'subtotal', 'histories'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            abort(403, 'Only the latest bill in the quotation can be edited');
        }
        if ($bill->bill_type == 'advance') {
            return $this->editAdvance($bill);
        }
        if ($bill->bill_type == 'running') {
            return $this->editRunning($bill);
        }

        return $this->editRegular($bill);
    }

    public function editAdvance(Bill $bill)
    {
        $bill->load(['quotation.customer.company', 'quotationRevision.products.product']);

        $quotation = $bill->quotation;
        $activeRevision = $bill->quotationRevision ?: $this->getActiveRevision($quotation);

        return view('tenant.bills.edit-advance', compact('bill', 'quotation', 'activeRevision'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            abort(403, 'Only the latest bill in the quotation can be edited');
        }
        $validated = $request->validate([
            'invoice_no' => ['required', 'string', 'max:255', Rule::unique('bills', 'invoice_no')->ignore($bill->id)],
            'bill_date' => 'required|date',
            'payment_received_date' => 'nullable|date',
            'status' => 'required|in:draft,issued,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $bill->update([
            'invoice_no' => $validated['invoice_no'],
            'bill_date' => Carbon::createFromFormat('d/m/Y', $validated['bill_date'])->format('Y-m-d'),
            'payment_received_date' => $validated['payment_received_date'] ? Carbon::createFromFormat('d/m/Y', $validated['payment_received_date'])->format('Y-m-d') : null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tenant.bills.index')->with('success', 'Bill updated successfully.');
    }

    public function updateAdvance(UpdateAdvanceBillRequest $request, Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            abort(403, 'Only the latest bill in the quotation can be edited');
        }
        $validated = $request->validated();

        $billDate = Carbon::createFromFormat('d/m/Y', $validated['bill_date'])->format('Y-m-d');
        $paymentDate = ! empty($validated['payment_received_date'])
            ? Carbon::createFromFormat('d/m/Y', $validated['payment_received_date'])->format('Y-m-d')
            : null;

        $billPercentage = $validated['bill_percentage'] ?? null;
        $billAmount = $validated['bill_amount'] ?? ($validated['total_amount'] ?? 0);
        $due = $validated['due'] ?? 0;

        $bill->update([
            'invoice_no' => $validated['invoice_no'],
            'bill_date' => $billDate,
            'payment_received_date' => $paymentDate,
            'bill_type' => 'advance',
            'quotation_id' => $bill->quotation_id,
            'quotation_revision_id' => $validated['quotation_revision_id'] ?? $bill->quotation_revision_id,
            'bill_percentage' => $billPercentage,
            'total_amount' => $billAmount,
            'bill_amount' => $billAmount,
            'due' => $due,
            'notes' => $validated['notes'] ?? $bill->notes,
        ]);

        if ($validated['po_no']) {
            $quotation = $bill->quotation;
            $quotation->update([
                'po_no' => $validated['po_no'],
            ]);
        }

        return redirect()->route('tenant.bills.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            Log::warning('Blocked bill deletion: not latest in quotation', [
                'bill_id' => $bill->id,
                'quotation_id' => $bill->quotation_id,
            ]);
            abort(403, 'Only the latest bill in the quotation can be deleted');
        }

        if ($bill->isAdvance() && $bill->children()->exists()) {
            Log::warning('Blocked advance bill deletion with running children', [
                'bill_id' => $bill->id,
                'quotation_id' => $bill->quotation_id,
            ]);
            abort(403, 'Cannot delete an advance bill that has running installments');
        }

        if ($bill->isRunning()) {
            $parent = $bill->parent;
            if (! $parent) {
                Log::warning('Blocked running bill deletion without valid parent', [
                    'bill_id' => $bill->id,
                    'quotation_id' => $bill->quotation_id,
                ]);
                abort(403, 'Invalid parent relationship for running bill');
            }

            $latestChild = $parent->children()
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if (optional($latestChild)->id !== $bill->id) {
                Log::warning('Blocked bill deletion: not latest child', [
                    'bill_id' => $bill->id,
                    'parent_bill_id' => $parent->id,
                    'quotation_id' => $bill->quotation_id,
                ]);
                abort(403, 'Only the latest running installment can be deleted');
            }
        }

        $bill->delete();

        return redirect()->route('tenant.bills.index')->with('success', 'Bill deleted successfully.');
    }

    /**
     * Search bills for autocomplete
     */
    public function search(Request $request)
    {
        $bills = Bill::select('id', 'invoice_no as text')
            ->where('invoice_no', 'like', '%'.$request->query('q').'%')
            ->limit(10)
            ->get();

        return response()->json($bills);
    }

    /**
     * Get billing data for dashboard
     */
    public function getBillingData(Request $request)
    {
        $bills = Bill::select(
            DB::raw('SUM(total_amount) as total_bill'),
            DB::raw('SUM(paid) as total_paid'),
            DB::raw('SUM(due) as total_due')
        )
            ->where('status', '!=', 'cancelled')
            ->get();

        return response()->json($bills);
    }

    /**
     * Show the appropriate bill creation form based on quotation status (smart workflow)
     */
    public function createFromQuotation(Quotation $quotation)
    {
        $latestBill = Bill::where('quotation_id', $quotation->id)
            ->orderBy('bill_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        if ($latestBill && (float) ($latestBill->due ?? 0) <= 0) {
            return redirect()->route('tenant.quotations.index')
                ->with('error', 'Billing is complete for this quotation. No further bills can be created.');
        }
        $activeRevision = $quotation->revisions()
            ->where('is_active', 1)
            ->first();

        if (! $activeRevision) {
            return redirect()->route('tenant.quotations.index')
                ->with('error', 'No active revision found for this quotation.');
        }

        // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
        if (($activeRevision->saved_as ?? null) !== 'quotation') {
            return redirect()->route('tenant.quotations.index')
                ->with('error', 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.');
        }

        $challans = Challan::with([
            'products' => function ($query) {
                $query->with(['quotationProduct', 'product']);
            },
        ])
            ->where('quotation_revision_id', $activeRevision->id)
            ->get();

        $advanceBills = $quotation->bills()
            ->with('children')
            ->where('bill_type', 'advance')
            ->get();

        if ($advanceBills->isNotEmpty()) {
            $this->calculateAdvanceBillRemaining($advanceBills);

            $parentBill = $advanceBills->first();
            $nextInvoiceNo = $this->invoiceNumberGenerator->generate($quotation);

            return view('tenant.bills.create-running', compact('quotation', 'parentBill', 'nextInvoiceNo'));
        }

        if ($challans->isNotEmpty()) {
            $this->calculateRemainingQuantitiesForChallanProducts($challans);

            return view('tenant.bills.regular', compact('quotation', 'activeRevision', 'challans'));
        }

        return $this->createAdvanceView($quotation, $activeRevision);
    }

    public function storeAdvanceBill(StoreAdvanceBillRequest $request, Quotation $quotation)
    {
        $validated = $request->validated();

        try {

            $activeRevision = $quotation->revisions()
                ->where('is_active', 1)
                ->first();

            if (! $activeRevision && $validated['bill_type'] === 'advance') {
                throw ValidationException::withMessages([
                    'quotation' => 'No active revision found for advance bill.',
                ]);
            }

            // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
            if (($activeRevision->saved_as ?? null) !== 'quotation') {
                throw ValidationException::withMessages([
                    'quotation' => 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.',
                ]);
            }

            $payload = [
                'bill_type' => 'advance',
                'quotation_id' => $quotation->id,
                'quotation_revision_id' => $validated['quotation_revision_id'] ?? $activeRevision?->id,
                'invoice_no' => $validated['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $validated['bill_date'])->format('Y-m-d'),
                'notes' => $validated['notes'] ?? '',

            ];

            if (! empty($validated['payment_received_date'])) {
                $payload['payment_received_date'] = Carbon::createFromFormat('d/m/Y', $validated['payment_received_date'])->format('Y-m-d');
            }

            $payload['bill_percentage'] = $validated['bill_percentage'] ?? 100;
            $payload['total_amount'] = $validated['total_amount'] ?? 0;
            $payload['bill_amount'] = $validated['bill_amount'] ?? 0;
            $payload['due'] = $validated['due'];

            $bill = $this->billingService->createAdvance($payload);

            if ($validated['po_no']) {
                $quotation->update([
                    'po_no' => $validated['po_no'],
                ]);
            }

            return redirect()->route('tenant.bills.show', $bill)
                ->with('success', 'Bill created successfully.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create bill: '.$e->getMessage());
        }
    }

    public function storeRunningBill(StoreRunningBillRequest $request, Quotation $quotation)
    {
        $validated = $request->validated();

        try {
            // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
            $activeRevision = $quotation->revisions()->where('is_active', 1)->first();
            if (! $activeRevision || ($activeRevision->saved_as ?? null) !== 'quotation') {
                throw ValidationException::withMessages([
                    'quotation' => 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.',
                ]);
            }
            $this->billingService->createRunning(array_merge($validated, [
                'quotation_id' => $quotation->id,
                'total_amount' => $quotation->revisions()->where('is_active', 1)->first()->total ?? 0,
            ]));

            return redirect()->route('tenant.bills.index')
                ->with('success', 'Running bill created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create running bill: '.$e->getMessage());
        }
    }

    private function calculateRemainingQuantitiesForChallanProducts($challans, $excludeBillId = null): void
    {
        foreach ($challans as $challan) {
            foreach ($challan->products as $challanProduct) {
                $billedQuantity = DB::table('bill_items')
                    ->join('bill_challans', 'bill_items.bill_challan_id', '=', 'bill_challans.id')
                    ->join('bills', 'bill_challans.bill_id', '=', 'bills.id')
                    ->where('bill_items.challan_product_id', $challanProduct->id)
                    ->where('bills.bill_type', 'regular')
                    ->where('bills.status', '!=', 'cancelled')
                    ->when($excludeBillId, function ($query) use ($excludeBillId) {
                        $query->where('bills.id', '!=', $excludeBillId);
                    })
                    ->sum('bill_items.quantity');

                $challanProduct->remaining_quantity = $challanProduct->quantity - $billedQuantity;
                $challanProduct->unit_price = optional($challanProduct->quotationProduct)->unit_price ?? 0;
                $challanProduct->line_total = $challanProduct->remaining_quantity * $challanProduct->unit_price;
            }
        }
    }

    private function calculateAdvanceBillRemaining($advanceBills): void
    {
        foreach ($advanceBills as $bill) {
            $billedThroughRunning = $bill->children()
                ->where('bill_type', 'running')
                ->sum('total_amount');

            $bill->remaining_amount = max(0, $bill->total_amount - $billedThroughRunning);
            $bill->bill_percentage = $billedThroughRunning > 0
                ? ($billedThroughRunning / $bill->total_amount) * 100
                : 0;
        }
    }

    private function validateBusinessRulesFromQuotation(array $validated, Quotation $quotation): void
    {
        if ($validated['bill_type'] === 'advance') {
            $hasChallans = Challan::whereHas('revision', function ($query) use ($quotation) {
                $query->where('quotation_id', $quotation->id)->where('is_active', 1);
            })->exists();

            if ($hasChallans) {
                throw ValidationException::withMessages([
                    'bill_type' => 'Cannot create advance bill - challans already exist for this quotation.',
                ]);
            }

            $existingAdvance = $quotation->bills()->where('bill_type', 'advance')->exists();
            if ($existingAdvance) {
                throw ValidationException::withMessages([
                    'bill_type' => 'An advance bill already exists for this quotation.',
                ]);
            }
        }

        if ($validated['bill_type'] === 'running') {
            $parentBill = $quotation->bills()
                ->where('id', $validated['parent_bill_id'])
                ->where('bill_type', 'advance')
                ->first();

            if (! $parentBill) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Invalid parent bill for running installment.',
                ]);
            }

            $remainingAmount = $parentBill->total_amount - $parentBill->children()
                ->where('bill_type', 'running')
                ->sum('total_amount');

            if (($validated['installment_amount'] ?? 0) > $remainingAmount) {
                throw ValidationException::withMessages([
                    'installment_amount' => "Installment amount exceeds remaining balance ({$remainingAmount}).",
                ]);
            }
        }
    }

    public function editRunning(Bill $bill)
    {
        $bill->load(['quotation', 'parent.children']);
        $quotation = $bill->quotation;
        $parentBill = $bill->parent ?: $bill;
        $quotation->load('bills');

        return view('tenant.bills.edit-running', compact('bill', 'quotation', 'parentBill'));
    }

    public function updateRunning(Request $request, Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            abort(403, 'Only the latest bill in the quotation can be edited');
        }
        $validated = $request->validate([
            'invoice_no' => ['required', 'string', 'max:255', Rule::unique('bills', 'invoice_no')->ignore($bill->id)],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'bill_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->billingService->updateRunning($bill, array_merge($validated, [
                'quotation_id' => $bill->quotation_id,
            ]));

            return redirect()->route('tenant.bills.index')->with('success', 'Running bill updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update running bill: '.$e->getMessage());
        }
    }

    private function isLatestBillForQuotation(Bill $bill): bool
    {
        $latest = Bill::where('quotation_id', $bill->quotation_id)
            ->orderBy('bill_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return optional($latest)->id === $bill->id;
    }

    public function editRegular(Bill $bill)
    {
        $bill->load(['quotation']);

        $quotation = $bill->quotation;
        $activeRevision = $this->getActiveRevision($quotation);
        $challans = $this->getRevisionChallans($activeRevision);
        $this->calculateRemainingQuantitiesForChallanProducts($challans, $bill->id);
        $bill->load('items');

        return view('tenant.bills.edit-regular', compact('bill', 'quotation', 'activeRevision', 'challans'));
    }

    public function updateRegular(UpdateRegularBillRequest $request, Bill $bill)
    {
        if (! $this->isLatestBillForQuotation($bill)) {
            abort(403, 'Only the latest bill in the quotation can be edited');
        }

        $validated = $request->validated();

        try {
            $this->billingService->updateRegular($bill, array_merge($validated, [
                'bill_type' => 'regular',
            ]));

            return redirect()->route('tenant.bills.index')->with('success', 'Bill updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('Failed to update regular bill', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to update bill. '.$e->getMessage());
        }
    }
}
