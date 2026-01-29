<x-dashboard.layout.default title="View Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.qutation class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <x-ui.svg.arrow-left class="h-5 w-5 text-gray-400 mx-1" />
                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">{{ $quotation->reference_no }}</span>
            </div>
        </li>
    </x-dashboard.ui.bread-crumb>

    <div class="flex justify-end mb-4 gap-2 no-print">
        <button onclick="window.print()" class="flex items-center gap-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            <x-ui.svg.printer class="w-4 h-4" />
            Print / PDF
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-4xl mx-auto print:shadow-none print:w-full" id="quotation-print">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8 border-b pb-8 dark:border-gray-700">
            <div>
                <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-2">QUOTATION</h1>
                <p class="text-gray-500 dark:text-gray-400">Reference: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $quotation->reference_no }}</span></p>
                <p class="text-gray-500 dark:text-gray-400">Date: {{ \Carbon\Carbon::parse($quotation->issue_date)->format('M d, Y') }}</p>
                <p class="text-gray-500 dark:text-gray-400">Expiry: {{ \Carbon\Carbon::parse($quotation->expiry_date)->format('M d, Y') }}</p>
            </div>
            <div class="text-right">
                <!-- Company Logo/Info Placeholders -->
                <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ auth()->user()->company->name ?? 'Your Company Name' }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">123 Business Street</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">City, Country, 12345</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Phone: +1 234 567 890</p>
            </div>
        </div>

        <!-- Customer & Bill To -->
        <div class="flex justify-between mb-8">
            <div class="w-1/2">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2 dark:text-gray-400">Bill To:</h3>
                <h4 class="text-lg font-bold text-gray-800 dark:text-white">{{ $quotation->customer->name }}</h4>
                <p class="text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $quotation->customer->address }}</p>
                <p class="text-gray-600 dark:text-gray-300 mt-1">{{ $quotation->customer->email }}</p>
                <p class="text-gray-600 dark:text-gray-300">{{ $quotation->customer->phone }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                        <th class="py-3 px-2 font-semibold text-gray-700 dark:text-gray-200">Item Description</th>
                        <th class="py-3 px-2 text-center font-semibold text-gray-700 dark:text-gray-200 w-24">Qty</th>
                        <th class="py-3 px-2 text-right font-semibold text-gray-700 dark:text-gray-200 w-32">Unit Price</th>
                        <th class="py-3 px-2 text-right font-semibold text-gray-700 dark:text-gray-200 w-32">Total</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 dark:text-gray-300">
                    @if($quotation->activeRevision)
                        @foreach($quotation->activeRevision->items as $item)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-4 px-2">
                                <p class="font-medium text-gray-800 dark:text-white">{{ $item->product->name ?? 'Product' }}</p>
                                <!-- Specs or Description if available -->
                            </td>
                            <td class="py-4 px-2 text-center">{{ $item->quantity }}</td>
                            <td class="py-4 px-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-4 px-2 text-right font-medium">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-1/2 sm:w-1/3">
                <div class="flex justify-between py-2 text-gray-600 dark:text-gray-300">
                    <span>Subtotal:</span>
                    <span class="font-medium">{{ number_format($quotation->activeRevision->subtotal ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 text-gray-600 dark:text-gray-300">
                    <span>Tax:</span>
                    <span class="font-medium">{{ number_format($quotation->activeRevision->tax_total ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 text-gray-600 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 pb-2">
                    <span>Discount:</span>
                    <span class="font-medium">-{{ number_format($quotation->activeRevision->discount_total ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between py-3 text-xl font-bold text-gray-800 dark:text-white">
                    <span>Total:</span>
                    <span>{{ number_format($quotation->activeRevision->grand_total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes & Terms -->
        <div class="border-t pt-8 dark:border-gray-700">
            @if($quotation->activeRevision && $quotation->activeRevision->notes)
            <div class="mb-4">
                <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Notes:</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $quotation->activeRevision->notes }}</p>
            </div>
            @endif

            @if($quotation->activeRevision && $quotation->activeRevision->terms)
            <div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Terms & Conditions:</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $quotation->activeRevision->terms }}</p>
            </div>
            @endif
        </div>
    </div>
    
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
            }
            #quotation-print {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: none;
            }
        }
    </style>
</x-dashboard.layout.default>
