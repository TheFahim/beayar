<x-dashboard.layout.default title="New Expenses">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('expense.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Expense
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New Expenses">
        <form action="{{ route('expense.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-2 mx-5">
                <div class="grid grid-cols-3">
                    <div class="px-2">

                        <div class="relative max-w-sm">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                <x-ui.svg.calendar class="h-4 w-4" />
                            </div>
                            <input id="expense-datepicker" name="date" type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Select date">
                        </div>

                    </div>


                    <div class="p-2 col-span-3">
                        <div class="grid grid-cols-1">

                            <div class="w-full space-y-4" x-data="clonableInputs">
                                <div class="flex items-center my-4">
                                    <h1 class="text-xl font-bold dark:text-white">Add Expenses</h1>
                                    <button type="button"
                                        class="px-2 py-1 mx-2 dark:text-white rounded hover:bg-gray-600"
                                        @click="addRow">
                                        <x-ui.svg.circle-plus class="w-6 h-6 " />
                                    </button>
                                </div>

                                <!-- Input Rows -->
                                <template x-for="(row, index) in rows" :key="index">
                                    <div class="grid items-center gap-4"
                                        style="grid-template-columns: repeat(10, minmax(0, 1fr));">
                                        <!-- Dropdown Select -->
                                        <div class="col-span-2">
                                            {{--
                    When 'others' is selected, the name attribute is removed (by binding to null).
                    This prevents it from being submitted and clashing with the input below.
                --}}
                                            <x-ui.form.simple-select x-model="row.type"
                                                x-bind:name="row.type === 'others' ? null : 'expense[' + index + '][type]'"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                                <option value="transport">Transport</option>
                                                <option value="food">Food</option>
                                                <option value="phone">Phone</option>
                                                <option value="others">Others</option>
                                            </x-ui.form.simple-select>

                                            <template x-if="row.type === 'others'">
                                                <div class="mt-2">
                                                    <x-ui.form.input x-bind:name="'expense[' + index + '][type]'"
                                                        x-model="row.other_type" type="text"
                                                        placeholder="Specify Other Type"
                                                        class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                                        required />
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Input Field -->
                                        <div class=" col-span-2">
                                            <x-ui.form.input x-bind:name="'expense[' + index + '][amount]'"
                                                x-model="row.amount" type="number" placeholder="Amount (Tk)"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                                required />
                                        </div>

                                        <div class=" col-span-2">
                                            <input
                                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                id="file_input" type="file"
                                                x-bind:name="'expense[' + index + '][voucher]'">
                                        </div>

                                        <div class=" col-span-4">
                                            <x-ui.form.input x-bind:name="'expense[' + index + '][remarks]'"
                                                x-model="row.remarks" type="text" placeholder="Remarks"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100" />
                                        </div>


                                        <!-- Remove Button -->
                                        <div class="px-1">
                                            <button type="button"
                                                class="col-span-2 flex items-center justify-center p-2 bg-red-500 text-white rounded hover:bg-red-600"
                                                @click="rows.splice(index, 1)" x-show="rows.length > 1">
                                                <x-ui.svg.close class="h-6 w-6" />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 my-10 mx-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
        </form>

    </x-ui.card>

</x-dashboard.layout.default>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clonableInputs', () => ({
            rows: {!! json_encode(
                old('expense', [['type' => '', 'amount' => '', 'voucher' => '', 'remarks' => '', 'other_type' => '']]),
            ) !!},
            addRow() {
                this.rows.push({
                    type: '',
                    amount: '',
                    voucher: '',
                    remarks: '',
                    other_type: ''
                });
            },

        }));
    });
</script>
