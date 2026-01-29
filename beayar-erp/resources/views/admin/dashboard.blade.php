<x-dashboard.layout.default title="Admin Dashboard">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <div class="flex flex-col items-center justify-center h-24 rounded bg-white dark:bg-gray-800 shadow p-4">
            <dt class="mb-2 text-3xl font-extrabold text-gray-900 dark:text-white">${{ number_format($stats['mrr'], 2) }}</dt>
            <dd class="text-gray-500 dark:text-gray-400">Total MRR</dd>
        </div>
        <div class="flex flex-col items-center justify-center h-24 rounded bg-white dark:bg-gray-800 shadow p-4">
            <dt class="mb-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_tenants'] }}</dt>
            <dd class="text-gray-500 dark:text-gray-400">Total Tenants</dd>
        </div>
        <div class="flex flex-col items-center justify-center h-24 rounded bg-white dark:bg-gray-800 shadow p-4">
            <dt class="mb-2 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stats['new_tenants'] }}</dt>
            <dd class="text-gray-500 dark:text-gray-400">New This Month</dd>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
        </div>
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Tenant</th>
                        <th scope="col" class="px-6 py-3">Amount</th>
                        <th scope="col" class="px-6 py-3">Date</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity as $payment)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $payment->invoice->user->company->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            ${{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $payment->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Paid</span>
                        </td>
                    </tr>
                    @empty
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td colspan="4" class="px-6 py-4 text-center">No recent activity found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dashboard.layout.default>
