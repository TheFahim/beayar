<x-dashboard.layout.default :title="'Challan - ' . $challan->challan_no">
    <style>
        /* Hide the watermark on the screen view by default */
        .print-watermark {
            display: none;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 0;
            }

            .action-bar {
                display: none;
            }

            /* --- STACKING CONTEXT AND WATERMARK --- */
            #challan-invoice-container {
                position: relative;
            }

            .print-watermark {
                display: block;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                background: url("{{ asset('assets/images/app-logo.jpeg') }}") center/contain no-repeat;
                width: 70vmin;
                height: 70vmin;
                opacity: 0.08;
                pointer-events: none;
                z-index: 0;
            }

            #challan-invoice-container > table {
                position: relative;
                z-index: 1;
            }

            /* --- REPEATING HEADER/FOOTER --- */
            #challan-invoice-container thead {
                display: table-header-group;
            }

            #challan-invoice-container tfoot {
                display: table-footer-group;
            }

            #products-table tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>

    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('challans.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Challans
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Challan" />
    </x-dashboard.ui.bread-crumb>

    <div class="bg-gray-100 p-8 font-sans">
        <div class="flex justify-between p-4 action-bar">
            {{-- Buttons... --}}
            <div class="flex items-center space-x-2">
                <button id="printBtn" type="button" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5"><svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" /></svg><span>&nbsp;&nbsp;Print</span></button>

            </div>
            @if (!$hasBill)
                 <a href="{{ route('bills.create', ['challan_id' => $challan->id]) }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">Proceed To Bill <svg class="w-6 h-6 text-white ml-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg></a>
            @else
                <a href="{{ route('bills.show', $challan->bill->id) }}" class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">Show Bill <svg class="w-6 h-6 text-white ml-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg></a>
            @endif
        </div>

        <div id="challan-invoice-container" class="max-w-4xl mx-auto bg-white px-8">
            <div class="print-watermark"></div>
            <table class="w-full">
                <thead>
                    <tr>
                        <td>
                            <header class="flex justify-start items-start my-8">
                                <div class="text-left">
                                    <h2 class="text-xl font-bold text-blue-600 mb-4">Challan</h2>
                                    <div class="grid grid-cols-2 text-sm">
                                        <div class="font-bold p-1 text-left">Challan No</div>
                                        <div class="border border-gray-400 p-1 bg-blue-100 font-semibold">{{ $challan->challan_no }}</div>
                                        <div class="font-bold p-1 text-left">CUSTOMER ID</div>
                                        <div class="border border-gray-400 p-1">{{ $challan->quotation->customer->customer_no }}</div>
                                        <div class="font-bold p-1 text-left">PO No</div>
                                        <div class="border border-gray-400 p-1 bg-blue-100 font-semibold">{{ $challan->po_no }}</div>
                                        <div class="font-bold p-1 text-left">DELIVERY DATE</div>
                                        <div class="border border-gray-400 p-1">{{ $challan->delivery_date ? \Carbon\Carbon::parse($challan->delivery_date)->format('d/m/Y') : 'N/A' }}</div>
                                    </div>
                                </div>
                            </header>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <!--
                                ** THE FIX **
                                Replaced the nested table with a Flexbox layout.
                                This is the best method for creating equal-height columns.
                            -->
                            <section class="mb-4 text-sm">
                                {{-- `items-stretch` makes the direct children (the two w-1/2 divs) equal height. --}}
                                <div class="flex justify-between items-stretch gap-x-8">
                                    {{-- Delivered By Box --}}
                                    {{-- `flex-col` stacks the h3 and content div vertically. --}}
                                    <div class="w-1/2 flex flex-col">
                                        <h3 class="bg-blue-900 text-white font-bold p-2 text-sm">Delivered By</h3>
                                        {{-- `flex-grow` makes this div expand to fill all available vertical space in its column. --}}
                                        <div class="border border-gray-400 p-3 space-y-1 break-words">
                                            <p><strong class="w-full inline-block text-xs">WESUM CORPORATION</strong></p>
                                            <p><strong class="w-16 inline-block text-xs">Phone</strong> : 01889977489</p>
                                            {{-- <p><strong class="w-16 inline-block text-xs">Web Site</strong> <a href="https://wesumcorporation.com/" class="text-blue-600 hover:underline text-xs"> : https://wesumcorporation.com</a></p> --}}
                                            <p><strong class="w-16 inline-block text-xs">Address</strong> : 78/1, Hasanlen, Dattapara, Tongi, Gazipur.</p>
                                            <p><strong class="w-16 inline-block text-xs">BIN no.</strong> : 006482594-0102</p>

                                        </div>
                                    </div>

                                    {{-- Bill To Box --}}
                                    <div class="w-1/2 flex flex-col">
                                        <h3 class="bg-blue-900 text-white font-bold p-2 text-sm">Bill To</h3>
                                        <div class="border border-gray-400 p-3 space-y-1 break-words">
                                            <p><strong class="w-24 inline-block text-xs">Company</strong> : {{ $challan->quotation->customer->company_name }}</p>
                                            <p><strong class="w-24 inline-block text-xs">Address</strong> : {{ $challan->quotation->customer->address }}</p>
                                            {{-- <p><strong class="w-24 inline-block text-xs">Phone</strong> : {{ $challan->quotation->customer->phone }}</p> --}}
                                            <p><strong class="w-24 inline-block text-xs">BIN no.</strong> : {{ $challan->quotation->customer->bin_no }}</p>

                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <table id="products-table" class="w-full border-collapse text-sm">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 w-12">SL</th>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left">ITEM NAME</th>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-16">QTY</th>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-center w-20">Unit</th>
                                            <th colspan="1" class="bg-blue-900 text-white p-2 border border-gray-500 text-left w-32">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($challan->quotation->products as $product)
                                            <tr>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">{{ $loop->iteration }}</td>
                                                <td class="border border-gray-400 p-2 align-top font-bold text-xs">{{ $product->name }}</td>

                                                <td class="border border-gray-400 p-2 text-right align-top text-xs">{{ $product->quantity }}</td>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">{{ $product->unit }}</td>
                                                <td colspan="1" class="border border-gray-400 p-2 align-top whitespace-nowrap text-xs">{{ $product->remarks ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </section>

                            <section class="mt-8 print:break-inside-avoid">
                                <h3 class="bg-blue-900 text-white font-bold p-2 mb-4 text-lg">Notes</h3>
                                <ul class="list-disc list-inside text-sm space-y-2 text-gray-700">
                                    <li>Goods delivered as per above details.</li>
                                    <li>Please check and confirm receipt of goods in good condition.</li>
                                    <li>Any discrepancy should be reported within 24 hours of delivery.</li>
                                </ul>
                            </section>
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <td>
                            <div class="h-[110px]"></div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script>
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#challan-invoice-container').printThis({
                importCSS: true,
                importStyle: true,
            });
        });
    </script>
</x-dashboard.layout.default>
