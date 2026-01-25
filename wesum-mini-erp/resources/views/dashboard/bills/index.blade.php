<x-dashboard.layout.default title="Bills">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        <hr class="border-t border-gray-300 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="team-members"
                class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-6 py-3">S/L</th>
                        <th scope="col" class="px-6 py-3">Bill NO.</th>
                        <th scope="col" class="px-6 py-3">Company</th>
                        <th scope="col" class="px-6 py-3">Products</th>
                        <th scope="col" class="px-6 py-3 text-right">Total</th>
                        <th scope="col" class="px-6 py-3 text-right">Status</th>
                        @role('admin')
                            <th scope="col" class="px-6 py-3">User</th>
                        @endrole
                        <th scope="col" class="px-6 py-3">Created</th>
                        <th scope="col" class="px-6 py-3"><span class="sr-only">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bills as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            @role('admin')
                                data-modal-target="billDetailsModal"
                                data-modal-toggle="billDetailsModal"
                                style="cursor: pointer;"
                                data-bill-no="{{ $item->bill_no }}"
                                data-po-no="{{ $item->challan->po_no ?? 'N/A' }}"
                                data-payable="{{ number_format($item->payable, 2) }}"
                                data-att="{{ number_format($item->att, 2) ?? '0.00' }}"
                                data-vat="{{ number_format($item->vat, 2) ?? '0.00' }}"
                                data-delivery-cost="{{ number_format($item->delivery_cost, 2) ?? '0.00' }}"
                                data-buying-price="{{ number_format($item->buying_price, 2) ?? '0.00' }}"
                                data-profit="{{ number_format($item->profit, 2) ?? '0.00' }}"
                            @endrole>
                            <td class="px-6 py-4 flex gap-2">
                                {{ $loop->iteration }}
                                @role('admin')
                                    @if ($item->buying_price <= 0)
                                        <x-ui.svg.warning-tirangle class="w-6 h-6" />
                                    @endif
                                @endrole
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->bill_no }}
                            </th>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{-- {{ $item->challan->quotation->customer->company_name }} --}}
                                {{ strlen($item->challan->quotation->customer->company_name) > 15 ? substr($item->challan->quotation->customer->company_name, 0, 15) . '...' : $item->challan->quotation->customer->company_name }}

                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @php
                                    $products = $item->challan->quotation->products;
                                    $maxToShow = 2;
                                @endphp
                                @foreach ($products->take($maxToShow) as $index => $product)
                                    {{ $index + 1 }}. {{ $product->name }}<br>
                                @endforeach
                                @if ($products->count() > $maxToShow)
                                    ...
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                {{ $item->payable }} &#2547;
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                @if ($item->due <= 0)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Paid
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
                                        Due
                                    </span>
                                @endif
                            </td>
                            @role('admin')
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->challan->quotation->user->username }}
                                </td>
                            @endrole
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ date('d/m/Y H:i', strtotime($item->created_at)) }}
                            </th>
                            <td class="grid grid-cols-2 px-2 py-4 gap-x-10">
                                <div>
                                    <a href="{{ route('bills.show', $item->id) }}"
                                        class="action-link font-medium text-blue-600 dark:text-green-500 hover:underline">View</a>
                                </div>
                                <div>
                                    <a href="{{ route('bills.edit', $item->id) }}"
                                        class="action-link font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @role('admin')
        <!-- Main modal -->
        <div id="billDetailsModal" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
            <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                    <!-- Modal header -->
                    <div class="flex justify-between items-start p-4 rounded-t border-b dark:border-gray-600">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white" id="modal_bill_no"></h3>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400" id="modal_po_no"></p>
                        </div>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="billDetailsModal">
                            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <!-- Payable -->
                            <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payable Amount</dt>
                                <dd class="mt-1 text-xl font-extrabold text-green-600 dark:text-green-400"
                                    id="modal_payable"></dd>
                            </div>
                            <!-- Profit -->
                            <div class="p-3 bg-gray-50 rounded-lg dark:bg-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit</dt>
                                <dd class="mt-1 text-xl font-extrabold text-blue-600 dark:text-blue-400" id="modal_profit">
                                </dd>
                            </div>
                            <!-- Buying Price -->
                            <div class="p-3">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Buying Price</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white"
                                    id="modal_buying_price"></dd>
                            </div>
                            <!-- AIT -->
                            <div class="p-3">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ATT</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white" id="modal_att"></dd>
                            </div>
                            <!-- VAT -->
                            <div class="p-3">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">VAT</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white" id="modal_vat"></dd>
                            </div>
                            <!-- Delivery Cost -->
                            <div class="p-3">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivery Cost</dt>
                                <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white"
                                    id="modal_delivery_cost"></dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // --- Prevent Modal trigger on action links ---
                document.querySelectorAll('.action-link').forEach(link => {
                    link.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                });

                // --- Populate Modal with data ---
                const modalBillNo = document.getElementById('modal_bill_no');
                const modalPoNo = document.getElementById('modal_po_no');
                const modalPayable = document.getElementById('modal_payable');
                const modalAtt = document.getElementById('modal_att');
                const modalVat = document.getElementById('modal_vat');
                const modalDeliveryCost = document.getElementById('modal_delivery_cost');
                const modalBuyingPrice = document.getElementById('modal_buying_price');
                const modalProfit = document.getElementById('modal_profit');

                document.querySelectorAll('[data-modal-toggle="billDetailsModal"]').forEach(trigger => {
                    trigger.addEventListener('click', () => {
                        // We only want to populate data if the trigger is the table row (TR)
                        if (trigger.tagName === 'TR') {
                            modalBillNo.textContent = `Bill No: ${trigger.dataset.billNo}`;
                            modalPoNo.textContent = `PO No: ${trigger.dataset.poNo}`;
                            modalPayable.textContent = `${trigger.dataset.payable} Tk`;
                            modalAtt.textContent = `${trigger.dataset.att} Tk`;
                            modalVat.textContent = `${trigger.dataset.vat} Tk`;
                            modalDeliveryCost.textContent = `${trigger.dataset.deliveryCost} Tk`;
                            modalBuyingPrice.textContent = `${trigger.dataset.buyingPrice} Tk`;
                            modalProfit.textContent = `${trigger.dataset.profit} Tk`;
                        }
                    });
                });
            });
        </script>
    @endrole

</x-dashboard.layout.default>
