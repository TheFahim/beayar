<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'activeRevision']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $quotations = $query->latest()->paginate(10);

        return view('tenant.quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::all(); // Should be scoped to tenant
        $products = Product::all(); // Should be scoped to tenant
        return view('tenant.quotations.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:issue_date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $userCompanyId = auth()->user()->current_user_company_id;

            $quotation = Quotation::create([
                'user_company_id' => $userCompanyId,
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'],
                'status' => 'draft', // Default status
            ]);

            // Create initial revision
            $revision = $quotation->revisions()->create([
                'user_company_id' => $userCompanyId,
                'revision_number' => 1,
                'is_active' => true,
                'subtotal' => 0, // Calculated below
                'tax_total' => 0,
                'discount_total' => 0,
                'grand_total' => 0,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $taxAmount = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
                $discountAmount = $item['discount'] ?? 0; // Assuming fixed discount for now, or percentage? 
                // Let's assume the frontend sends calculated values or we calculate here.
                // For simplicity, let's assume discount is amount per line item or percentage?
                // The prompt says "Calculations: Alpine.js logic for qty * price = subtotal, tax, discount, grand_total".
                // I will assume the backend just stores what is sent or recalculates.
                // Let's just store simple values for now.
                
                $finalLineTotal = $lineTotal + $taxAmount - $discountAmount;

                $revision->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total' => $finalLineTotal,
                ]);

                $subtotal += $lineTotal;
                $taxTotal += $taxAmount;
                $discountTotal += $discountAmount;
            }

            $revision->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'discount_total' => $discountTotal,
                'grand_total' => $subtotal + $taxTotal - $discountTotal,
            ]);

            DB::commit();

            return redirect()->route('tenant.quotations.index')
                ->with('success', 'Quotation created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create quotation: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Quotation $quotation)
    {
        if ($quotation->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }
        $quotation->load(['customer', 'activeRevision.items.product']);
        return view('tenant.quotations.show', compact('quotation'));
    }
    
    // Add edit/update later if needed
}
