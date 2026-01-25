<x-dashboard.layout.default title="New Payments">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('received-bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Expense
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New Payments">
        <form action="{{ route('received-bills.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-2 mx-5">
                <div class="grid grid-cols-3">
                    <div>
                        <x-ui.form.searchable-select name="bill_id" x-model="bill_id"
                            api-endpoint="{{ route('bills.search') }}" />
                    </div>

                    <div class="p-2 col-span-3">
                        <div class="grid grid-cols-1">

                            <div class="w-full space-y-4" x-data="clonableInputs">
                                <div class="flex items-center my-4">
                                    <h1 class="text-xl font-bold dark:text-white">Add Payment</h1>
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

                                            <!-- Inside your <template x-for="..."> -->

                                            <div class="relative max-w-sm">
                                                <div
                                                    class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                                    <x-ui.svg.calendar class="h-4 w-4" />
                                                </div>
                                                <input {{-- Give each input a unique name for submission --}}
                                                    x-bind:name="'payment[' + index + '][received_date]'" type="text"
                                                    {{-- Add the custom x-datepicker directive --}} x-datepicker {{-- Bind the value to the Alpine row data --}}
                                                    x-model="row.date"
                                                    class="flowbite-datepicker bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Reveived Date" autocomplete="off" {{-- Recommended for datepickers --}}>
                                            </div>
                                        </div>

                                        <!-- Input Field -->
                                        <div class=" col-span-2">
                                            <x-ui.form.input x-bind:name="'payment[' + index + '][amount]'"
                                                x-model="row.amount" type="number" placeholder="Amount (Tk)"
                                                class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                                required />
                                        </div>



                                        <div class=" col-span-4">
                                            <x-ui.form.input x-bind:name="'payment[' + index + '][details]'"
                                                x-model="row.remarks" type="text" placeholder="Details"
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
    // This helper function can stay as it is, at the top level of your script.
    function formatDateToDDMMYYYY(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        // Check if the date is valid before formatting
        if (isNaN(date.getTime())) {
            return '';
        }
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }


    document.addEventListener('alpine:init', () => {
        // --- Alpine Component for clonable inputs ---
        Alpine.data('clonableInputs', () => ({
            // Your existing data from Laravel 'old()' helper
            rows: {!! json_encode(old('payment', [['received_date' => '', 'amount' => '', 'details' => '']])) !!},

            init() {
                // When using 'old()' values, the dates might need re-formatting on page load.
                // We loop through the initial rows and format the date for each one.
                this.rows.forEach(row => {
                    if (row.received_date != null ) {
                        row.received_date = formatDateToDDMMYYYY(row.date);
                    }
                });
            },

            addRow() {
                this.rows.push({
                    received_date: '', // Start with an empty date
                    amount: '',
                    details: '',
                });
            },
        }));

        // --- Custom Alpine Directive for Flowbite Datepicker ---
        Alpine.directive('datepicker', (el, _, {
            cleanup
        }) => {
            // Define options inside the directive or have them in a shared scope
            const options = {
                autohide: true,
                format: 'dd/mm/yyyy',
                orientation: 'bottom',
                title: null,
                autoSelectToday: true, // Good practice for new rows
            };

            // Initialize the datepicker on the element `el`
            const datepicker = new Datepicker(el, options);

            // This is a robust addition: It cleans up the datepicker instance
            // when the element is removed by Alpine (e.g., when a row is deleted).
            // This prevents memory leaks.
            cleanup(() => {
                datepicker.destroy();
            });
        });
    });
</script>
