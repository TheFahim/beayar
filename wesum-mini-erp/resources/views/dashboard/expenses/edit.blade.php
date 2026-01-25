<x-dashboard.layout.default title="Edit Expense">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('expense.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Expense
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Edit Expense">
        <div class="flex flex-col items-center justify-center">
            {{-- Initializing Alpine.js component with existing expense data --}}
            <div x-data="editExpenseForm({{ json_encode($expense->type) }})" class="w-full max-w-md">

                <form action="{{ route('expense.update', $expense->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-2 mx-5">
                        <div class="px-2 space-y-3">

                            <label for="expense-datepicker"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Date
                                <span
                                    class="bg-red-100 text-red-800 text-xs font-medium px-1 py-0.5 rounded dark:bg-red-900 dark:text-red-300">required
                                </span>
                            </label>
                            <div class="relative max-w-sm">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <x-ui.svg.calendar class="h-4 w-4" />
                                </div>
                                <input id="expense-datepicker" name="date" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Select date" value="{{ $expense->date }}">
                            </div>

                            {{-- Expense Type Selection --}}
                            <div class="relative max-w-sm">
                                <label for="type"
                                   class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Expense Type
                                     <span
                                    class="bg-red-100 text-red-800 text-xs font-medium px-1 py-0.5 rounded dark:bg-red-900 dark:text-red-300">required
                                     </span>
                                </label>
                                {{-- The name attribute is conditional. If 'others' is selected, it's removed to avoid sending "others" as the value --}}
                                <x-ui.form.simple-select x-model="selectedType"
                                    x-bind:name="selectedType === 'others' ? '' : 'type'"
                                    class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="transport">Transport</option>
                                    <option value="food">Food</option>
                                    <option value="phone">Phone</option>
                                    <option value="others">Others</option>
                                </x-ui.form.simple-select>

                                {{-- This input only appears if "Others" is selected --}}
                                <template x-if="selectedType === 'others'">
                                    <div class="mt-2">
                                        <x-ui.form.input
                                            name="type"
                                            x-model="otherTypeName"
                                            type="text"
                                            placeholder="Specify other type"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                </template>
                            </div>

                            <div class="relative max-w-sm">
                                <x-ui.form.input label="Amount" name="amount" type="number" value="{{ $expense->amount }}"
                                    required />
                            </div>

                            <div class="relative max-w-sm">
                                <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                    Remarks
                                </label>
                                <textarea id="remarks"
                                    name="remarks"
                                    rows="4"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Add your remarks here...">{{ old('remarks', $expense->remarks) }}</textarea>
                            </div>

                            <div class="relative max-w-sm">
                                <x-ui.form.image-upload name="voucher"
                                    oldData="{{ $expense->voucher ? asset($expense->voucher) : '' }}" />
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 my-10 mx-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
                    <button form="delete-form" type="button"
                        class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800 delete-button">Delete</button>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('expense.destroy', $expense->id) }}" id="delete-form"
            class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </x-ui.card>
</x-dashboard.layout.default>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('editExpenseForm', (initialType) => ({
            selectedType: 'transport',
            otherTypeName: '',
            init() {
                const standardTypes = ['transport', 'food', 'phone'];
                // Check if the initial type from the database is a standard one
                if (standardTypes.includes(initialType)) {
                    this.selectedType = initialType;
                } else {
                    // If not, it's a custom type
                    this.selectedType = 'others';
                    this.otherTypeName = initialType;
                }
            }
        }));
    });
</script>
