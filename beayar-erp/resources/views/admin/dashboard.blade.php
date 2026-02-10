<x-dashboard.layout.default title="Admin Dashboard">
    <div class="p-4">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div
                class="flex flex-col items-center justify-center h-28 rounded-lg bg-white dark:bg-gray-800 shadow p-4 border border-gray-100 dark:border-gray-700">
                <dt class="mb-2 text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
                    ${{ number_format($stats['mrr'], 2) }}</dt>
                <dd class="text-sm text-gray-500 dark:text-gray-400">Monthly Recurring Revenue</dd>
            </div>
            <div
                class="flex flex-col items-center justify-center h-28 rounded-lg bg-white dark:bg-gray-800 shadow p-4 border border-gray-100 dark:border-gray-700">
                <dt class="mb-2 text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $stats['total_tenants'] }}
                </dt>
                <dd class="text-sm text-gray-500 dark:text-gray-400">Total Tenants</dd>
            </div>
            <div
                class="flex flex-col items-center justify-center h-28 rounded-lg bg-white dark:bg-gray-800 shadow p-4 border border-gray-100 dark:border-gray-700">
                <dt class="mb-2 text-3xl font-extrabold text-purple-600 dark:text-purple-400">
                    {{ $stats['new_tenants'] }}</dt>
                <dd class="text-sm text-gray-500 dark:text-gray-400">New This Month</dd>
            </div>
            <div
                class="flex flex-col items-center justify-center h-28 rounded-lg bg-white dark:bg-gray-800 shadow p-4 border border-gray-100 dark:border-gray-700">
                <dt class="mb-2 text-3xl font-extrabold text-amber-600 dark:text-amber-400">
                    {{ $stats['active_subscriptions'] }}</dt>
                <dd class="text-sm text-gray-500 dark:text-gray-400">Active Subscriptions</dd>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Plan Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Distribution</h3>
                </div>
                <div class="p-4">
                    @forelse($planDistribution as $plan)
                        <div
                            class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                    @if($plan->slug === 'free') bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                    @elseif($plan->slug === 'pro') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                    @elseif($plan->slug === 'pro-plus') bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300
                                    @else bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300
                                    @endif">
                                    {{ $plan->subscriptions_count }}
                                </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $plan->name }}</span>
                            </div>
                            <span
                                class="text-sm text-gray-500 dark:text-gray-400">${{ number_format($plan->base_price, 2) }}/mo</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No plans configured.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Payments</h3>
                </div>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tenant</th>
                                <th scope="col" class="px-4 py-3">Amount</th>
                                <th scope="col" class="px-4 py-3">Date</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $payment)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $payment->invoice->user->company->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3">${{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-3">{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No recent payments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
