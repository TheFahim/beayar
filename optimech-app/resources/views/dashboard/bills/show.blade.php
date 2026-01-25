<x-dashboard.layout.default :title="'Bill - ' . ($bill->invoice_no ?? 'N/A')">
    <style>
        /* Hide the watermark on the screen view by default */
        .print-watermark {
            display: none;
        }

        /* Hide the fixed footer on screen; show only while printing */
        .print-footer {
            display: none;
        }

        .q-sign {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            margin-top: .75rem;
        }

        .q-sign .sig-box {
            width: 240px;
            text-align: center;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
            }

            /* Reserve space for pre-printed header and footer on every page */
            @page {
                margin: 42mm 0mm 10mm 0mm; /* top, right, bottom, left - adjust to match your letterhead */
            }

            .action-bar {
                display: none;
            }

            /* --- STACKING CONTEXT AND WATERMARK --- */
            #bill-invoice-container {
                position: relative;
            }

            .print-watermark {
                display: block;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                background: url("{{ asset('assets/images/logo.png') }}") center/contain no-repeat;
                width: 70vmin;
                height: 70vmin;
                opacity: 0.06;
                pointer-events: none;
                z-index: 0;
            }

            #bill-invoice-container>table {
                position: relative;
                z-index: 1;
            }

            /* thead repeats at the top of every printed page */
            #bill-invoice-container thead {
                display: table-header-group;
            }

            /* tfoot repeats at the bottom of every printed page */
            #bill-invoice-container tfoot {
                display: table-footer-group;
            }

            /* Hide the fixed positioned footer - using tfoot instead */
            .print-footer {
                display: none !important;
            }

            #products-table tbody tr {
                page-break-inside: avoid;
            }

            /* Prevent products table header from repeating on page breaks */
            #products-table thead {
                display: table-row-group;
            }

            /* Keep signature section together - don't split across pages */
            .signature-section {
                page-break-inside: avoid;
            }

            .q-sign {
                margin-top: 2mm;
            }
        }
    </style>

    <!-- Error Handling for Missing Data -->
    @if (!$bill->quotation || !$bill->quotation->customer)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">Missing Bill Data</h3>
                    <p class="text-sm text-red-700 mt-1">
                        This bill is missing associated challan or quotation data. Some information may not display
                        correctly.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Bill" />
    </x-dashboard.ui.bread-crumb>

    <div class="p-8 bg-gray-50 min-h-screen font-sans">
        <div class="flex justify-between p-4 action-bar">
            <button id="printBtn" type="button"
                class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                        d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                </svg>
                <span>&nbsp;&nbsp;Print</span>
            </button>
        </div>

        <div id="bill-invoice-container" class="max-w-4xl mx-auto bg-white p-3">
            <div class="print-watermark"></div>
            <table class="w-full">
                <thead>
                    <tr>
                        <td>
                            <!-- Bill TO header section - repeats on every printed page -->
                            <div class="mb-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <span class="block text-xs text-gray-600 font-semibold uppercase mb-1.5">Bill TO</span>
                                        <div class="text-xs text-gray-900 font-semibold">
                                            {{ $bill->quotation->customer->company->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            {{ $bill->quotation->customer->company->address ?? ($bill->quotation->customer->address ?? 'N/A') }}
                                        </div>
                                    </div>

                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="text-xs text-gray-700 font-bold">BILL</div>
                                            <div>
                                                <span
                                                    class="inline-block px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-md whitespace-nowrap">{{ $bill->invoice_no ?? '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-xs">
                                            <div class="text-gray-600">Bill Date</div>
                                            <div class="text-right font-semibold">
                                                {{ $bill->bill_date ? $bill->bill_date->format('d/m/Y') : 'N/A' }}</div>
                                            <div class="text-gray-600">Customer ID</div>
                                            <div class="text-right font-semibold">
                                                {{ $bill->quotation->customer->customer_no ?? 'N/A' }}</div>
                                            <div class="text-gray-600">PO No</div>
                                            <div class="text-right font-semibold">
                                                {{ $bill->quotation->po_no ?? 'N/A' }}</div>
                                            {{-- <div class="text-gray-600">Bill Type</div> --}}
                                            {{-- <div class="text-right font-semibold">
                                                {{ ucfirst($bill->bill_type == 'running' ? 'Installment' : $bill->bill_type) }}
                                            </div> --}}
                                            <div class="text-gray-600">PO Date</div>
                                            <div class="text-right font-semibold">
                                                {{ $bill->quotation->po_date ? date('d/m/Y', strtotime($bill->quotation->po_date)) : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <section>
                                <table id="products-table" class="w-full border-collapse text-sm">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 w-12 text-xs">
                                                SL</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-left w-1/4 text-xs">
                                                ITEM NAME</th>
                                            {{-- <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                DESCRIPTION</th> --}}
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                Specifications</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-16 text-xs">
                                                QTY</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-center w-20 text-xs">
                                                UNIT</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right text-xs">
                                                PRICE</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-24 text-xs">
                                                AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Conditional display: advance/running bills show full quotation products; regular shows bill items only --}}
                                        @php $isAdvanceOrRunning = ($bill->isAdvance() || $bill->isRunning()); @endphp

                                        @if ($isAdvanceOrRunning)
                                            {{-- Advance/Running: load products from associated or active quotation revision --}}
                                            @php
                                                $revision =
                                                    $bill->quotationRevision ??
                                                    $bill->quotation->revisions()->where('is_active', true)->first();
                                                $products = $revision
                                                    ? $revision->products()->with(['product', 'specification', 'brandOrigin'])->get()
                                                    : collect();
                                                $subtotal = 0;

                                                $shipping = $bill->quotationRevision->shipping;
                                                $discount = $bill->quotationRevision->discount_amount ?? 0;
                                            @endphp

                                            @if ($products->count() > 0)
                                                @php
                                                    $prevSpecId = null;
                                                    $rowspan = 1;
                                                    $skipSpec = false;
                                                @endphp
                                                @foreach ($products as $qp)
                                                    @php
                                                        $lineAmount = ($qp->unit_price ?? 0) * ($qp->quantity ?? 0);
                                                        $subtotal += $lineAmount;

                                                        $currentSpecId = $qp->specification_id;
                                                        if ($prevSpecId !== $currentSpecId) {
                                                            $rowspan = $products->where('specification_id', $currentSpecId)->count();
                                                            $prevSpecId = $currentSpecId;
                                                            $skipSpec = false;
                                                        } else {
                                                            $skipSpec = true;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td
                                                            class="border border-gray-400 p-2 text-center align-top text-xs">
                                                            {{ $loop->iteration }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 align-top font-bold text-xs">
                                                            {{ $qp->product->name ?? 'N/A' }}</td>
                                                        @if (!$skipSpec)
                                                        <td class="border border-gray-400 p-2 align-top text-xs" rowspan="{{ $rowspan }}">
                                                            {!! $qp->specification->description ?? '' !!}
                                                            @if ($qp->brandOrigin->name ?? false)
                                                                <div>
                                                                    <strong>Brand/Origin: </strong>
                                                                    {{ $qp->brandOrigin->name }}
                                                                </div>
                                                            @endif
                                                            @if ($qp->add_spec ?? false)
                                                                <div>
                                                                    {{ $qp->add_spec }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        @endif
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top text-xs">
                                                            {{ $qp->quantity ?? 0 }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-center align-top text-xs">
                                                            {{ $qp->unit ?? '' }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top text-xs">
                                                            {{ number_format($qp->unit_price ?? 0, 2) }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top font-bold text-xs">
                                                            {{ number_format($lineAmount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">Subtotal</td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">{{ number_format($subtotal ?? 0, 2) }}</td>
                                                </tr>

                                                @if ($discount)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Discount</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">
                                                            {{ number_format($discount ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if ($discount)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Subtotal Less Discount</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">
                                                            {{ number_format($subtotal - $discount ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if ($shipping)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Shipping</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">{{ number_format($shipping ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif

                                                @php
                                                    $vatPercentage = (float) ($revision->vat_percentage ?? 0);
                                                    $vatAmount =
                                                        $revision->vat_amount ??
                                                        (($subtotal ?? 0) * $vatPercentage) / 100;
                                                @endphp
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">VAT ({{ number_format($vatPercentage, 2) }}%)
                                                    </td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">{{ number_format($vatAmount ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">Total
                                                    </td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">
                                                        {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                                </tr>

                                                @foreach ($histories as $history)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top font-bold text-xs"
                                                            colspan="6">
                                                            @if ($loop->last)
                                                                Billing Amount
                                                            @else
                                                                {{ $loop->index != 0 ? ($bill->bill_type == 'running' ? 'Installment ' . $loop->index : $bill->bill_type) : 'Advance' }}
                                                            @endif

                                                            ({{ $history->bill_percentage }}%)
                                                            @if ($history->payment_received_date)
                                                                ({{ $history->payment_received_date ? date('d-m-Y', strtotime($history->payment_received_date)) : '' }})
                                                            @endif
                                                        </td>
                                                        <td class="border border-gray-400 text-right align-top font-bold text-xs"
                                                            colspan="2">
                                                            {{ number_format($history->bill_amount ?? 'Error', 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">Due
                                                    </td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">
                                                        {{ number_format($bill->due ?? 'Error', 2) }}
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="7"
                                                        class="border border-gray-400 p-8 text-center text-gray-500">
                                                        <div class="flex flex-col items-center justify-center">
                                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <p class="text-sm font-medium text-gray-600">No quotation
                                                                products available</p>
                                                            <p class="text-xs text-gray-500 mt-1">Ensure an active
                                                                revision exists for this quotation</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @else
                                            {{-- Regular: show only items included in this bill --}}
                                            @php
                                                $items = $bill->items()->with(['quotationProduct.specification', 'quotationProduct.brandOrigin', 'quotationProduct.product'])->get();
                                            @endphp
                                            @if ($items->count() > 0)
                                                @php
                                                    $subtotal = 0;
                                                    $prevSpecId = null;
                                                    $rowspan = 1;
                                                    $skipSpec = false;
                                                @endphp
                                                @foreach ($items as $item)
                                                    @php
                                                        $subtotal += ($item->bill_price ?? 0);

                                                        $currentSpecId = $item->quotationProduct->specification_id ?? null;
                                                        if ($prevSpecId !== $currentSpecId) {
                                                            $rowspan = $items->filter(function($i) use ($currentSpecId) {
                                                                return ($i->quotationProduct->specification_id ?? null) === $currentSpecId;
                                                            })->count();
                                                            $prevSpecId = $currentSpecId;
                                                            $skipSpec = false;
                                                        } else {
                                                            $skipSpec = true;
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td
                                                            class="border border-gray-400 p-2 text-center align-top text-xs">
                                                            {{ $loop->iteration }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 align-top font-bold text-xs">
                                                            {{ $item->quotationProduct->product->name ?? 'N/A' }}</td>
                                                        @if (!$skipSpec)
                                                        <td class="border border-gray-400 align-top text-xs p-2" rowspan="{{ $rowspan }}">
                                                            {!! $item->quotationProduct->specification->description ?? '' !!}
                                                            @if ($item->quotationProduct->brandOrigin->name ?? false)
                                                                <div>
                                                                    <strong>Brand/Origin: </strong>
                                                                    {{ $item->quotationProduct->brandOrigin->name }}
                                                                </div>
                                                            @endif
                                                            @if ($item->quotationProduct->add_spec ?? false)
                                                                <div>
                                                                    {{ $item->quotationProduct->add_spec }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        @endif
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top text-xs">
                                                            {{ $item->quantity }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-center align-top text-xs">
                                                            {{ $item->quotationProduct->unit ?? '' }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top text-xs">
                                                            {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                                        <td
                                                            class="border border-gray-400 p-2 text-right align-top font-bold text-xs">
                                                            {{ number_format($item->bill_price ?? 0, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">Subtotal</td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">{{ number_format($subtotal ?? 0, 2) }}</td>
                                                </tr>
                                                @php
                                                    $rev =
                                                        $bill->quotationRevision ??
                                                        $bill->quotation
                                                            ->revisions()
                                                            ->where('is_active', true)
                                                            ->first();
                                                    $vatPercentage = (float) ($rev->vat_percentage ?? 0);
                                                    $vatAmount =
                                                        $rev->vat_amount ?? (($subtotal ?? 0) * $vatPercentage) / 100;
                                                @endphp

                                                @if ($bill->discount ?? 0 > 0)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Discount</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">
                                                            {{ number_format($bill->discount ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if ($bill->shipping ?? 0 > 0)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Shipping</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">
                                                            {{ number_format($bill->shipping ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif

                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">VAT ({{ number_format($vatPercentage, 2) }}%)
                                                    </td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">{{ number_format($vatAmount ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="6">Total
                                                    </td>
                                                    <td class="border border-gray-400 text-right align-top text-xs"
                                                        colspan="2">
                                                        {{ number_format($bill->total_amount ?? 0, 2) }}</td>
                                                </tr>
                                                @if (($bill->bill_amount - $bill->total_amount) ?? 0 != 0)
                                                    <tr>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="6">Round Up</td>
                                                        <td class="border border-gray-400 text-right align-top text-xs"
                                                            colspan="2">
                                                            {{ number_format($bill->bill_amount - $bill->total_amount ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="border border-gray-400 text-right align-top font-bold text-xs"
                                                        colspan="6">Bill Amount</td>
                                                    <td class="border border-gray-400 text-right align-top font-bold text-xs"
                                                        colspan="2">
                                                        {{ number_format($bill->bill_amount ?? 'Error', 2) }}
                                                    </td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td colspan="7"
                                                        class="border border-gray-400 p-8 text-center text-gray-500">
                                                        <div class="flex flex-col items-center justify-center">
                                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <p class="text-sm font-medium text-gray-600">No items found
                                                                for this bill</p>
                                                            <p class="text-xs text-gray-500 mt-1">Relevant data may be
                                                                missing</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endif
                                    </tbody>
                                </table>

                            </section>

                            <section class="mt-4">
                                <div class="w-1/2 border border-gray-300 rounded-lg p-2.5 bg-gray-50">
                                    <h2 class="text-blue-800 font-bold text-xs">Enclosed with Invoice</h2>
                                    <ol class="list-decimal pl-5 mt-1 space-y-1 text-xs">
                                        <li>PO Copy</li>
                                        <li>Materials Delivery Challan</li>
                                        <li>Mushak 6.3 Documents</li>
                                    </ol>
                                </div>
                                <div class="flex justify-between items-start mt-6 mb-2 signature-section">
                                    <div class="w-5/12">
                                        <div class="text-xs font-bold mb-1 uppercase text-gray-700">Submitted by</div>
                                        <div class="h-16"></div>
                                        <div class="border-b-2 border-gray-600 mb-2"></div>
                                        <div class="text-xs text-gray-800 leading-snug">
                                            <div class="font-semibold">Mohammad Ataur Rahman</div>
                                            <div>Proprietor</div>
                                            <div class="font-bold">OptiMech Project Solution</div>
                                            <div>Mobile: 01841176747</div>
                                            <div>Email: ataur@optimech.com.bd</div>
                                        </div>
                                    </div>
                                    <div class="w-5/12 text-right">
                                        <div class="text-xs font-bold mb-1 uppercase text-gray-700">Received by</div>
                                        <div class="h-16"></div>
                                        <div class="border-b-2 border-gray-600 mb-2"></div>
                                        <div class="text-xs text-gray-900 leading-snug">
                                            <div class="font-bold">{{ $bill->quotation->customer->company->name ?? '' }}</div>

                                        </div>
                                    </div>
                                </div>
                            </section>
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <td>
                            <!-- Footer space is now handled by @page margin-bottom -->
                        </td>
                    </tr>
                </tfoot>
                <!-- Print-only fixed footer to anchor at page bottom -->
                <div class="print-footer h-10"></div>
            </table>
        </div>
    </div>
    <script>
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#bill-invoice-container').printThis({
                importCSS: true,
                importStyle: true,
                copyTagStyles: true,
                printDelay: 500,
                beforePrintEvent: function() {
                    var footer = document.querySelector('.print-footer');
                    var h = footer ? footer.offsetHeight : 0;
                    document.body.style.marginBottom = (h + 8) + 'px';
                },
                afterPrint: function() {
                    document.body.style.marginBottom = '';
                }
            });
        });
    </script>
</x-dashboard.layout.default>
