<x-dashboard.common.navbar />

<x-dashboard.common.sidebar-wrapper>
    <ul class="space-y-2 font-medium">

        {{-- Admin Section --}}
        @role('super_admin')
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
                    <x-dashboard.common.sidebar-link url="{{ route('admin.modules.index') }}" class="pl-12">
                        <span class="flex-1 ms-3 whitespace-nowrap">Modules</span>
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
        @role('tenant_admin|company_admin|employee')
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                <x-ui.svg.dashboard class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
            </x-dashboard.common.sidebar-link>
        </li>

        @if(Auth::user()->current_tenant_company_id && Auth::user()->isOwnerOf(Auth::user()->current_tenant_company_id))
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.profile.show') }}">
                <x-ui.svg.company class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Tenant Profile</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif

        <li>
            <button type="button" class="flex items-center w-full p-2 text-base text-gray-900 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="sidebar-dropdown-org" data-collapse-toggle="sidebar-dropdown-org">
                <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
                    <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
                    <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
                </svg>
                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Organization</span>
                <x-ui.svg.down-arrow class="h-3 w-3" />
            </button>
            <ul id="sidebar-dropdown-org" class="hidden py-2 space-y-2 transition duration-300">
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.user-companies.index') }}" class="pl-12">
                        <span class="flex-1 ms-3 whitespace-nowrap">My Companies</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('company-members.index') }}" class="pl-12">
                        <span class="flex-1 ms-3 whitespace-nowrap">Team Members</span>
                    </x-dashboard.common.sidebar-link>
                </li>
            </ul>
        </li>

        @if(Auth::user()->subscription && Auth::user()->current_tenant_company_id)
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}">
                <x-ui.svg.customer class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Customers</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif

        @if(Auth::user()->hasModuleAccess('quotations'))
            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.quotations.index') }}">
                    <x-ui.svg.qutation class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Quotations</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endif

        @if(Auth::user()->hasModuleAccess('challans'))
            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.challans.index') }}">
                    <x-ui.svg.chalan class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Challans</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endif

        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.products.index') }}">
                <x-ui.svg.product class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Products</span>
            </x-dashboard.common.sidebar-link>
        </li>

        @if(Auth::user()->hasModuleAccess('billing'))
            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.bills.index') }}">
                    <x-ui.svg.bill class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Billing</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endif

        @if(Auth::user()->hasModuleAccess('finance'))
            <li>
                <x-dashboard.common.sidebar-link url="{{ route('tenant.finance.index') }}">
                    <x-ui.svg.payment class="h-5 w-5" />
                    <span class="flex-1 ms-3 whitespace-nowrap">Finance</span>
                </x-dashboard.common.sidebar-link>
            </li>
        @endif

        @if(Auth::user()->subscription && Auth::user()->current_tenant_company_id)
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}">
                <x-ui.svg.image class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Image Library</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif

        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.subscription.index') }}">
                <x-ui.svg.home class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Subscription</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endrole

        {{-- Fallback for development if no roles set --}}
        @unlessrole('super_admin|tenant_admin|company_admin|employee')
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                <x-ui.svg.dashboard class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @if(Auth::user()->subscription && Auth::user()->current_tenant_company_id)
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}">
                <x-ui.svg.customer class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Customers</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif
        @if(Auth::user()->subscription && Auth::user()->current_tenant_company_id)
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}">
                <x-ui.svg.image class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Image Library</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif
        @if(Auth::user()->subscription && Auth::user()->current_tenant_company_id)
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.dashboard') }}">
                <x-ui.svg.admin-settings class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Admin Dashboard</span>
            </x-dashboard.common.sidebar-link>
        </li>
        @endif
        @endunlessrole

    </ul>
</x-dashboard.common.sidebar-wrapper>
