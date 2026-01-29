<x-dashboard.layout.default title="Quotations">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.qutation class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 p-2 mb-4 gap-4">
            <div class="col-span-1 lg:col-span-2">
                <a href="{{ route('tenant.quotations.create') }}"
                    class="flex items-center justify-center gap-2 text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                    <x-ui.svg.circle-plus class="w-5 h-5" />
                    <span>Create Quotation</span>
                </a>
            </div>
            
            <div class="col-span-1 lg:col-span-6">
                <form method="GET" action="{{ route('tenant.quotations.index') }}" class="w-full">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            placeholder="Search by reference or customer name...">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>
        </div>

        <hr class="border-t border-gray-300 dark:border-gray-600 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2 overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-3 py-3 w-16">Ref No</th>
                        <th scope="col" class="px-6 py-3">Customer</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Amount</th>
                        <th scope="col" class="px-3 py-3 w-32 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                            <td class="px-3 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $quotation->reference_no }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $quotation->customer->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($quotation->issue_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($quotation->status == 'draft') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @elseif($quotation->status == 'sent') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                    @elseif($quotation->status == 'accepted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                    @elseif($quotation->status == 'rejected') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($quotation->activeRevision->grand_total ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('tenant.quotations.show', $quotation->id) }}" 
                                       class="p-2 bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 hover:text-gray-800 transition-colors"
                                       title="View">
                                        <x-ui.svg.eye class="w-4 h-4" />
                                    </a>
                                    <!-- Add Edit/Delete later -->
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.svg.qutation class="w-12 h-12 mb-3 opacity-50" />
                                    <p class="text-lg font-medium">No quotations found</p>
                                    <p class="text-sm">Create your first quotation.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 px-4 pb-4">
            {{ $quotations->links() }}
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
