<x-dashboard.common.navbar />

<x-dashboard.common.sidebar-wrapper>
    <ul class="space-y-2 font-medium">

        {{-- Admin Section --}}
        @role('admin')
            <li>
                <button type="button"
                    class="flex items-center w-full p-2 text-base text-gray-900 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                    aria-controls="sidebar-dropdown-admin" data-collapse-toggle="sidebar-dropdown-admin">
                    <x-ui.svg.admin-settings class="h-5 w-5" />
                    <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Admin</span>
                    <x-ui.svg.down-arrow class="h-3 w-3" />
                </button>
                <ul id="sidebar-dropdown-admin" class="hidden py-2 space-y-2 transition duration-300">
                    <li>
                        <x-dashboard.common.sidebar-link url="{{ route('admin.dashboard') }}" class="pl-12">
                            <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
                        </x-dashboard.common.sidebar-link>
                    </li>
                    <li>
                        <x-dashboard.common.sidebar-link url="{{ route('admin.tenants.index') }}" class="pl-12">
                            <span class="flex-1 ms-3 whitespace-nowrap">Tenants</span>
                        </x-dashboard.common.sidebar-link>
                    </li>
                     <li>
                        <x-dashboard.common.sidebar-link url="{{ route('admin.plans.index') }}" class="pl-12">
                            <span class="flex-1 ms-3 whitespace-nowrap">Plans</span>
                        </x-dashboard.common.sidebar-link>
                    </li>
                     <li>
                        <x-dashboard.common.sidebar-link url="{{ route('admin.coupons.index') }}" class="pl-12">
                            <span class="flex-1 ms-3 whitespace-nowrap">Coupons</span>
                        </x-dashboard.common.sidebar-link>
                    </li>
                </ul>
            </li>
        @endrole

        {{-- Tenant Section --}}
        @role('tenant|user')
             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                    <x-ui.svg.dashboard class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
                </x-dashboard.common.sidebar-link>
            </li>

            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}">
                    <x-ui.svg.customer class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Customers</span>
                </x-dashboard.common.sidebar-link>
            </li>

            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.quotations.index') }}">
                    <x-ui.svg.qutation class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Quotations</span>
                </x-dashboard.common.sidebar-link>
            </li>

            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.challans.index') }}">
                    <x-ui.svg.chalan class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Challans</span>
                </x-dashboard.common.sidebar-link>
            </li>

            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.products.index') }}">
                    <x-ui.svg.product class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Products</span>
                </x-dashboard.common.sidebar-link>
            </li>

             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.billing.index') }}">
                    <x-ui.svg.bill class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Billing</span>
                </x-dashboard.common.sidebar-link>
            </li>

             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.finance.index') }}">
                    <x-ui.svg.payment class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Finance</span>
                </x-dashboard.common.sidebar-link>
            </li>

             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}">
                    <x-ui.svg.image class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Image Library</span>
                </x-dashboard.common.sidebar-link>
            </li>

             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.subscription.index') }}">
                     <x-ui.svg.home class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Subscription</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endrole
        
        {{-- Fallback for development if no roles set --}}
        @unlessrole('admin|tenant|user')
             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                    <x-ui.svg.dashboard class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
                </x-dashboard.common.sidebar-link>
            </li>
             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}">
                    <x-ui.svg.customer class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Customers</span>
                </x-dashboard.common.sidebar-link>
            </li>
             <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}">
                    <x-ui.svg.image class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Image Library</span>
                </x-dashboard.common.sidebar-link>
            </li>
             <li>
                <x-dashboard.common.sidebar-link url="{{ route('admin.dashboard') }}">
                    <x-ui.svg.admin-settings class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Admin Dashboard</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endunlessrole

    </ul>
</x-dashboard.common.sidebar-wrapper>
