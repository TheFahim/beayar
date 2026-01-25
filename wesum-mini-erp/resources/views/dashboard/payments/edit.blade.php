<x-dashboard.layout.default title="Edit Payments for Bill #{{ $receivedBill->bill_no }}">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('received-bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Payments
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Edit Payments for Bill #{{ $receivedBill->bill_no }}">
        <form action="{{ route('received-bills.update', $receivedBill->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="space-y-2 mx-5">
                <div class="grid grid-cols-1 xl:grid-cols-3 xl:gap-8">
                    {{-- Bill Details Section --}}
                    <div class="mb-6 xl:col-span-1">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Bill Details</h3>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div class="grid grid-cols-3 gap-4 px-4 py-3">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Bill No</div>
                                    <div class="text-sm text-gray-900 dark:text-white col-span-2 font-mono">{{ $receivedBill->bill_no }}</div>
                                </div>
                                <div class="grid grid-cols-3 gap-4 px-4 py-3">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Payable</div>
                                    <div class="text-sm text-gray-900 dark:text-white col-span-2">Tk {{ number_format($receivedBill->payable, 2) }}</div>
                                </div>
                                <div class="grid grid-cols-3 gap-4 px-4 py-3">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Paid</div>
                                    <div class="text-sm text-green-600 dark:text-green-400 col-span-2">Tk {{ number_format($receivedBill->paid, 2) }}</div>
                                </div>
                                <div class="grid grid-cols-3 gap-4 px-4 py-3">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Due</div>
                                    <div class="text-sm font-semibold text-red-600 dark:text-red-400 col-span-2">Tk {{ number_format($receivedBill->due, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        {{-- <input type="hidden" name="bill_id" value="{{ $receivedBill->id }}"> --}}
                    </div>

                    {{-- Payments Section --}}
                    <div class="p-2 xl:col-span-2">
                        <div class="grid grid-cols-1">
                            <div class="w-full space-y-4" x-data="clonableInputs">
                                <div class="flex items-center mb-4">
                                    <h1 class="text-xl font-bold dark:text-white">Manage Payments</h1>
                                    <button type="button"
                                        class="ms-4 p-1.5 text-gray-600 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700"
                                        @click="addRow">
                                        <x-ui.svg.circle-plus class="w-6 h-6" />
                                        <span class="sr-only">Add New Payment</span>
                                    </button>
                                </div>

                                <!-- Input Rows -->
                                {{--
                                    ==================================
                                    THE FIX IS APPLIED ON THIS LINE
                                    ==================================
                                --}}
                                <template x-for="(row, index) in rows" :key="row.id ? 'db-' + row.id : 'new-' + index">
                                    <div class="grid items-center gap-4" style="grid-template-columns: repeat(10, minmax(0, 1fr));">
                                        <input type="hidden" x-bind:name="'payment[' + index + '][id]'" x-model="row.id">

                                        <!-- Date Input -->
                                        <div class="col-span-3 lg:col-span-2">
                                            <div class="relative max-w-sm">
                                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                                    <x-ui.svg.calendar class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                                </div>
                                                <input x-bind:name="'payment[' + index + '][received_date]'" type="text" x-datepicker x-model="row.received_date"
                                                    class="flowbite-datepicker bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Received Date" autocomplete="off" required>
                                            </div>
                                        </div>

                                        <!-- Amount Input -->
                                        <div class="col-span-3 lg:col-span-2">
                                            <x-ui.form.input x-bind:name="'payment[' + index + '][amount]'" x-model="row.amount" type="number" step="0.01" placeholder="Amount (Tk)" required />
                                        </div>

                                        <!-- Details Input -->
                                        <div class="col-span-4">
                                            <x-ui.form.input x-bind:name="'payment[' + index + '][details]'" x-model="row.details" type="text" placeholder="Details" />
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="col-span-2 lg:col-span-1 justify-self-start">
                                            <button type="button"
                                                class="flex items-center justify-center p-2 bg-red-500 text-white rounded hover:bg-red-600"
                                                @click="rows.splice(index, 1)"
                                                x-show="!row.id"
                                                title="Remove Payment">
                                                <x-ui.svg.close class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end px-5">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 my-10 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">
                    Update Payments
                </button>
            </div>
        </form>
    </x-ui.card>
</x-dashboard.layout.default>

{{-- The script remains unchanged --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clonableInputs', () => ({
            rows: {!! json_encode(old('payment', $formattedPayments)) !!},
            addRow() {
                this.rows.push({
                    id: null,
                    received_date: '',
                    amount: '',
                    details: '',
                });
            },
        }));

        Alpine.directive('datepicker', (el, _, { cleanup }) => {
            const datepicker = new Datepicker(el, {
                autohide: true,
                format: 'dd/mm/yyyy',
                orientation: 'bottom',
                title: null,
                autoSelectToday: true,
            });
            cleanup(() => {
                datepicker.destroy();
            });
        });
    });
</script>
