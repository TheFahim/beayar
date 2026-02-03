<x-dashboard.layout.default :title="'Challan - ' . $challan->challan_no">
    <style>
        /* Document palette / layout */
        :root {
            --brand-blue: #0b5ed7;
            --muted: #6b7280;
            --border: #e6e9ef;
            --paper: #ffffff;
        }

        /* Hide watermark on screen, show only while printing */
        .print-watermark {
            display: none;
        }

        /* Container that becomes A4 width when printing */
        #q-invoice {
            /* Handled by Tailwind classes */
        }

        /* Header styles to match PDF visual hierarchy */
        .q-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
        }

        .q-head .logo {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .q-head .logo img {
            height: 56px;
            width: auto;
        }

        .q-title {
            text-align: right;
        }

        .q-title h1 {
            font-size: 24px;
            margin: 0;
            color: var(--brand-blue, #0b5ed7);
            letter-spacing: 0.02em;
        }

        .q-meta {
            margin-top: 6px;
        }

        .q-ref {
            display: inline-block;
            padding: 6px 12px;
            background: linear-gradient(90deg, var(--brand-blue, #0b5ed7), #06b6d4);
            color: white;
            border-radius: 6px;
            font-weight: 700;
        }

        .q-info-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: .75rem;
            margin-top: .75rem;
        }

        .q-box {
            border: 1px solid var(--border);
            padding: 10px;
            border-radius: 6px;
            background: #fbfdff;
        }

        .q-customer strong {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        /* Items table styling */
        .q-items table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .q-items thead th {
            background: var(--brand-blue, #0b5ed7);
            color: white;
            padding: 8px;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 11px;
        }

        .q-items tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .q-amount {
            text-align: right;
        }

        /* Signature and terms */
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

        .q-terms {
            font-size: 12px;
            color: var(--muted);
        }

        .screen-footer {
            display: block;
        }

        .print-footer-challan {
            display: none;
        }

        /* Responsive tweaks */
        @media (max-width:900px) {
            .q-info-grid {
                grid-template-columns: 1fr;
            }

            .q-title {
                text-align: left;
            }
        }

        /* Print styles - keep watermark and A4 fitting */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
            }

            @page {
                size: A4;
                margin: 0;
            }

            html,
            body {
                background: white;
            }

            /* make invoice full A4 width and remove shadows */
            #q-invoice {
                width: 210mm;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                border: 0;
                box-sizing: border-box;
                padding: 4mm;
                overflow: visible;
            }

            /* show watermark in print only (use pseudo-element to avoid layout shift) */
            .print-watermark {
                display: none !important;
            }

            #q-invoice {
                position: relative;
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

            /* ensure content prints above watermark */
            #q-invoice>* {
                position: relative;
                z-index: 1;
            }

            .overflow-x-auto {
                overflow: visible !important;
            }

            /* Keep the info header section horizontally aligned in print */
            .q-info-grid {
                display: flex !important;
                gap: .75rem;
                margin-top: 2mm;
            }

            /* Minimize extra spacing within printed content */
            .px-6,
            .py-6 {
                padding: 0 !important;
            }

            .mt-6 {
                margin-top: 2mm !important;
            }

            .screen-footer {
                display: none !important;
            }

            #q-invoice {
                padding-bottom: 22mm;
            }

            .print-footer-challan {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                box-sizing: border-box;
                padding: 0 12mm 6mm 12mm;
                z-index: 10;
                display: block;
            }

            .q-sign {
                margin-top: 2mm;
            }

            .q-info-grid>.q-customer {
                flex: 1 1 auto;
            }

            .q-info-grid> :nth-child(2) {
                flex: 0 0 320px;
                width: 320px;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group;
            }

            tr {
                page-break-inside: avoid;
                break-inside: avoid-page;
            }

            table {
                page-break-inside: auto;
                break-inside: auto;
            }

            .q-items,
            .q-box,
            .q-head,
            .q-sign,
            .q-terms {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>

    {{-- Breadcrumb --}}
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.challans.index') }}" class="inline-flex items-center text-sm font-medium text-gray-300">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Challans
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Challan" />
    </x-dashboard.ui.bread-crumb>

    <div class="p-8 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-4xl mx-auto">
            {{-- Action Buttons --}}
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-3">
                    <button id="printBtn" type="button"
                        class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 rounded-lg px-4 py-2 text-sm">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                        </svg>
                        Print
                    </button>

                    {{-- @if (!$hasBill)
                        <a href="{{ route('bills.create', ['challan_id' => $challan->id]) }}"
                            class="inline-flex items-center text-white bg-green-600 rounded-lg px-4 py-2 text-sm">Proceed
                            to Bill</a>
                    @else
                        @if ($latestBill)
                            <a href="{{ route('bills.show', $latestBill->id) }}"
                                class="inline-flex items-center text-white bg-green-600 rounded-lg px-4 py-2 text-sm">Show
                                Bill</a>
                        @endif
                    @endif --}}
                </div>


            </div>

            {{-- Invoice card (this will be printed) --}}
            <div id="q-invoice" class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-lg text-gray-900">
                <div class="print-watermark hidden"></div>

                <div class="px-6 py-6">
                    {{-- Header --}}
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex gap-4 items-center">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Company Logo" class="h-20 w-auto" />
                        </div>
                        <div class="leading-tight text-right">
                            <div class="text-xs text-gray-600">Malek Mansion (Ground), 128 Motijheel C/A, Dhaka-1000
                            </div>
                            <div class="text-xs text-gray-600">ataur@optimech.com.bd, ataur.optimech@gmail.com</div>
                            <div class="text-xs text-gray-600">+8801841176747, +8801712117558</div>
                            <div class="text-xs text-gray-600">www.optimech.com.bd</div>
                        </div>
                    </div>

                    {{-- Info grid --}}
                    <div class="grid grid-cols-3 gap-3 mt-4">
                        <div class="border col-span-2 border-gray-200 rounded-lg p-4 grid grid-cols-2">
                            <div>
                                <span class="block text-xs text-gray-600 font-semibold uppercase">Bill To</span>
                                <div class="mt-1.5">
                                    <div class="text-xs font-semibold text-gray-900">
                                        {{ $challan->revision->quotation->customer->customer_name }}</div>
                                    <div class="text-xs text-gray-600">
                                        {{ $challan->revision->quotation->customer->designation }}</div>
                                    <div class="text-xs text-gray-600">
                                        {{ optional($challan->revision->quotation->customer->company)->name ?? $challan->revision->quotation->customer->company_name }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-2 whitespace-pre-line"></div>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-600 font-semibold uppercase">Ship To</span>
                                <div class="mt-1.5">
                                    <div class="text-xs text-gray-600">
                                        {{ optional($challan->revision->quotation->customer->company)->name ?? $challan->revision->quotation->customer->company_name }}
                                    </div>
                                    <div class="text-xs text-gray-600">{{ $challan->revision->quotation->ship_to }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-2 whitespace-pre-line"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-xs text-gray-700 font-bold">CHALLAN</div>
                                <div>
                                    <span
                                        class="inline-block px-1 py-1.5 bg-blue-500 text-white text-xs font-bold rounded-md whitespace-nowrap">
                                        {{ $challan->challan_no }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="block text-xs text-gray-600 font-semibold uppercase">PO No</div>
                                <div class="text-xs text-gray-600 text-right">
                                    {{ $challan->revision->quotation->po_no }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="block text-xs text-gray-600 font-semibold uppercase">PO Date</div>
                                <div class="text-xs text-gray-600 text-right">
                                    {{ $challan->revision->quotation->po_date ? \Carbon\Carbon::parse($challan->revision->quotation->po_date)->format('d/m/Y') : 'N/A' }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-gray-600">Date</div>
                                <div class="text-right font-semibold">
                                    {{ $challan->date ? \Carbon\Carbon::parse($challan->date)->format('d/m/Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items table --}}
                    <section class="q-items mt-6">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr>
                                        <th style="width:40px; text-align:center;" class="border border-gray-300">SL
                                        </th>
                                        <th style="text-align:left;" class="border border-gray-300">Product</th>
                                        <th style="text-align:left;" class="border border-gray-300 w-1/4">Specification
                                        </th>
                                        <th style="width:70px; text-align:center;" class="border border-gray-300">Unit
                                        </th>
                                        <th style="width:70px; text-align:right;" class="border border-gray-300">
                                            Quantity</th>
                                        <th style="text-align:left;" class="border border-gray-300 w-1/5">Remarks</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($challan->products as $index => $cp)
                                        <tr>
                                            <td style="text-align:center;" class="border border-gray-300">
                                                {{ $index + 1 }}</td>
                                            <td style="text-align:left; font-weight:600;"
                                                class="border border-gray-300">
                                                <div>
                                                    <strong>Name: </strong> {{ $cp->quotationProduct->product->name }}
                                                    @if ($cp->quotationProduct->size ?? false)
                                                        <div>
                                                            <strong>Size: </strong> {{ $cp->quotationProduct->size }}
                                                        </div>
                                                    @endif

                                                    @if ($cp->quotationProduct->requision_no ?? false)
                                                        <div>
                                                            <strong>Requisition No: </strong>
                                                            {{ $cp->quotationProduct->requision_no }}
                                                        </div>
                                                    @endif
                                            </td>


                                            <td class="border border-gray-300 w-1/4">
                                                @if ($cp->quotationProduct->specification ?? false)
                                                    {!! $cp->quotationProduct->specification->description !!}
                                                @else
                                                    {!! $cp->quotationProduct->product->description !!}
                                                @endif

                                                @if ($cp->quotationProduct->brandOrigin ?? false)
                                                    <div>
                                                        <strong>Brand Origin: </strong>
                                                        {{ $cp->quotationProduct->brandOrigin->name }}
                                                    </div>
                                                @endif

                                                @if ($cp->quotationProduct->brand ?? false)
                                                    <div>
                                                        <strong>Brand: </strong>
                                                        {{ $cp->quotationProduct->brand->name }}
                                                    </div>
                                                @endif

                                                @if ($cp->quotationProduct->add_spec ?? false)
                                                    <div>
                                                        {{ $cp->quotationProduct->add_spec }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="text-align:center;" class="border border-gray-300">
                                                {{ $cp->quotationProduct->unit }}</td>
                                            <td style="text-align:right;" class="border border-gray-300">
                                                {{ $cp->quantity }}</td>
                                            <td style="text-align:left;" class="border border-gray-300">
                                                {{ $cp->remarks ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- Signature and Notes --}}
                    <div class="screen-footer">
                        <div class="q-sign">
                            <div class="sig-box">
                                <div style="height:72px;"></div>
                                <div style="border-top:2px solid #374151; padding-top:6px; font-weight:700;">Authorized
                                    By
                                </div>
                            </div>
                            <div class="sig-box">
                                <div style="height:72px;"></div>
                                <div style="border-top:2px solid #374151; padding-top:6px; font-weight:700;">Received By
                                </div>
                            </div>

                        </div>
                        <div class="w-full border-t border-gray-300 mt-6"></div>
                        <div
                            class="mt-1 p-2.5 border-t-3 text-red-600 border-blue-600 bg-gray-50 text-center text-xs underline">
                            <div>"Complete Solution for Pharma, Food, Power, and Other Industries"</div>
                        </div>
                        <div class="text-center text-sm mt-1.5 font-bold uppercase">Thank You For Your Business!</div>
                    </div>

                    <div class="print-footer-challan">
                        <div class="q-sign">
                            <div class="sig-box">
                                <div style="height:72px;"></div>
                                <div style="border-top:2px solid #374151; padding-top:6px; font-weight:700;">Authorized
                                    By
                                </div>
                            </div>
                            <div class="sig-box">
                                <div style="height:72px;"></div>
                                <div style="border-top:2px solid #374151; padding-top:6px; font-weight:700;">Received By
                                </div>
                            </div>

                        </div>
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

    <script>
        // Print current invoice card with styles
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#q-invoice').printThis({
                importCSS: true,
                importStyle: true,
                copyTagStyles: true,
                printDelay: 500,
                beforePrintEvent: function() {
                    var footer = document.querySelector('.print-footer-challan');
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
