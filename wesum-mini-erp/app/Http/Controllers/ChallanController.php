<?php

namespace App\Http\Controllers;

use App\Models\Challan;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChallanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()->hasRole('admin')) {
            $challans = Challan::with(['quotation', 'quotation.customer', 'quotation.products', 'bill'])->latest()->get();


        } else {
            $challans = Challan::with(['quotation', 'quotation.customer', 'bill'])
                ->whereHas('quotation', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->latest()
                ->get();
        }

        $challans->load('quotation.products');

        return view('dashboard.challans.index', compact('challans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $quotationId = $request->query('quotation_id');

        $quotation = Quotation::find($quotationId);

        if (!$quotation || $quotation->type != 1) {
            abort(404, 'Quotation not found');
        }

        if (!Auth::user()->hasRole('admin') && $quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $quotation->load(['customer', 'products']);

        $hasChallan = $quotation->challan ? true : false;

        if ($hasChallan) {
            abort(403);
        }

        return view('dashboard.challans.create', compact('quotation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'challan_no' => 'required|string|max:255|unique:challans,challan_no',
            'po_no' => 'required|string|max:255|unique:challans,po_no',
            'delivery_date' => 'required|date_format:d/m/Y',
        ]);

        $challan = Challan::create([
            'quotation_id' => $validated['quotation_id'],
            'challan_no' => $validated['challan_no'],
            'po_no' => $validated['po_no'],
            'delivery_date' => $validated['delivery_date'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $validated['delivery_date'])->format('Y-m-d') : null,
        ]);

        return redirect()->route('challans.show', $challan->id)->with('success', 'Challan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Challan $challan)
    {



        $challan->load(['quotation', 'quotation.products', 'quotation.customer']);

        if (!Auth::user()->hasRole('admin') && $challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }


        $hasBill = $challan->bill ? true : false;


        return view('dashboard.challans.show', compact('challan', 'hasBill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Challan $challan)
    {
        $challan->load(['quotation', 'quotation.customer', 'bill']);

        if (!Auth::user()->hasRole('admin') && $challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }


        $hasBill = $challan->bill ? true : false;


        return view('dashboard.challans.edit', compact('challan', 'hasBill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Challan $challan)
    {
        $validated = $request->validate([
            'challan_no' => ['required', Rule::unique('challans', 'challan_no')->ignore($challan->id)],
            'po_no' => ['required', Rule::unique('challans', 'po_no')->ignore($challan->id)],
            'delivery_date' => 'required|date_format:d/m/Y',
            'product' => 'required|array',
            'product.*.id' => 'required|exists:products,id',
            'product.*.buying_price' => 'nullable|numeric',
            'product.*.remarks' => 'nullable|string|max:255',
        ]);

        $challan->load('quotation');

        if (!Auth::user()->hasRole('admin') && $challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }


        $challan->update([
            'challan_no' => $validated['challan_no'],
            'po_no' => $validated['po_no'],
            'delivery_date' => $validated['delivery_date'] ? \Carbon\Carbon::createFromFormat('d/m/Y', $validated['delivery_date'])->format('Y-m-d') : null,
        ]);

        foreach ($validated['product'] as $productData) {
            Product::where('id', $productData['id'])->update([
                'remarks' => $productData['remarks'] ?? null,
            ]);
        }

        return redirect()->route('challans.show', $challan->id)->with('success', 'Challan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Challan $challan)
    {
        $challan->load(['bill', 'quotation']);
        if (!Auth::user()->hasRole('admin') && $challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $hasBill = $challan->bill ? true : false;

        if ($hasBill) {
            abort(403);
        }

        $challan->delete();

        return redirect()->route('challans.index')->with('success', 'Challan Deleted successfully.');


    }
}
