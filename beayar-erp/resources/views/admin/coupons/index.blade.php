<x-admin.layout.default title="Coupons Management">
    <div x-data="{
        createModalOpen: false,
        toggleModal() {
            this.createModalOpen = !this.createModalOpen;
        }
    }">
        <div class="mb-4">
            <button @click="toggleModal()" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Create Campaign</button>
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Code</th>
                        <th scope="col" class="px-6 py-3">Discount</th>
                        <th scope="col" class="px-6 py-3">Valid Dates</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $coupon->code }}
                        </td>
                        <td class="px-6 py-4">
                            @if($coupon->discount_percentage > 0)
                                {{ $coupon->discount_percentage }}%
                            @else
                                ${{ number_format($coupon->discount_amount, 2) }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('M d') : 'Any' }}
                            -
                            {{ $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('M d, Y') : 'Forever' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($coupon->is_active)
                                <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No active campaigns found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $coupons->links() }}
            </div>
        </div>

        <!-- Create Modal -->
        <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModalOpen" @click="createModalOpen = false" class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Create New Campaign
                        </h3>
                        <div class="mt-2">
                            <form action="{{ route('admin.coupons.store') }}" method="POST" id="createCouponForm">
                                @csrf
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                        <input type="text" name="code" id="code" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Type</label>
                                        <select name="discount_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="fixed">Fixed Amount ($)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                                        <input type="number" step="0.01" name="discount_value" id="amount" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires At</label>
                                        <input type="date" name="expires_at" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" form="createCouponForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create
                        </button>
                        <button type="button" @click="createModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
