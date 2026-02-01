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
                <a href="{{ route('tenant.quotations.index') }}"
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
                        <a href="{{ route('tenant.challans.create', ['quotation_id' => $quotation->id]) }}"
                            class="inline-flex items-center text-white bg-green-600 rounded-lg px-4 py-2 text-sm">Proceed
                            to Challan</a>
                    @else
                        <a href="{{ route('tenant.challans.show', $activeRevision->challan->id) }}"
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

        </script>
    </div>
</x-dashboard.layout.default>
