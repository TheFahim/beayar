<x-dashboard.layout.default :title="($activeRevision->revision_no !== 'R00' ? 'Revised ' : '') . 'Commercial Quotation - ' . $quotation->quotation_no">
    <div x-data="{ isTechnical: false }" x-init="$watch('isTechnical', value => document.title = (value ? '{{ $activeRevision->revision_no !== 'R00' ? 'Revised ' : '' }}Technical Quotation - ' : '{{ $activeRevision->revision_no !== 'R00' ? 'Revised ' : '' }}Commercial Quotation - ') + '{{ $quotation->quotation_no }}')">
        <style>
            .screen-footer {
                display: block;
            }

            .print-footer-quotation {
                display: none;
            }

            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    margin: 0;
                }

                @page {
                    size: A4;
                    margin: 10mm 0 0 0;
                    /* Reduced from default */
                }

                @page :first {
                    margin-top: 0mm;
                }

                html,
                body {
                    background: white;
                }

                #q-invoice {
                    width: 210mm;
                    margin: 0;
                    box-shadow: none;
                    border-radius: 0;
                    border: 0;
                    box-sizing: border-box;
                    padding: 2mm 2mm 28mm 2mm;
                    /* bottom padding reserves space for fixed print footer */
                    overflow: visible;
                    position: relative;
                    font-size: 10px;
                    /* Slightly smaller text */
                }

                #q-invoice::before {
                    content: "";
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    width: 70vmin;
                    height: 70vmin;
                    background: url("{{ asset('assets/images/logo.png') }}") center/contain no-repeat;
                    opacity: 0.06;
                    z-index: 0;
                    pointer-events: none;
                }

                #q-invoice>* {
                    position: relative;
                    z-index: 1;
                }

                .overflow-x-auto {
                    overflow: visible !important;
                }

                /* Solution 1: Fix Page Break Issues */
                .grid,
                .flex {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }

                /* Keep header together */
                #q-invoice>div:first-child {
                    page-break-after: avoid;
                }

                thead {
                    display: table-header-group;
                }

                tfoot {
                    display: table-row-group;
                }

                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                /* Prevent orphaned rows */
                tbody tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                table {
                    page-break-inside: auto;
                    break-inside: auto;
                }

                /* Solution 2: Reduce spacing */
                .mt-6 {
                    margin-top: 8px !important;
                }

                .mt-4 {
                    margin-top: 6px !important;
                }

                .p-4 {
                    padding: 8px !important;
                }

                .p-6 {
                    padding: 10px !important;
                }

                .screen-footer {
                    display: none !important;
                }

                /* Solution 3: Fix Footer Positioning */
                .print-footer-quotation {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    width: 100%;
                    height: auto;
                    box-sizing: border-box;
                    padding: 0 12mm 6mm 12mm;
                    display: block;
                    margin: 0;
                    background: white;
                    z-index: 2;
                }

                /* Layout Table for Repeating Header */
                table.layout-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                table.layout-table>thead {
                    display: table-header-group;
                }

                table.layout-table>tbody>tr,
                table.layout-table>tbody>tr>td {
                    page-break-inside: auto !important;
                    break-inside: auto !important;
                }
            }
        </style>

        {{-- Breadcrumb --}}
        <x-dashboard.ui.bread-crumb>
            <li class="inline-flex items-center">
                <a href="{{ route('quotations.index') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-300">
                    <x-ui.svg.book class="h-3 w-3 me-2" />
                    Quotations
                </a>
            </li>
            <x-dashboard.ui.bread-crumb-list name="Quotation" />
        </x-dashboard.ui.bread-crumb>

        <div class="p-8 bg-gray-50 min-h-screen font-sans">
            <div class="max-w-4xl mx-auto">
                {{-- Action Buttons --}}
                <div class="flex justify-between items-center mb-6">
                    <div class="flex gap-3">
                        <button id="printBtn" type="button"
                            class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 rounded-lg px-4 py-2 text-sm">
                            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                    d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                            </svg>
                            Print
                        </button>
                        {{-- <button id="downloadExcelBtn" type="button"
                            class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 rounded-lg px-4 py-2 text-sm transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Excel
                        </button> --}}
                        <button @click="isTechnical = !isTechnical" type="button"
                            class="inline-flex items-center text-white rounded-lg px-4 py-2 text-sm transition-colors duration-200"
                            :class="isTechnical ? 'bg-gray-600 hover:bg-gray-700' : 'bg-indigo-600 hover:bg-indigo-700'">
                            <span x-text="isTechnical ? 'Show Commercial' : 'Technical'">Technical</span>
                        </button>

                        {{-- @if (!$hasChallan)
                        <a href="{{ route('challans.create', ['quotation_id' => $quotation->id]) }}"
                            class="inline-flex items-center text-white bg-green-600 rounded-lg px-4 py-2 text-sm">Proceed
                            to Challan</a>
                    @else
                        <a href="{{ route('challans.show', $activeRevision->challan->id) }}"
                            class="inline-flex items-center text-white bg-green-600 rounded-lg px-4 py-2 text-sm">Show
                            Challan</a>
                    @endif --}}
                    </div>

                    <div class="text-sm text-gray-600">Created:
                        <strong>{{ $activeRevision->created_at->format('d M, Y') }}</strong>
                    </div>
                </div>

                {{-- Invoice card (this will be printed) --}}
                <div id="q-invoice" class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-lg">
                    <div class="print-watermark hidden"></div>

                    <div class="px-6 py-6">
                        <table class="w-full layout-table">
                            <thead>
                                <tr>
                                    <td>
                                        {{-- Header --}}
                                        <div class="flex justify-between items-start gap-3">
                                            <div class="flex gap-4 items-center">
                                                <img src="{{ asset('assets/images/logo.png') }}" alt="Company Logo"
                                                    class="h-20 w-auto" />
                                            </div>
                                            <div class="leading-tight text-right">
                                                <div class="text-xs text-gray-600">Malek Mansion (Ground), 128 Motijheel
                                                    C/A, Dhaka-1000
                                                </div>
                                                <div class="text-xs text-gray-600">ataur@optimech.com.bd,
                                                    ataur.optimech@gmail.com</div>
                                                <div class="text-xs text-gray-600">+8801841176747, +8801712117558</div>
                                                <div class="text-xs text-gray-600">www.optimech.com.bd</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{-- Info grid --}}
                                        <div class="grid grid-cols-3 gap-3 mt-4">
                                            <div
                                                class="border col-span-2 border-gray-200 rounded-lg p-4 bg-gray-50 grid grid-cols-2">
                                                <div>
                                                    <span
                                                        class="block text-xs text-gray-600 font-semibold uppercase">Bill
                                                        To</span>
                                                    <div class="mt-1.5">
                                                        <div class="text-xs font-semibold text-gray-900">
                                                            {{ $quotation->customer->customer_name }}</div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->designation }}</div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->department }}</div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->company->name }}
                                                        </div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->address }}
                                                        </div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->phone? 'Cell: '. $quotation->customer->phone : 'N/A' }}</div>
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->email }}</div>
                                                        @if ($quotation->customer->attention)
                                                            <div class="text-xs text-gray-600 mt-2 font-semibold">
                                                                Attention:
                                                                {{ $quotation->customer->attention }}</div>
                                                        @endif
                                                        <div class="text-xs text-gray-600 mt-2 whitespace-pre-line">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span
                                                        class="block text-xs text-gray-600 font-semibold uppercase">Ship
                                                        To</span>
                                                    <div class="mt-1.5">
                                                        <div class="text-xs text-gray-600">
                                                            {{ $quotation->customer->company->name }}
                                                        </div>
                                                        <div class="text-xs text-gray-600">{{ $quotation->ship_to }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-2 whitespace-pre-line">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                                <div class="flex justify-between items-center mb-4">
                                                    @php
                                                        $titlePrefix =
                                                            $activeRevision->revision_no !== 'R00' ? 'REVISED ' : '';
                                                    @endphp
                                                    <div class="text-xs text-gray-700 font-bold"
                                                        x-text="isTechnical ? '{{ $titlePrefix }}TECHNICAL QUOTATION' : '{{ $titlePrefix }}COMMERCIAL QUOTATION'">
                                                        {{ $titlePrefix }}COMMERCIAL QUOTATION</div>
                                                    <div>
                                                        <span
                                                            class="inline-block px-1 py-1.5 bg-blue-500 text-white text-xs font-bold rounded-md whitespace-nowrap">
                                                            {{ $quotation->quotation_no }}{{ $activeRevision->revision_no == 'R00' ? '' : ' (' . $activeRevision->revision_no . ')' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-2 text-sm">
                                                    <div class="text-gray-600">Date</div>
                                                    <div class="text-right font-semibold">
                                                        {{ $activeRevision->date->format('d/m/Y') }}
                                                    </div>

                                                    <div class="text-gray-600">Validity</div>
                                                    <div class="flex justify-end text-right">
                                                        <div class="text-xs text-gray-500 whitespace-nowrap">
                                                            ({{ $activeRevision->date->diffInDays(\Carbon\Carbon::parse($activeRevision->validity)) }}
                                                            days)
                                                        </div>
                                                        <div class="font-semibold">
                                                            {{ date('d/m/Y', strtotime($activeRevision->validity)) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Items table --}}
                                        <section class="mt-6">
                                            <div class="overflow-x-auto">
                                                <table class="w-full border-collapse border border-gray-300 text-xs">
                                                    <thead
                                                        class="bg-blue-800 text-white uppercase font-bold text-[11px]">
                                                        <tr>
                                                            {{-- <th class="w-10 text-center p-2 border border-gray-300">SL</th> --}}
                                                            <th
                                                                class="w-[150px] text-center p-2 border border-gray-300">
                                                                Item Name</th>
                                                            <th class="text-center p-2 border border-gray-300 w-1/4">
                                                                Specification</th>
                                                            <th
                                                                class=" w-[40px] text-center p-2 border border-gray-300">
                                                                Qty</th>
                                                            <th
                                                                class=" w-[40px] text-center p-2 border border-gray-300">
                                                                Unit</th>
                                                            <th
                                                                class=" w-[40px] text-center p-2 border border-gray-300">
                                                                Delivery</th>
                                                            <th
                                                                class=" w-[40px] text-center p-2 border border-gray-300">
                                                                Sample Photo
                                                            </th>
                                                            <th class=" w-[70px] text-center p-2 border border-gray-300"
                                                                x-show="!isTechnical">Unit Price</th>
                                                            <th class=" w-[60px] text-center p-2 border border-gray-300"
                                                                x-show="!isTechnical">Total</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @php
                                                            $sentinel = new stdClass();
                                                            $prevSpecId = $sentinel;
                                                            $prevImagePath = $sentinel;
                                                            $rowspan = 1;
                                                            $imageRowspan = 1;
                                                            $skipSpec = false;
                                                            $skipImage = false;
                                                        @endphp
                                                        @foreach ($activeRevision->products as $index => $quotationProduct)
                                                            @php
                                                                // Handle specification rowspan
                                                                $currentSpecId = $quotationProduct->specification_id;
                                                                if ($prevSpecId !== $currentSpecId) {
                                                                    $rowspan = 1;
                                                                    for (
                                                                        $i = $index + 1;
                                                                        $i < count($activeRevision->products);
                                                                        $i++
                                                                    ) {
                                                                        if (
                                                                            $activeRevision->products[$i]
                                                                                ->specification_id === $currentSpecId
                                                                        ) {
                                                                            $rowspan++;
                                                                        } else {
                                                                            break;
                                                                        }
                                                                    }
                                                                    $prevSpecId = $currentSpecId;
                                                                    $skipSpec = false;
                                                                } else {
                                                                    $skipSpec = true;
                                                                }

                                                                // Handle image rowspan
                                                                $currentImagePath =
                                                                    $quotationProduct->product->image->path ?? null;
                                                                if ($prevImagePath !== $currentImagePath) {
                                                                    // Count how many consecutive rows have the same image path
                                                                    $imageRowspan = 1;
                                                                    for (
                                                                        $i = $index + 1;
                                                                        $i < count($activeRevision->products);
                                                                        $i++
                                                                    ) {
                                                                        $nextImagePath =
                                                                            $activeRevision->products[$i]->product
                                                                                ->image->path ?? null;
                                                                        if ($nextImagePath === $currentImagePath) {
                                                                            $imageRowspan++;
                                                                        } else {
                                                                            break;
                                                                        }
                                                                    }
                                                                    $prevImagePath = $currentImagePath;
                                                                    $skipImage = false;
                                                                } else {
                                                                    $skipImage = true;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td class="text-center border border-gray-300">
                                                                    <div>
                                                                        <strong>Name: </strong>
                                                                        {{ $quotationProduct->product->name }}
                                                                    </div>
                                                                    @if ($quotationProduct->size ?? false)
                                                                        <div>
                                                                            <strong>Size: </strong>
                                                                            {{ $quotationProduct->size }}
                                                                        </div>
                                                                    @endif

                                                                    @if ($quotationProduct->requision_no ?? false)
                                                                        <div class="mt-2">
                                                                            <strong>Req/PR: </strong>
                                                                            {{ $quotationProduct->requision_no }}
                                                                        </div>
                                                                    @endif

                                                                </td>
                                                                @if (!$skipSpec)
                                                                    <td class="align-middle text-center border border-gray-300 w-1/4"
                                                                        rowspan="{{ $rowspan }}">
                                                                        {!! $quotationProduct->specification->description !!}
                                                                        @if ($quotationProduct->brandOrigin->name ?? false)
                                                                            <div class="font-bold">
                                                                                <strong>Brand/Origin: </strong>
                                                                                {{ $quotationProduct->brandOrigin->name }}
                                                                            </div>
                                                                        @endif
                                                                        @if ($quotationProduct->add_spec ?? false)
                                                                            <div>
                                                                                {{-- <strong>Add Spec: </strong> --}}
                                                                                {{ $quotationProduct->add_spec }}
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                                <td class="text-center border border-gray-300">
                                                                    {{ $quotationProduct->quantity }}</td>
                                                                <td class="text-center border border-gray-300">
                                                                    {{ $quotationProduct->unit }}</td>
                                                                <td class="text-center border border-gray-300">
                                                                    {{ $quotationProduct->delivery_time }}</td>
                                                                @if (!$skipImage)
                                                                    <td class="border border-gray-300 text-center align-middle"
                                                                        rowspan="{{ $imageRowspan }}">
                                                                        @if ($quotationProduct->product->image->path ?? false)
                                                                            <img src="{{ asset($quotationProduct->product->image->path) }}"
                                                                                alt="Sample Image">
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </td>
                                                                @endif
                                                                <td class="text-right border border-gray-300"
                                                                    x-show="!isTechnical">
                                                                    {{ number_format($quotationProduct->unit_price, 2) }}
                                                                </td>
                                                                <td class="text-right border border-gray-300"
                                                                    x-show="!isTechnical">
                                                                    {{ number_format($quotationProduct->unit_price * $quotationProduct->quantity, 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        {{-- Summary rows --}}
                                                        <tr x-show="!isTechnical">
                                                            <td colspan="4" class="border-0"></td>
                                                            <td class="border text-right border-gray-300"
                                                                colspan="2">Subtotal</td>
                                                            <td class="border text-right border-gray-300"
                                                                colspan="2">
                                                                {{ number_format($activeRevision->subtotal, 2) }}
                                                                {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                            </td>
                                                        </tr>
                                                        @if ($activeRevision->discount_amount)
                                                            <tr x-show="!isTechnical">
                                                                <td colspan="4" class="border-0"></td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">Discount
                                                                </td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">
                                                                    {{ number_format($activeRevision->discount_amount, 2) }}
                                                                    {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                                </td>
                                                            </tr>
                                                            <tr x-show="!isTechnical">
                                                                <td colspan="4" class="border-0"></td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">Discount
                                                                    Less
                                                                    Subtotal</td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">
                                                                    {{ number_format($activeRevision->subtotal - $activeRevision->discount_amount, 2) }}
                                                                    {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        @if ($activeRevision->shipping)
                                                            <tr x-show="!isTechnical">
                                                                <td colspan="4" class="border-0"></td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">Shipping
                                                                </td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">
                                                                    {{ number_format($activeRevision->shipping, 2) }}
                                                                    {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        @if ($activeRevision->vat_amount)
                                                            <tr x-show="!isTechnical">
                                                                <td colspan="4" class="border-0"></td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">VAT
                                                                    ({{ (int) $activeRevision->vat_percentage }}%)</td>
                                                                <td class="border text-right border-gray-300"
                                                                    colspan="2">
                                                                    {{ number_format($activeRevision->vat_amount, 2) }}
                                                                    {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        <tr x-show="!isTechnical">
                                                            <td colspan="4" class="border-0"></td>
                                                            <td class="border text-right border-gray-300"
                                                                colspan="2">Grand Total
                                                            </td>
                                                            <td class="border text-right border-gray-300"
                                                                colspan="2">
                                                                {{ number_format($activeRevision->total, 2) }}
                                                                {{ $activeRevision->type == 'via' ? $activeRevision->currency : '৳' }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>

                                        {{-- Signature and Terms --}}
                                        <div class="flex justify-between gap-3 mt-6 ">
                                            <div
                                                class="text-xs text-gray-600 border border-gray-200 rounded-lg p-4 bg-gray-50 {{ $activeRevision->type === 'via' ? 'w-2/3' : '' }}">
                                                @if ($activeRevision->terms_conditions)
                                                    {!! $activeRevision->terms_conditions !!}
                                                @else
                                                    {!! $quotation->terms_conditions !!}
                                                @endif
                                            </div>
                                            <div class="flex justify-between w-60 text-center mt-10">
                                                <div>
                                                    <img src="{{ asset('assets/images/seal.jpg') }}" alt="seal"
                                                        class="mx-auto h-16 w-auto" />
                                                </div>
                                                <div>
                                                    <div class="text-xs text-gray-600 mb-2">Authorized By</div>
                                                    <div>
                                                        <img src="{{ asset('assets/images/signature.jpg') }}"
                                                            alt="Signature" class="mx-auto h-12 w-auto" />
                                                    </div>
                                                    <p class="text-xs text-gray-600">Mohammad Ataur Rahman</p>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="screen-footer">
                            <div class="w-full border-t border-gray-300 mt-6"></div>
                            <div
                                class="mt-1 p-2.5 border-t-3 text-red-600 border-blue-600 bg-gray-50 text-center text-xs underline">
                                <div>"Complete Solution for Pharma, Food, Power, and Other Industries"</div>
                            </div>
                            {{-- <div class="text-center text-sm mt-1.5 font-bold uppercase">Thank You For Your Business!</div> --}}
                        </div>
                        <div class="print-footer-quotation">
                            <div class="w-full border-t border-gray-300 mt-6"></div>
                            <div
                                class="mt-1 p-2.5 border-t-3 text-red-600 border-blue-600 bg-gray-50 text-center text-xs underline">
                                <div>"Complete Solution for Pharma, Food, Power, and Other Industries"</div>
                            </div>
                            {{-- <div class="text-center text-sm mt-1.5 font-bold uppercase">Thank You For Your Business!</div> --}}
                        </div>

                    </div>
                </div>

            </div>
        </div>

        @php
            $products = $activeRevision->products;
            $count = count($products);
            $processedProducts = [];

            // First pass: convert to array and basic processing
            foreach ($products as $index => $qp) {
                $processedProducts[$index] = [
                    'name' => $qp->product->name,
                    'size' => $qp->size,
                    'req_no' => $qp->requision_no,
                    'specification' => strip_tags(
                        str_replace(['<br>', '<br/>', '<br />'], "\n", $qp->specification->description),
                    ),
                    'brand_origin' => $qp->brandOrigin->name ?? '',
                    'add_spec' => $qp->add_spec,
                    'quantity' => $qp->quantity,
                    'unit' => $qp->unit,
                    'delivery_time' => $qp->delivery_time,
                    'image_path' => $qp->product->image->path ?? null,
                    'image_url' => isset($qp->product->image->path) ? asset($qp->product->image->path) : null,
                    'unit_price' => $qp->unit_price,
                    'total' => $qp->unit_price * $qp->quantity,
                    'specification_id' => $qp->specification_id,
                    'spec_rowspan' => 1,
                    'skip_spec' => false,
                    'image_rowspan' => 1,
                    'skip_image' => false,
                ];
            }

            // Second pass: Calculate rowspans
            for ($i = 0; $i < $count; $i++) {
                // Spec
                if (
                    $i > 0 &&
                    $processedProducts[$i]['specification_id'] === $processedProducts[$i - 1]['specification_id']
                ) {
                    $processedProducts[$i]['skip_spec'] = true;
                } else {
                    $rowspan = 1;
                    for ($j = $i + 1; $j < $count; $j++) {
                        if (
                            $processedProducts[$j]['specification_id'] === $processedProducts[$i]['specification_id']
                        ) {
                            $rowspan++;
                        } else {
                            break;
                        }
                    }
                    $processedProducts[$i]['spec_rowspan'] = $rowspan;
                }

                // Image
                $currentImg = $processedProducts[$i]['image_path'];
                if ($i > 0 && $processedProducts[$i]['image_path'] === $processedProducts[$i - 1]['image_path']) {
                    $processedProducts[$i]['skip_image'] = true;
                } else {
                    $rowspan = 1;
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($processedProducts[$j]['image_path'] === $currentImg) {
                            $rowspan++;
                        } else {
                            break;
                        }
                    }
                    $processedProducts[$i]['image_rowspan'] = $rowspan;
                }
            }

            $quotationData = [
                'quotationNo' => $quotation->quotation_no,
                'revisionNo' => $activeRevision->revision_no,
                'createdDate' => $activeRevision->created_at->format('d M, Y'),
                'date' => $activeRevision->date->format('d/m/Y'),
                'validity' => date('d/m/Y', strtotime($activeRevision->validity)),
                'validityDays' => $activeRevision->date->diffInDays(
                    \Carbon\Carbon::parse($activeRevision->validity),
                ),
                'customer' => [
                    'name' => $quotation->customer->customer_name,
                    'designation' => $quotation->customer->designation,
                    'department' => $quotation->customer->department,
                    'company' => $quotation->customer->company->name,
                    'address' => $quotation->customer->address,
                    'phone' => $quotation->customer->phone,
                    'email' => $quotation->customer->email,
                    'attention' => $quotation->customer->attention,
                ],
                'shipTo' => [
                    'company' => $quotation->customer->company->name,
                    'address' => $quotation->ship_to,
                ],
                'products' => $processedProducts,
                'subtotal' => $activeRevision->subtotal,
                'discount_amount' => $activeRevision->discount_amount,
                'shipping' => $activeRevision->shipping,
                'vat_percentage' => (int) $activeRevision->vat_percentage,
                'vat_amount' => $activeRevision->vat_amount,
                'total' => $activeRevision->total,
                'currency' => $activeRevision->type == 'via' ? $activeRevision->currency : '৳',
                'terms_conditions' => strip_tags(
                    str_replace(
                        ['<br>', '<br/>', '<br />'],
                        "\n",
                        $activeRevision->terms_conditions
                            ? $activeRevision->terms_conditions
                            : $quotation->terms_conditions,
                    ),
                ),
                'logo_url' => asset('assets/images/logo.png'),
            ];
        @endphp

        <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

        <script>
            // Keep printThis.js behavior intact (unchanged functionality)
            document.querySelector('#printBtn').addEventListener('click', function() {
                $('#q-invoice').printThis({
                    importCSS: true,
                    importStyle: true,
                    copyTagStyles: true,
                    printDelay: 500,
                });
            });

            // Excel Generation Logic
            document.querySelector('#downloadExcelBtn').addEventListener('click', async function() {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.innerHTML =
                    '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';
                btn.disabled = true;

                try {
                    const data = @json($quotationData);
                    // Determine if we are in technical mode
                    // The button with x-text="isTechnical ? 'Show Commercial' : 'Technical'" reflects the state.
                    // If it says "Show Commercial", it means we are currently in Technical mode.
                    const toggleBtnSpan = document.querySelector('button span[x-text*="isTechnical"]');
                    const isTechnical = toggleBtnSpan ? toggleBtnSpan.innerText.includes('Show Commercial') : false;

                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet('Quotation');

                    // --- Styles ---
                    const boldFont = {
                        name: 'Calibri',
                        size: 11,
                        bold: true
                    };
                    const normalFont = {
                        name: 'Calibri',
                        size: 11
                    };
                    const titleFont = {
                        name: 'Calibri',
                        size: 14,
                        bold: true
                    };
                    const headerFill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FF1E40AF'
                        }
                    }; // Blue-800
                    const headerFont = {
                        name: 'Calibri',
                        size: 11,
                        bold: true,
                        color: {
                            argb: 'FFFFFFFF'
                        }
                    }; // White
                    const borderStyle = {
                        top: {
                            style: 'thin'
                        },
                        left: {
                            style: 'thin'
                        },
                        bottom: {
                            style: 'thin'
                        },
                        right: {
                            style: 'thin'
                        }
                    };
                    const centerAlign = {
                        vertical: 'middle',
                        horizontal: 'center',
                        wrapText: true
                    };
                    const leftAlign = {
                        vertical: 'middle',
                        horizontal: 'left',
                        wrapText: true
                    };
                    const rightAlign = {
                        vertical: 'middle',
                        horizontal: 'right'
                    };

                    // --- Columns Setup ---
                    // Columns: Item(A), Spec(B), Qty(C), Unit(D), Delivery(E), Sample(F), Price(G), Total(H)
                    // If Technical, hide G and H.
                    worksheet.columns = [{
                            key: 'item',
                            width: 25
                        },
                        {
                            key: 'spec',
                            width: 40
                        },
                        {
                            key: 'qty',
                            width: 8
                        },
                        {
                            key: 'unit',
                            width: 8
                        },
                        {
                            key: 'delivery',
                            width: 12
                        },
                        {
                            key: 'sample',
                            width: 15
                        },
                        {
                            key: 'price',
                            width: 15
                        },
                        {
                            key: 'total',
                            width: 15
                        },
                    ];

                    // --- Header Section ---
                    // Row 1-5: Logo and Company Info
                    // We need to fetch logo image
                    let logoId = null;
                    if (data.logo_url) {
                        try {
                            const response = await fetch(data.logo_url);
                            const buffer = await response.arrayBuffer();
                            logoId = workbook.addImage({
                                buffer: buffer,
                                extension: 'png',
                            });
                        } catch (e) {
                            console.warn('Failed to load logo', e);
                        }
                    }

                    // Company Info Text (Right Aligned)
                    worksheet.mergeCells('D1:H1');
                    worksheet.getCell('D1').value = 'Malek Mansion (Ground), 128 Motijheel C/A, Dhaka-1000';
                    worksheet.getCell('D1').alignment = rightAlign;
                    worksheet.getCell('D1').font = {
                        size: 9,
                        color: {
                            argb: 'FF4B5563'
                        }
                    }; // Gray-600

                    worksheet.mergeCells('D2:H2');
                    worksheet.getCell('D2').value = 'ataur@optimech.com.bd, ataur.optimech@gmail.com';
                    worksheet.getCell('D2').alignment = rightAlign;
                    worksheet.getCell('D2').font = {
                        size: 9,
                        color: {
                            argb: 'FF4B5563'
                        }
                    };

                    worksheet.mergeCells('D3:H3');
                    worksheet.getCell('D3').value = '+8801841176747, +8801712117558';
                    worksheet.getCell('D3').alignment = rightAlign;
                    worksheet.getCell('D3').font = {
                        size: 9,
                        color: {
                            argb: 'FF4B5563'
                        }
                    };

                    worksheet.mergeCells('D4:H4');
                    worksheet.getCell('D4').value = 'www.optimech.com.bd';
                    worksheet.getCell('D4').alignment = rightAlign;
                    worksheet.getCell('D4').font = {
                        size: 9,
                        color: {
                            argb: 'FF4B5563'
                        }
                    };

                    if (logoId !== null) {
                        worksheet.addImage(logoId, {
                            tl: {
                                col: 0,
                                row: 0
                            },
                            ext: {
                                width: 150,
                                height: 60
                            }
                        });
                    }

                    worksheet.addRow([]); // Spacer

                    // --- Bill To / Ship To ---
                    const startRow = 6;
                    // Bill To Box
                    worksheet.mergeCells(`A${startRow}:C${startRow}`);
                    worksheet.getCell(`A${startRow}`).value = 'BILL TO';
                    worksheet.getCell(`A${startRow}`).font = { ...boldFont,
                        color: {
                            argb: 'FF4B5563'
                        }
                    };

                    const billToStart = startRow + 1;
                    worksheet.mergeCells(`A${billToStart}:C${billToStart}`);
                    worksheet.getCell(`A${billToStart}`).value = data.customer.name;
                    worksheet.getCell(`A${billToStart}`).font = boldFont;

                    let currentRow = billToStart + 1;
                    const customerFields = [data.customer.designation, data.customer.department, data.customer.company,
                        data.customer.address, data.customer.phone, data.customer.email
                    ];
                    if (data.customer.attention) customerFields.push('Attention: ' + data.customer.attention);

                    customerFields.forEach(text => {
                        if (text) {
                            worksheet.mergeCells(`A${currentRow}:C${currentRow}`);
                            worksheet.getCell(`A${currentRow}`).value = text;
                            worksheet.getCell(`A${currentRow}`).font = {
                                size: 10,
                                color: {
                                    argb: 'FF4B5563'
                                }
                            };
                            currentRow++;
                        }
                    });

                    // Ship To Box (Right Side) - Actually UI puts it next to Bill To
                    // In UI: Bill To is Col-Span-2 (Left), Ship To is inside that block on the right?
                    // No, "Bill To" takes left part, "Ship To" takes right part of the left block.
                    // And there is a Right Block with Date/Validity.

                    // Let's simplify for Excel:
                    // A-C: Bill To
                    // D-E: Ship To
                    // F-H: Quotation Info

                    worksheet.getCell(`D${startRow}`).value = 'SHIP TO';
                    worksheet.getCell(`D${startRow}`).font = { ...boldFont,
                        color: {
                            argb: 'FF4B5563'
                        }
                    };

                    let shipRow = startRow + 1;
                    const shipFields = [data.shipTo.company, data.shipTo.address];
                    shipFields.forEach(text => {
                        if (text) {
                            worksheet.mergeCells(`D${shipRow}:E${shipRow}`);
                            worksheet.getCell(`D${shipRow}`).value = text;
                            worksheet.getCell(`D${shipRow}`).font = {
                                size: 10,
                                color: {
                                    argb: 'FF4B5563'
                                }
                            };
                            worksheet.getCell(`D${shipRow}`).alignment = {
                                wrapText: true,
                                vertical: 'top'
                            };
                            shipRow++;
                        }
                    });

                    // Quotation Info (Right Side)
                    worksheet.mergeCells(`F${startRow}:H${startRow}`);
                    const titlePrefix = data.revisionNo !== 'R00' ? 'REVISED ' : '';
                    const titleType = isTechnical ? 'TECHNICAL' : 'COMMERCIAL';
                    worksheet.getCell(`F${startRow}`).value = `${titlePrefix}${titleType} QUOTATION`;
                    worksheet.getCell(`F${startRow}`).font = { ...boldFont,
                        size: 12
                    };
                    worksheet.getCell(`F${startRow}`).alignment = rightAlign;

                    const qNoRow = startRow + 1;
                    worksheet.mergeCells(`F${qNoRow}:H${qNoRow}`);
                    worksheet.getCell(`F${qNoRow}`).value =
                        `${data.quotationNo}${data.revisionNo !== 'R00' ? ' (' + data.revisionNo + ')' : ''}`;
                    worksheet.getCell(`F${qNoRow}`).fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FF3B82F6'
                        }
                    }; // Blue-500
                    worksheet.getCell(`F${qNoRow}`).font = {
                        color: {
                            argb: 'FFFFFFFF'
                        },
                        bold: true
                    };
                    worksheet.getCell(`F${qNoRow}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };

                    const dateRow = qNoRow + 1;
                    worksheet.getCell(`F${dateRow}`).value = 'Date';
                    worksheet.mergeCells(`G${dateRow}:H${dateRow}`);
                    worksheet.getCell(`G${dateRow}`).value = data.date;
                    worksheet.getCell(`G${dateRow}`).alignment = rightAlign;

                    const validRow = dateRow + 1;
                    worksheet.getCell(`F${validRow}`).value = 'Validity';
                    worksheet.mergeCells(`G${validRow}:H${validRow}`);
                    worksheet.getCell(`G${validRow}`).value = `(${data.validityDays} days) ${data.validity}`;
                    worksheet.getCell(`G${validRow}`).alignment = rightAlign;

                    // Ensure we move past the tallest block
                    let tableStartRow = Math.max(currentRow, shipRow, validRow) + 2;

                    // --- Product Table ---
                    const headerRow = worksheet.getRow(tableStartRow);
                    headerRow.values = ['Item Name', 'Specification', 'Qty', 'Unit', 'Delivery', 'Sample Photo',
                        'Unit Price', 'Total'
                    ];
                    headerRow.eachCell((cell) => {
                        cell.fill = headerFill;
                        cell.font = headerFont;
                        cell.alignment = centerAlign;
                        cell.border = borderStyle;
                    });

                    // Hide Price/Total if Technical
                    if (isTechnical) {
                        worksheet.getColumn('price').hidden = true;
                        worksheet.getColumn('total').hidden = true;
                    }

                    let currentRowIdx = tableStartRow + 1;

                    for (const product of data.products) {
                        const row = worksheet.getRow(currentRowIdx);

                        // Item Name Column (A)
                        let nameText = `Name: ${product.name}`;
                        if (product.size) nameText += `\nSize: ${product.size}`;
                        if (product.req_no) nameText += `\nReq/PR: ${product.req_no}`;
                        row.getCell(1).value = nameText;
                        row.getCell(1).alignment = leftAlign;

                        // Specification (B)
                        // Only set value if not skipped, but we need to handle merge later
                        if (!product.skip_spec) {
                            let specText = product.specification;
                            if (product.brand_origin) specText += `\nBrand/Origin: ${product.brand_origin}`;
                            if (product.add_spec) specText += `\n${product.add_spec}`;
                            row.getCell(2).value = specText;
                        }

                        // Qty (C)
                        row.getCell(3).value = product.quantity;
                        // Unit (D)
                        row.getCell(4).value = product.unit;
                        // Delivery (E)
                        row.getCell(5).value = product.delivery_time;

                        // Image (F)
                        // If not skipped, we will add image
                        // We need to fetch image if it exists
                        if (!product.skip_image && product.image_url) {
                            // Fetching images inside a loop might be slow, but necessary
                            try {
                                const imgResp = await fetch(product.image_url);
                                const imgBuff = await imgResp.arrayBuffer();
                                const imgId = workbook.addImage({
                                    buffer: imgBuff,
                                    extension: 'png', // Assuming png/jpg, ExcelJS handles it
                                });
                                // We'll position it after setting merges
                                product._imageId = imgId;
                            } catch (e) {
                                row.getCell(6).value = 'Image Error';
                            }
                        } else if (!product.skip_image && !product.image_url) {
                            row.getCell(6).value = 'N/A';
                        }

                        // Price (G)
                        row.getCell(7).value = product.unit_price;
                        row.getCell(7).numFmt = '#,##0.00';

                        // Total (H)
                        row.getCell(8).value = product.total;
                        row.getCell(8).numFmt = '#,##0.00';

                        // Apply styles to all cells in row
                        row.eachCell((cell) => {
                            cell.border = borderStyle;
                            if (cell.col !== 1 && cell.col !== 2) cell.alignment =
                            centerAlign; // Center others
                            if (cell.col === 7 || cell.col === 8) cell.alignment = rightAlign; // Right align money
                        });

                        currentRowIdx++;
                    }

                    // Apply Merges
                    // We need to iterate again or do it during loop.
                    // Since we have rowspan data, we can merge.
                    // Note: ExcelJS uses 1-based indexing.
                    // tableStartRow + 1 is the first data row.
                    data.products.forEach((p, index) => {
                        const startR = tableStartRow + 1 + index;
                        // Spec Merge
                        if (p.spec_rowspan > 1) {
                            worksheet.mergeCells(startR, 2, startR + p.spec_rowspan - 1, 2);
                        }
                        // Image Merge
                        if (p.image_rowspan > 1) {
                            worksheet.mergeCells(startR, 6, startR + p.image_rowspan - 1, 6);
                        }

                        // Add Image to Cell
                        if (!p.skip_image && p._imageId !== undefined) {
                            // Calculate height based on rowspan
                            const endR = startR + p.image_rowspan - 1;
                            worksheet.addImage(p._imageId, {
                                tl: {
                                    col: 5,
                                    row: startR - 1
                                }, // col 5 is index 5 (0-based) -> F
                                br: {
                                    col: 6,
                                    row: endR
                                },
                                editAs: 'oneCell'
                            });
                        }
                    });


                    // --- Totals ---
                    if (!isTechnical) {
                        let totalRow = currentRowIdx;
                        // Subtotal
                        worksheet.mergeCells(`A${totalRow}:D${totalRow}`); // Spacer
                        worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                        worksheet.getCell(`E${totalRow}`).value = 'Subtotal';
                        worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                        worksheet.getCell(`E${totalRow}`).border = borderStyle;

                        worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                        worksheet.getCell(`G${totalRow}`).value = data.subtotal;
                        worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                        worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                        worksheet.getCell(`G${totalRow}`).border = borderStyle;
                        totalRow++;

                        // Discount
                        if (data.discount_amount) {
                            worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                            worksheet.getCell(`E${totalRow}`).value = 'Discount';
                            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`E${totalRow}`).border = borderStyle;

                            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                            worksheet.getCell(`G${totalRow}`).value = data.discount_amount;
                            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`G${totalRow}`).border = borderStyle;
                            totalRow++;

                             worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                            worksheet.getCell(`E${totalRow}`).value = 'Discount Less Subtotal';
                            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`E${totalRow}`).border = borderStyle;

                            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                            worksheet.getCell(`G${totalRow}`).value = data.subtotal - data.discount_amount;
                            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`G${totalRow}`).border = borderStyle;
                            totalRow++;
                        }

                        // Shipping
                        if (data.shipping) {
                             worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                            worksheet.getCell(`E${totalRow}`).value = 'Shipping';
                            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`E${totalRow}`).border = borderStyle;

                            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                            worksheet.getCell(`G${totalRow}`).value = data.shipping;
                            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`G${totalRow}`).border = borderStyle;
                            totalRow++;
                        }

                         // VAT
                        if (data.vat_amount) {
                             worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                            worksheet.getCell(`E${totalRow}`).value = `VAT (${data.vat_percentage}%)`;
                            worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`E${totalRow}`).border = borderStyle;

                            worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                            worksheet.getCell(`G${totalRow}`).value = data.vat_amount;
                            worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                            worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                            worksheet.getCell(`G${totalRow}`).border = borderStyle;
                            totalRow++;
                        }

                        // Grand Total
                         worksheet.mergeCells(`E${totalRow}:F${totalRow}`);
                        worksheet.getCell(`E${totalRow}`).value = 'Grand Total';
                        worksheet.getCell(`E${totalRow}`).alignment = rightAlign;
                        worksheet.getCell(`E${totalRow}`).font = boldFont;
                        worksheet.getCell(`E${totalRow}`).border = borderStyle;

                        worksheet.mergeCells(`G${totalRow}:H${totalRow}`);
                        worksheet.getCell(`G${totalRow}`).value = data.total;
                        worksheet.getCell(`G${totalRow}`).numFmt = `#,##0.00 "${data.currency}"`;
                        worksheet.getCell(`G${totalRow}`).alignment = rightAlign;
                        worksheet.getCell(`G${totalRow}`).font = boldFont;
                        worksheet.getCell(`G${totalRow}`).border = borderStyle;

                        currentRowIdx = totalRow + 2; // Add some space
                    } else {
                         currentRowIdx += 2;
                    }


                    // --- Terms and Signature ---
                    const termsRow = currentRowIdx;
                    worksheet.mergeCells(`A${termsRow}:E${termsRow + 5}`); // Give some space
                    const termsCell = worksheet.getCell(`A${termsRow}`);
                    termsCell.value = data.terms_conditions;
                    termsCell.alignment = { vertical: 'top', horizontal: 'left', wrapText: true };
                    termsCell.border = borderStyle;

                    // Signature
                    // We can try to fetch the signature image if available (assets/images/signature.jpg)
                     try {
                        const sigResp = await fetch("{{ asset('assets/images/signature.jpg') }}");
                        const sigBuff = await sigResp.arrayBuffer();
                        const sigId = workbook.addImage({
                            buffer: sigBuff,
                            extension: 'jpg',
                        });
                         worksheet.addImage(sigId, {
                            tl: { col: 6, row: termsRow },
                            ext: { width: 100, height: 50 }
                        });
                    } catch(e) {}

                    // Seal
                     try {
                        const sealResp = await fetch("{{ asset('assets/images/seal.jpg') }}");
                        const sealBuff = await sealResp.arrayBuffer();
                        const sealId = workbook.addImage({
                            buffer: sealBuff,
                            extension: 'jpg',
                        });
                         worksheet.addImage(sealId, {
                            tl: { col: 5, row: termsRow },
                            ext: { width: 80, height: 80 }
                        });
                    } catch(e) {}

                    worksheet.getCell(`G${termsRow + 4}`).value = "Mohammad Ataur Rahman";
                    worksheet.getCell(`G${termsRow + 4}`).font = { size: 10 };
                    worksheet.getCell(`G${termsRow + 4}`).alignment = centerAlign;

                    worksheet.getCell(`G${termsRow + 5}`).value = "Authorized By";
                    worksheet.getCell(`G${termsRow + 5}`).font = { size: 10, italic: true };
                    worksheet.getCell(`G${termsRow + 5}`).alignment = centerAlign;

                    // Download
                    const buffer = await workbook.xlsx.writeBuffer();
                    const fileName = `Quotation_${data.quotationNo}_${data.revisionNo}.xlsx`;
                    saveAs(new Blob([buffer]), fileName);

                } catch (error) {
                    console.error('Excel Generation Error:', error);
                    alert('Failed to generate Excel file. Please try again.');
                } finally {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        </script>
    </div>
</x-dashboard.layout.default>
