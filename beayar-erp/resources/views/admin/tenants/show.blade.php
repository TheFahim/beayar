<x-admin.layout.default title="Tenant Details">
    <div class="p-4" x-data="{
        showBoundaryForm: false,
        moduleAccess: {{ json_encode($subscription?->module_access ?? []) }},
        isModuleSelected(slug) {
            return this.moduleAccess && this.moduleAccess.includes(slug);
        },
        toggleModule(slug) {
            if (!this.moduleAccess) this.moduleAccess = [];
            const idx = this.moduleAccess.indexOf(slug);
            if (idx > -1) {
                this.moduleAccess.splice(idx, 1);
            } else {
                this.moduleAccess.push(slug);
            }
        }
    }">
        {{-- Back Link --}}
        <div class="mb-4">
            <a href="{{ route('admin.tenants.index') }}"
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Tenants
            </a>
        </div>

        {{-- Tenant Info Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $company->name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Owner: <span
                            class="font-medium text-gray-700 dark:text-gray-300">{{ $company->owner?->name ?? 'N/A' }}</span>
                        ({{ $company->owner?->email ?? '' }})
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Created:
                        {{ $company->created_at?->format('M d, Y') }}</p>
                </div>
                <div class="flex gap-2">
                    @if(($company->status ?? 'active') === 'active')
                        <span
                            class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Active</span>
                    @elseif(($company->status ?? '') === 'suspended')
                        <span
                            class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Suspended</span>
                    @else
                        <span
                            class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($company->status ?? 'Unknown') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Subscription Info --}}
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription</h3>
                    @if($subscription)
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded
                            @if($subscription->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($subscription->status === 'trial') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                            @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endif">{{ ucfirst($subscription->status) }}</span>
                    @endif
                </div>
                <div class="p-4">
                    @if($subscription)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Plan</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $subscription->plan?->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Price</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    ${{ number_format($subscription->price, 2) }}/mo</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Started</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $subscription->starts_at?->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Ends</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $subscription->ends_at?->format('M d, Y') ?? 'Never' }}</p>
                            </div>
                        </div>

                        {{-- Current Limits --}}
                        <div class="mb-4">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Effective
                                Limits</h4>
                            <div class="grid grid-cols-3 gap-3">
                                @php
                                    $metrics = ['sub_companies', 'quotations', 'employees'];
                                @endphp
                                @foreach($metrics as $metric)
                                    @php
                                        $limit = $subscription->getLimit($metric);
                                        $usage = $subscription->usages->where('metric', $metric)->first();
                                        $used = $usage?->used ?? 0;
                                        $isCustom = isset($subscription->custom_limits[$metric]);
                                    @endphp
                                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                            {{ ucwords(str_replace('_', ' ', $metric)) }}</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $used }} / {{ $limit == -1 ? '∞' : $limit }}
                                        </p>
                                        @if($isCustom)
                                            <span class="text-xs text-amber-600 dark:text-amber-400">Custom override</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Current Module Access --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Module Access
                            </h4>
                            @php
                                $activeModules = $subscription->module_access ?? $subscription->plan?->module_access ?? [];
                            @endphp
                            @if(count($activeModules) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activeModules as $mod)
                                        <span
                                            class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $mod }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">No modules assigned</p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No subscription found for this
                            tenant.</p>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-3">
                    <form action="{{ route('admin.tenants.impersonate', $company) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                            Login as {{ $company->owner?->name ?? 'Owner' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.tenants.suspend', $company) }}" method="POST"
                        onsubmit="return confirm('Are you sure?')">
                        @csrf
                        <input type="hidden" name="status"
                            value="{{ ($company->status ?? 'active') === 'suspended' ? 'active' : 'suspended' }}">
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium rounded-lg
                            {{ ($company->status ?? 'active') === 'suspended'
    ? 'text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50'
    : 'text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50' }}">
                            {{ ($company->status ?? 'active') === 'suspended' ? 'Activate Tenant' : 'Suspend Tenant' }}
                        </button>
                    </form>

                    @if($subscription)
                        <button @click="showBoundaryForm = !showBoundaryForm"
                            class="w-full px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50">
                            Set Subscription Boundaries
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Subscription Boundary Override Form --}}
        @if($subscription)
            <div x-show="showBoundaryForm" x-cloak
                class="bg-white dark:bg-gray-800 rounded-lg shadow border border-amber-200 dark:border-amber-700 mb-6">
                <div
                    class="p-4 border-b border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 rounded-t-lg">
                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-300">Override Subscription Boundaries
                    </h3>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Custom overrides take priority over plan
                        defaults.</p>
                </div>
                <form action="{{ route('admin.tenants.subscription.update', $company) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Plan & Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan</label>
                            <select name="plan_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} (${{ number_format($plan->base_price, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                @foreach(['active', 'trial', 'cancelled', 'expired'] as $status)
                                    <option value="{{ $status }}" {{ $subscription->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Custom Limits --}}
                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Custom
                            Limits <span class="text-xs font-normal text-gray-500">(-1 = Unlimited, leave empty to use plan
                                default)</span></h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub-Companies</label>
                                <input type="number" name="custom_limits[sub_companies]" min="-1"
                                    value="{{ $subscription->custom_limits['sub_companies'] ?? '' }}"
                                    placeholder="Plan default"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quotations</label>
                                <input type="number" name="custom_limits[quotations]" min="-1"
                                    value="{{ $subscription->custom_limits['quotations'] ?? '' }}"
                                    placeholder="Plan default"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employees</label>
                                <input type="number" name="custom_limits[employees]" min="-1"
                                    value="{{ $subscription->custom_limits['employees'] ?? '' }}" placeholder="Plan default"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Module Access Override --}}
                    <div class="mt-6">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Module
                            Access Override</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($modules as $module)
                                <label
                                    class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    :class="isModuleSelected('{{ $module->slug }}') ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-600' : ''">
                                    <input type="checkbox" name="module_access[]" value="{{ $module->slug }}"
                                        :checked="isModuleSelected('{{ $module->slug }}')"
                                        @change="toggleModule('{{ $module->slug }}')"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                    <div>
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $module->name }}</span>
                                        <span
                                            class="block text-xs text-gray-500 dark:text-gray-400">${{ number_format($module->price, 2) }}/mo</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="showBoundaryForm = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-300">Save
                            Boundaries</button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Company Members --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Members</h3>
            </div>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Role</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($company->members as $member)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $member->name }}</td>
                                <td class="px-6 py-4">{{ $member->email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($member->pivot->role ?? 'member') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($member->pivot->is_active ?? true)
                                        <span
                                            class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Active</span>
                                    @else
                                        <span
                                            class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No team members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.layout.default>
