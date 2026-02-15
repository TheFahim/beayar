<x-admin.layout.default title="Manage Plans">
    <div class="p-4" x-data="{
        editModalOpen: false,
        createModalOpen: false,
        currentPlan: {},
        openEditModal(plan) {
            this.currentPlan = JSON.parse(JSON.stringify(plan));
            if (!this.currentPlan.limits) this.currentPlan.limits = {};
            if (!this.currentPlan.module_access) this.currentPlan.module_access = [];
            this.editModalOpen = true;
        },
        isModuleSelected(slug) {
            return this.currentPlan.module_access && this.currentPlan.module_access.includes(slug);
        },
        toggleModule(slug) {
            if (!this.currentPlan.module_access) this.currentPlan.module_access = [];
            const idx = this.currentPlan.module_access.indexOf(slug);
            if (idx > -1) {
                this.currentPlan.module_access.splice(idx, 1);
            } else {
                this.currentPlan.module_access.push(slug);
            }
        }
    }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h2>
            <button @click="createModalOpen = true" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                + New Plan
            </button>
        </div>

        {{-- Plan Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
            @forelse($plans as $plan)
                <div
                    class="relative p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 {{ !$plan->is_active ? 'opacity-60' : '' }}">
                    @if(!$plan->is_active)
                        <span
                            class="absolute top-2 right-2 bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Inactive</span>
                    @endif

                    <h5 class="mb-1 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $plan->name }}</h5>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                        ${{ number_format($plan->base_price, 2) }}<span
                            class="text-sm font-normal text-gray-500">/{{ $plan->billing_cycle }}</span></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $plan->subscriptions_count ?? 0 }}
                        subscriber(s)</p>

                    @if($plan->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $plan->description }}</p>
                    @endif

                    {{-- Limits Summary --}}
                    <div class="mb-3 space-y-1">
                        <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Limits</p>
                        @if($plan->limits)
                            @foreach($plan->limits as $key => $value)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ $value == -1 ? 'Unlimited' : $value }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-400 italic">Custom / No limits set</p>
                        @endif
                    </div>

                    {{-- Module Access Summary --}}
                    <div class="mb-4">
                        <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Modules</p>
                        @if($plan->module_access && count($plan->module_access) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach($plan->module_access as $mod)
                                    <span
                                        class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $mod }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic">None</p>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button @click="openEditModal({{ $plan->toJson() }})"
                            class="flex-1 text-center px-3 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Edit
                        </button>
                        @if($plan->is_active && ($plan->subscriptions_count ?? 0) === 0)
                            <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST"
                                onsubmit="return confirm('Deactivate this plan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                    Deactivate
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center text-gray-500 py-8">No plans found. Create one to get started.</div>
            @endforelse
        </div>

        {{-- Edit Plan Modal --}}
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="edit-plan-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="editModalOpen" @click="editModalOpen = false"
                    class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

                <div x-show="editModalOpen"
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-auto">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="edit-plan-title">
                            Edit Plan: <span x-text="currentPlan.name"></span>
                        </h3>
                    </div>
                    <form :action="`{{ url('admin/plans') }}/${currentPlan.id}`" method="POST" class="p-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input type="text" name="name" x-model="currentPlan.name" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base
                                    Price</label>
                                <input type="number" step="0.01" name="base_price" x-model="currentPlan.base_price"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing
                                    Cycle</label>
                                <select name="billing_cycle" x-model="currentPlan.billing_cycle"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select name="is_active" x-model="currentPlan.is_active"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea name="description" x-model="currentPlan.description" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"></textarea>
                            </div>
                        </div>

                        {{-- Limits Section --}}
                        <div class="mb-6">
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">
                                Usage Limits <span class="text-xs font-normal text-gray-500">(-1 = Unlimited)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub-Companies</label>
                                    <input type="number" name="limits[sub_companies]"
                                        x-model="currentPlan.limits.sub_companies" min="-1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quotations</label>
                                    <input type="number" name="limits[quotations]"
                                        x-model="currentPlan.limits.quotations" min="-1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employees</label>
                                    <input type="number" name="limits[employees]" x-model="currentPlan.limits.employees"
                                        min="-1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Module Access Section --}}
                        <div class="mb-6">
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">
                                Module Access</h4>
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
                                                class="block text-xs text-gray-500 dark:text-gray-400">${{ number_format($module->price, 2) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Create Plan Modal --}}
        <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="create-plan-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="createModalOpen" @click="createModalOpen = false"
                    class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>

                <div x-show="createModalOpen"
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-auto">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="create-plan-title">Create
                            New Plan</h3>
                    </div>
                    <form action="{{ route('admin.plans.store') }}" method="POST" class="p-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input type="text" name="name" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                                <input type="text" name="slug" required placeholder="e.g. enterprise"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base
                                    Price</label>
                                <input type="number" step="0.01" name="base_price" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing
                                    Cycle</label>
                                <select name="billing_cycle"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea name="description" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"></textarea>
                            </div>
                        </div>

                        {{-- Limits --}}
                        <div class="mb-6">
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">
                                Usage Limits <span class="text-xs font-normal text-gray-500">(-1 = Unlimited)</span>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub-Companies</label>
                                    <input type="number" name="limits[sub_companies]" min="-1" value="1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quotations</label>
                                    <input type="number" name="limits[quotations]" min="-1" value="20"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employees</label>
                                    <input type="number" name="limits[employees]" min="-1" value="3"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Module Access --}}
                        <div class="mb-6">
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">
                                Module Access</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($modules as $module)
                                    <label
                                        class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <input type="checkbox" name="module_access[]" value="{{ $module->slug }}"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-white">{{ $module->name }}</span>
                                            <span
                                                class="block text-xs text-gray-500 dark:text-gray-400">${{ number_format($module->price, 2) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" @click="createModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300">Create
                                Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
