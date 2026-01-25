<x-dashboard.layout.default :title="'Quotation - ' . $quotation->quotation_no">
    <style>
        /* Hide watermark on screen by default */
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

            /* 1. Set up the container as the positioning context */
            #q-invoice {
                position: relative;
            }

            /* 2. Define the watermark's appearance and fixed position */
            .print-watermark {
                display: block;
                /* Make it visible for print */
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                /* Center the watermark */
                background: url("{{ asset('assets/images/app-logo.jpeg') }}") center/contain no-repeat;
                /* Use the logo */
                width: 70vmin;
                /* Adjust size as needed */
                height: 70vmin;
                opacity: 0.06;
                /* Lighter opacity */
                pointer-events: none;
                /* Ignore mouse events */
                z-index: 0;
                /* Place watermark on the bottom layer */
            }

            /* 3. Ensure the content prints on top of the watermark */
            #q-invoice>table {
                position: relative;
                z-index: 1;
                /* Content is on the top layer */
            }

            /* 4. Ensure ONLY the table header repeats. The footer will now be in the body. */
            thead {
                display: table-header-group;
            }

            tfoot {
                display: none;
            }

            /* Explicitly hide tfoot in case it's added back by mistake */
            tr {
                page-break-inside: avoid;
            }

            .avoid-break {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>

    {{-- Breadcrumb and controls remain outside the printable area --}}
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{  $quotation->type == 2 ? route('pre.quotation') : route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                {{ $quotation->type == 2 ? 'Pre-Quotations' : 'Quotations' }}
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="{{ $quotation->type == 2 ? 'Pre-Quotation' : 'Quotation' }}" />
    </x-dashboard.ui.bread-crumb>

    <div class="bg-gray-100 p-8 font-sans">
        <div class="flex justify-between p-4">
            <button id="printBtn" type="button"
                class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                        d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                </svg>
                <span>&nbsp;&nbsp;Print</span>
            </button>
            {{-- Other buttons... --}}
            @if ($quotation->type == 1)
                @if (!$hasChallan)
                    <a href="{{ route('challans.create', ['quotation_id' => $quotation->id]) }}"
                        class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                        Proceed To Challan

                        <svg class="w-6 h-6 text-gray-800 dark:text-white ml-1" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 12H5m14 0-4 4m4-4-4-4" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('challans.show', $quotation->challan->id) }}"
                        class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                        Show Challan

                        <svg class="w-6 h-6 text-gray-800 dark:text-white ml-1" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 12H5m14 0-4 4m4-4-4-4" />
                        </svg>
                    </a>
                @endif

            @endif

        </div>

        <div id="q-invoice" class="max-w-4xl mx-auto bg-white px-8 pt-5">
            <div class="print-watermark"></div>
            <table class="w-full">
                {{-- Repeating Header --}}
                <thead>
                    <tr>
                        <td>
                            <header class="flex justify-between items-start my-4">
                                <div>
                                    <div class="flex items-center space-x-2 mb-2">
                                        <img src="{{ asset('assets/images/app-logo.jpeg') }}" alt="Company Logo"
                                            class="h-16 mb-2">
                                        <h1 class="text-2xl font-serif font-bold tracking-wider">
                                            <span class="text-blue-900">WESUM</span>
                                            <span class="text-red-700">CORPORATION</span>
                                        </h1>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <h2 class="text-2xl font-bold text-blue-600 mb-4">Quotation</h2>
                                    <div class="grid grid-cols-2 text-sm">
                                        <div class="font-bold px-3 text-right">DATE</div>
                                        <div class="border border-gray-400 p-1">
                                            {{ $quotation->created_at->format('d/m/Y') }}</div>
                                        <div class="font-bold px-3 text-right">Ref</div>
                                        <div class="border border-gray-400 p-1 bg-blue-100 font-semibold">
                                            {{ $quotation->quotation_no }}</div>
                                        <div class="font-bold px-3 text-right">CUSTOMER ID</div>
                                        <div class="border border-gray-400 p-1">{{ $quotation->customer->customer_no }}
                                        </div>
                                    </div>
                                </div>
                            </header>
                        </td>
                    </tr>
                </thead>

                {{-- Main Document Body --}}
                <tbody>
                    <tr>
                        <td>
                            <section class="grid grid-cols-2 gap-8 mb-8 text-sm">
                                <div>
                                    <h3 class="bg-blue-900 text-white font-bold p-2 text-sm">Contact Details</h3>
                                    <div class="border border-gray-400 p-3">
                                        <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1 text-xs">
                                            <strong class="font-bold text-xs">Name</strong>
                                            <p>: Reazul Kabir</p>
                                            <strong class="font-bold text-xs">Phone</strong>
                                            <p>: 01889977489</p>
                                            <strong class="font-bold text-xs">Web</strong>
                                            <p>: <a href="https://wesumcorporation.com/"
                                                    class="text-blue-600 hover:underline"
                                                    target="_blank text-xs">https://wesumcorporation.com</a></p>
                                            <strong class="font-bold text-xs">Address</strong>
                                            <p>: 78/1, Hasanlen, Dattapara, Tongi, Gazipur.</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="bg-blue-900 text-white font-bold p-2 text-sm">Kind Attention</h3>
                                    <div class="border border-gray-400 p-3 text-xs">
                                        <div class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1 text-xs">
                                            <strong class="font-bold text-xs">Name</strong>
                                            <p>: {{ $quotation->customer->customer_name }}</p>
                                            <strong class="font-bold text-xs">Designation</strong>
                                            <p>: {{ $quotation->customer->designation }}</p>
                                            <strong class="font-bold text-xs">Company</strong>
                                            <p>: {{ $quotation->customer->company_name }}</p>
                                            <strong class="font-bold text-xs">Address</strong>
                                            <p>: {{ $quotation->customer->address }}</p>
                                            {{-- <strong class="font-bold">Phone</strong>
                                            <p>: {{ $quotation->customer->phone }}</p> --}}
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <table class="w-full border-collapse text-sm">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-xs">SL
                                            </th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                ITEM NAME</th>
                                            <th colspan="2"
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                SPECIFICATIONS</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right text-xs">
                                                UNIT PRICE</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right text-xs">
                                                QTY</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-center text-xs">
                                                Unit</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right text-xs">
                                                AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->products as $product)
                                            <tr>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">
                                                    {{ $loop->iteration }}</td>
                                                <td class="border border-gray-400 p-2 align-top font-bold text-xs">
                                                    {{ $product->name }}</td>
                                                <td colspan="2" class="border border-gray-400 p-2 align-top text-xs">
                                                    {!! $product->specs !!}
                                                </td>
                                                <td class="border border-gray-400 p-2 text-right align-top text-xs">
                                                    {{ number_format($product->price, 2) }}</td>
                                                <td class="border border-gray-400 p-2 text-right align-top text-xs">
                                                    {{ $product->quantity }}</td>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">
                                                    {{ $product->unit }}</td>
                                                <td
                                                    class="border border-gray-400 p-2 text-right align-top font-bold text-xs">
                                                    {{ number_format($product->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="6"
                                                class="font-bold p-2 text-right border border-gray-400 text-xs">
                                                SUBTOTAL</td>
                                            {{-- <td class="font-bold p-2 text-right border border-gray-400 text-xs">SUBTOTAL</td> --}}
                                            <td colspan="2"
                                                class="text-right p-2 font-semibold border border-gray-400 text-xs">
                                                {{ number_format($quotation->subtotal, 2) }} &#2547;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"
                                                class="font-bold p-2 text-right border border-gray-400 text-xs">VAT
                                                ({{ (int) $quotation->vat }}%)</td>
                                            {{-- <td class="font-bold p-2 text-right border border-gray-400 text-xs"></td> --}}
                                            <td colspan="2"
                                                class="text-right p-2 font-semibold border border-gray-400 text-xs">
                                                {{ $quotation->vat > 0 ? number_format($quotation->subtotal * ($quotation->vat / 100), 2) : '0.00' }}
                                                &#2547;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"
                                                class="font-bold p-2 text-right border border-gray-400 text-xs">GRAND
                                                TOTAL</td>
                                            {{-- <td class="font-bold p-2 text-right border border-gray-400 bg-gray-200 text-xs"></td> --}}
                                            <td colspan="2"
                                                class="text-right p-2 font-bold border border-gray-400 bg-gray-200 text-xs">
                                                {{ number_format($quotation->total, 2) }} &#2547;
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </section>

                            <section class="avoid-break">
                                <section class="flex mt-8 text-sm place-content-between">
                                    <div class="w-1/3 text-center place-content-end">
                                        <img src="{{ asset('assets/images/wesum-sign.jpeg') }}" style="width: 80%"
                                            class="mx-auto">
                                        <div class="border-t border-gray-600 mt-1">
                                            <p class="text-sm font-semibold">Authorized Signature</p>
                                        </div>
                                    </div>

                                </section>

                                <section class="my-4 text-xs">
                                    {!! $quotation->terms_conditions !!}
                                </section>

                                <footer class="px-4 border-t border-gray-300 text-xs text-gray-600 text-center">
                                    If you have any questions about this price quote, please contact
                                    <br>
                                    <div class="font-bold">
                                        Thank You For Your Business!
                                    </div>
                                </footer>
                            </section>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <script>
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#q-invoice').printThis({
                importCSS: true,
                importStyle: true,
            });
        });
    </script>
</x-dashboard.layout.default>
