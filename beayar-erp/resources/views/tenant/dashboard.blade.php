@use('Illuminate\Support\Number')
<x-dashboard.layout.default title="Dashboard">
    <div class="p-5 rounded-xl shadow-lg bg-gradient-to-br from-slate-100 to-slate-200 dark:from-gray-900 dark:to-slate-800">
        <!-- Stats row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-4">
            <!-- Revenue -->
            {{-- <div class="rounded-xl p-3" style="background:rgba(79,70,229,0.15); border:1px solid rgba(79,70,229,0.2);">
                <div class="text-xs text-indigo-300 mb-1">Revenue</div>
                <div class="text-lg font-display font-bold text-white">
                    {{ Number::currency($currentRevenue, 'BDT') }}
                </div>
                @if($revenueTrend['direction'] === 'up')
                    <div class="text-xs text-green-400 mt-1">▲ {{ $revenueTrend['value'] }}%</div>
                @elseif($revenueTrend['direction'] === 'down')
                    <div class="text-xs text-red-400 mt-1">▼ {{ $revenueTrend['value'] }}%</div>
                @else
                    <div class="text-xs text-gray-400 mt-1">- {{ $revenueTrend['value'] }}%</div>
                @endif
            </div> --}}

            <!-- Invoices -->
            <div class="rounded-xl p-3 bg-teal-50/50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800">
                <div class="text-xs text-teal-700 dark:text-teal-300 mb-1">Invoices</div>
                <div class="text-lg font-display font-bold text-gray-900 dark:text-white">{{ Number::format($currentInvoices) }}</div>
                @if($invoicesTrend['direction'] === 'up')
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">▲ {{ $invoicesTrend['value'] }}%</div>
                @elseif($invoicesTrend['direction'] === 'down')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">▼ {{ $invoicesTrend['value'] }}%</div>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">- {{ $invoicesTrend['value'] }}%</div>
                @endif
            </div>

            <!-- Pending Invoices -->
            {{-- <div class="rounded-xl p-3" style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2);">
                <div class="text-xs text-yellow-300 mb-1">Pending</div>
                <div class="text-lg font-display font-bold text-white">{{ Number::format($currentPending) }}</div>
                @if($pendingTrend['direction'] === 'up')
                    <!-- Pending going up is usually bad, so maybe red? But consistency with arrows implies direction. -->
                    <!-- User example has Red for Down on Pending? Wait. -->
                    <!-- "Pending ... ▼ 2.4% (Red)" in user example. -->
                    <!-- So Down is Red? Usually Down Pending is Good (Green). -->
                    <!-- But user snippet shows "Pending ... ▼ 2.4% (Red)". -->
                    <!-- This implies a decrease in pending is shown as Red/Down? Or maybe just color matches arrow direction. -->
                    <!-- Usually ▲ is Green, ▼ is Red. -->
                    <!-- But for Pending, ▲ (increase) is Bad (Red), ▼ (decrease) is Good (Green). -->
                    <!-- I will stick to user visual: Arrow matches color? -->
                    <!-- User snippet: "▼ 2.4%" is Red. This suggests generic "Down is Red". -->
                    <!-- Wait, "▼" is usually "Down". -->
                    <!-- If I have FEWER pending invoices, that's GOOD. -->
                    <!-- But if the color is RED for DOWN, then it implies "Negative Trend". -->
                    <!-- Let's follow standard: Increase = Up/Green, Decrease = Down/Red. -->
                    <!-- Even if for Pending, increase is bad. -->
                    <div class="text-xs {{ $pendingTrend['direction'] === 'up' ? 'text-green-400' : 'text-red-400' }} mt-1">
                        {{ $pendingTrend['direction'] === 'up' ? '▲' : ($pendingTrend['direction'] === 'down' ? '▼' : '-') }} {{ $pendingTrend['value'] }}%
                    </div>
                @else
                     <div class="text-xs text-gray-400 mt-1">- {{ $pendingTrend['value'] }}%</div>
                @endif
            </div> --}}

            <!-- Teams -->
            <div class="rounded-xl p-3 bg-olive-50/50 dark:bg-olive-950/20 border border-olive-200 dark:border-olive-800">
                <div class="text-xs text-olive-700 dark:text-olive-300 mb-1">Teams</div>
                <div class="text-lg font-display font-bold text-gray-900 dark:text-white">{{ Number::format($activeTeams) }}</div>
                <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Active</div>
            </div>

            <!-- Quotations -->
            <div class="rounded-xl p-3 bg-green-50/50 dark:bg-green-950/20 border border-green-200 dark:border-green-800">
                <div class="text-xs text-green-700 dark:text-green-300 mb-1">Quotations</div>
                <div class="text-lg font-display font-bold text-gray-900 dark:text-white">{{ Number::format($currentQuotations) }}</div>
                @if($quotationsTrend['direction'] === 'up')
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">▲ {{ $quotationsTrend['value'] }}%</div>
                @elseif($quotationsTrend['direction'] === 'down')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">▼ {{ $quotationsTrend['value'] }}%</div>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">- {{ $quotationsTrend['value'] }}%</div>
                @endif
            </div>

            <!-- Challans from Quotations -->
            <div class="rounded-xl p-3 bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800">
                <div class="text-xs text-orange-700 dark:text-orange-300 mb-1">Challans</div>
                <div class="text-lg font-display font-bold text-gray-900 dark:text-white">{{ Number::format($currentChallans) }}</div>
                @if($challansTrend['direction'] === 'up')
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">▲ {{ $challansTrend['value'] }}%</div>
                @elseif($challansTrend['direction'] === 'down')
                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">▼ {{ $challansTrend['value'] }}%</div>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">- {{ $challansTrend['value'] }}%</div>
                @endif
            </div>

        </div>

        <!-- Chart area -->
        {{-- <div class="rounded-xl p-4 mb-4" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-medium">Revenue Overview · {{ $currentYear }}</span>
                <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(79,70,229,0.2); color:#818CF8;">Monthly</span>
            </div>
            <div class="flex items-end gap-2 h-20">
                @foreach($monthlyRevenue as $monthData)
                    <div class="flex-1 rounded-t group relative" style="height:{{ $monthData['percentage'] }}%; background:rgba(79,70,229,0.3);">
                        <!-- Tooltip for exact amount -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-[10px] px-1 py-0.5 rounded z-10 whitespace-nowrap">
                            {{ $monthData['month'] }}: {{ Number::currency($monthData['amount'], 'BDT') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div> --}}

        <!-- Bottom rows -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Recent Quotations -->
            <div class="rounded-xl p-3 bg-white/50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">Recent Quotations</div>
                <div class="space-y-1.5">
                    @forelse($recentQuotations as $quotation)
                        @php
                            // Get saved_as status from first revision
                            $savedAs = $quotation->revisions->first()?->saved_as ?? 'draft';

                            // Determine saved_as styling
                            $savedAsClass = $savedAs === 'quotation'
                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200'
                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200';
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-700 dark:text-gray-300">{{ $quotation->reference_no }}</span>
                            <div class="flex items-center gap-1">
                                <!-- Saved As Status -->
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $savedAsClass }}">
                                    {{ ucfirst($savedAs) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">No recent quotations</div>
                    @endforelse
                </div>
            </div>

            <!-- Cash Flow -->
            {{-- <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                <div class="text-xs text-slate-500 mb-2">Cash Flow</div>
                <div class="space-y-2 mt-1">
                    <!-- Collected -->
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Collected</span>
                            <span class="text-green-400">{{ round($collectedPercentage) }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);">
                            <div class="h-full rounded-full db-green" style="width:{{ $collectedPercentage }}%; background: #34D399;"></div>
                        </div>
                    </div>
                    <!-- Target -->
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Target</span>
                            <span class="text-indigo-400">{{ round($targetPercentage) }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);">
                            <div class="h-full rounded-full db-indigo" style="width:{{ $targetPercentage }}%; background: #818CF8;"></div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</x-dashboard.layout.default>
