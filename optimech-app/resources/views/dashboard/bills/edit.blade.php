<x-dashboard.layout.default title="Edit Bill">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit Bill No." />
    </x-dashboard.ui.bread-crumb>

    <div>
        <h2 class="mx-5 text-xl font-extrabold dark:text-white">Edit Bill</h2>

        <form class="space-y-3" action="{{ route('bills.update', $bill->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-ui.card>
                <div class="grid grid-cols-3 p-6">
                    <div>

                        <x-ui.form.input name="bill_no" label="Bill No." placeholder="Ex. BILL-0001"
                            class="w-full p-2 text-lg" value="{{ old('bill_no', $bill->bill_no) }}" required />
                    </div>
                </div>
            </x-ui.card>
            @role('admin')
                <x-ui.card heading="Cost">


                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        P.O
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Total (P.O) ৳
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Buying Price ৳
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Delivery Cost ৳
                                    </th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $bill->challan->po_no }}
                                    </th>
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $bill->challan->quotation->total }}
                                    </th>
                                    <td class="px-6 py-4">
                                        <x-ui.form.input name="buying_price" placeholder="Total Buying Price" type="number" step="0.01"
                                            class="w-full p-2 text-lg" value="{{ old('buying_price', $bill->buying_price) }}"
                                            required />
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-ui.form.input name="delivery_cost" placeholder="Delivery Cost" type="number" step="0.01"
                                            class="w-full p-2 text-lg" value="{{ old('delivery_cost', $bill->delivery_cost) }}"
                                            required />
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                </x-ui.card>
            @endrole

            <x-ui.card>
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mx-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Update</button>
                    @role('admin')
                        <button form="delete-form" type="button"
                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800 delete-button">Delete</button>
                    @endrole
            </x-ui.card>
        </form>
        <form method="POST" action="{{ route('bills.destroy', $bill->id) }}" id="delete-form" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-dashboard.layout.default>
