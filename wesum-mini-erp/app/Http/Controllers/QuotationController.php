<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\type;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (Auth::user()->hasRole('admin')) {
            $quotations = Quotation::where('type', 1)->with(['customer', 'user'])->latest()->get();
        } else {
            $quotations = Quotation::where('type', 1)->where('user_id', Auth::id())->with(['customer', 'user'])->latest()->get();
        }

        $quotations->load('challan', 'products');

        // return $quotations;

        return view('dashboard.qoutations.index', compact('quotations'));
    }

    /**
     * Display the pre-quotation form.
     */
    public function preQuotation()
    {
        if (Auth::user()->hasRole('admin')) {
            $quotations = Quotation::where('type', 2)->with(['customer', 'user'])->latest()->get();
        } else {
            $quotations = Quotation::where('type', 2)->where('user_id', Auth::id())->with(['customer', 'user'])->latest()->get();
        }

        $quotations->load('challan', 'products');
        return view('dashboard.qoutations.pre-quote', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $type = request()->query('type', 1);

        $nextQuotationNumber = Quotation::select('quotation_no')->latest()->first();

        $nextQuotationNumber = $nextQuotationNumber ? substr($nextQuotationNumber->quotation_no, 0,2) .'-0'. intval(substr($nextQuotationNumber->quotation_no, 3)) + 1 : "";

        return view('dashboard.qoutations.create', compact('type', 'nextQuotationNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuotationRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $customerData = $validatedData['customer'];
            $customer = null;

            if (isset($customerData['id']) && $customerData['id']) {

                $customer = Customer::findOrFail($customerData['id']);
            } else {
                $customer = Customer::create($customerData);
            }

            $quotationData = $validatedData['quotation'];
            $quotationData['user_id'] = Auth::id();
            $quotationData['customer_id'] = $customer->id;

            if ($request->has('quotation.type')) {
                $quotationData['type'] = $request['quotation']['type'];
            } else {
                $quotationData['type'] = 1;
            }


            $quotation = Quotation::create($quotationData);


            if (!empty($validatedData['product'])) {
                $quotation->products()->createMany($validatedData['product']);
            }

            DB::commit();

            if ($quotationData['type'] == 2) {
                return redirect()->route('pre.quotation')->with('success', 'Pre-Quotation created successfully!');
            } else {
                return redirect()->route('quotations.index')->with('success', 'Quotation created successfully!');
            }


        } catch (\Exception $e) {
            DB::rollBack();

            // Log the detailed error for debugging
            Log::error('Quotation creation failed: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());

            // Return the user to the form with their input and a generic error message
            return back()->withInput()->with('error', 'There was a problem creating the quotation. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quotation $quotation)
    {

        // Check if the quotation belongs to the authenticated user or is accessible by admin
        if (!Auth::user()->hasRole('admin') && $quotation->user_id != Auth::id()) {
            abort(403);
        }

        // Load the customer and products relationships for the quotation
        $quotation->load(['customer', 'products', 'challan']);

        $hasChallan = $quotation->challan ? true : false;

        return view('dashboard.qoutations.show', compact('quotation', 'hasChallan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quotation $quotation)
    {


        if (!Auth::user()->hasRole('admin') && $quotation->user_id != Auth::id()) {
            abort(403);
        }


        $quotation->load(['customer', 'products']);

        $hasChallan = $quotation->challan ? true : false;

        if ($hasChallan) {
            abort(403);
        }



        return view('dashboard.qoutations.edit', compact('quotation', 'hasChallan'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuotationRequest $request, Quotation $quotation) // Renamed for clarity
    {

        $quotation->load(['challan']);

        $hasChallan = $quotation->challan ? true : false;

        if ($hasChallan) {
            abort(403);
        }


        if (!Auth::user()->hasRole('admin') && $quotation->user_id != Auth::id()) {
            abort(403);
        }


        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // --- IMPROVED CUSTOMER LOGIC ---
            $customerData = $validatedData['customer'];
            $customerId = null;

            if (!empty($customerData['id'])) {

                $customerId = $customerData['id'];
            } else {

                $newCustomer = Customer::create($customerData);
                $customerId = $newCustomer->id;
            }
            // --- QUOTATION UPDATE LOGIC ---
            $quotationUpdateData = $validatedData['quotation'];
            if ($request->has('quotation.type')) {
                $quotationUpdateData['type'] = $request['quotation']['type'];
            }

            $quotationUpdateData['customer_id'] = $customerId; // Assign the resolved customer ID

            // Step 1: Update the parent Quotation model
            $quotation->update($quotationUpdateData);


            // --- CORRECTED & SECURE PRODUCT SYNCHRONIZATION LOGIC ---
            $originalProductIds = $quotation->products()->pluck('id')->all();
            $incomingProductIds = [];

            // Step 2: Loop through incoming products
            foreach ($validatedData['product'] as $productData) {
                if (isset($productData['id']) && !empty($productData['id'])) {

                    Product::where('id', $productData['id'])
                           ->where('quotation_id', $quotation->id)
                           ->update($productData);

                    $incomingProductIds[] = $productData['id'];
                } else {

                    $newProduct = $quotation->products()->create($productData);
                    $incomingProductIds[] = $newProduct->id;
                }
            }

            // Step 3: Determine which products were removed from the form and delete them
            $idsToDelete = array_diff($originalProductIds, $incomingProductIds);
            if (!empty($idsToDelete)) {
                Product::destroy($idsToDelete);
            }

            DB::commit();

            if ($quotationUpdateData['type'] ?? false) {
                return redirect()->route('quotations.index')->with('success', 'Quotation updated successfully!');
            } else {
                return redirect()->route('pre.quotation')->with('success', 'Pre-Quotation updated successfully!');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation update failed: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return back()->withInput()->with('error', 'There was a problem updating the quotation. Please try again.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {

        if (!Auth::user()->hasRole('admin') && $quotation->user_id != Auth::id()) {
            abort(403);
        }

        $quotation->load(['challan']);

        $hasChallan = $quotation->challan ? true : false;

        if ($hasChallan) {
            abort(403);
        }

        $quotation->delete();


        return redirect()->route('quotations.index')->with('success', 'Quotation Deleted successfully!');

    }

    public function searchCustomer(Request $request)
    {
        $query = $request->input('q');

        // If there is NO search query, return the 5 most recent customers.
        if (!$query) {
            $customers = Customer::latest() // Orders by `created_at` in descending order
                ->limit(5)
                ->get();
            return response()->json($customers);
        }

        // Otherwise, perform the search as before.
        $customers = Customer::where('customer_name', 'LIKE', "%{$query}%")
            ->orWhere('company_name', 'LIKE', "%{$query}%")
            ->orWhere('customer_no', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
