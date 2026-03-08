<x-dashboard.common.navbar />

<x-dashboard.common.sidebar-wrapper>
    <div class="space-y-6">
        {{-- Admin Section --}}
        @role('super_admin')
        <div>
            <h3 class="text-xs font-semibold text-gray-800 dark:text-slate-500 uppercase tracking-wider mb-3 px-4">Administration</h3>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-dropdown targetId="sidebar-dropdown-admin" :childLinks="[route('admin.dashboard'), route('admin.tenants.index'), route('admin.plans.index'), route('admin.modules.index'), route('admin.coupons.index')]">
                        <x-ui.svg.admin-settings class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Admin</span>
                        <x-slot name="dropdownContent">
                            <ul class="space-y-1">
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('admin.dashboard') }}" class="pl-8">
                                        <x-ui.svg.dashboard class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Dashboard</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('admin.tenants.index') }}" class="pl-8">
                                        <x-ui.svg.company class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Tenants</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('admin.plans.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">Plans</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('admin.modules.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">Modules</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('admin.coupons.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">Coupons</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                            </ul>
                        </x-slot>
                    </x-dashboard.common.sidebar-dropdown>
                </li>
            </ul>
        </div>
        @endrole

        {{-- Tenant Section --}}
        @if(\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId())
        <div>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                        <x-ui.svg.dashboard class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Dashboard</span>
                    </x-dashboard.common.sidebar-link>
                </li>

                @if(\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId() && \Illuminate\Support\Facades\Auth::user()->isOwnerOf(\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId()))
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.profile.show') }}">
                        <x-ui.svg.company class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Profile</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                @endif

                <li>
                    <x-dashboard.common.sidebar-dropdown targetId="sidebar-dropdown-org" :childLinks="[route('tenant.user-companies.index'), route('company-members.index'), route('tenant.roles.index')]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Organization</span>
                        <x-slot name="dropdownContent">
                            <ul class="space-y-1">
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.user-companies.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">My Companies</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @can('manage_members')
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('company-members.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">Team Members</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @endcan
                                @can('manage_roles')
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.roles.index') }}" class="pl-8">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <span class="flex-1 ml-3 whitespace-nowrap">Roles & Permissions</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @endcan
                            </ul>
                        </x-slot>
                    </x-dashboard.common.sidebar-dropdown>
                </li>
            </ul>
        </div>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId())
        @canany(['view_customers', 'view_images', 'view_products'])
        <div>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-dropdown targetId="sidebar-dropdown-catalog" :childLinks="[route('tenant.customers.index'), route('tenant.images.index'), route('tenant.products.index')]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Catalog</span>
                        <x-slot name="dropdownContent">
                            <ul class="space-y-1">
                                @can('view_customers')
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}" class="pl-8">
                                        <x-ui.svg.customer class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Customers</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @endcan

                                @can('view_images')
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}" class="pl-8">
                                        <x-ui.svg.image class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Image Library</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @endcan

                                @can('view_products')
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.products.index') }}" class="pl-8">
                                        <x-ui.svg.product class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Products</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                @endcan
                            </ul>
                        </x-slot>
                    </x-dashboard.common.sidebar-dropdown>
                </li>
            </ul>
        </div>
        @endcanany
        @endif

        @canany(['view_quotations', 'view_challans', 'view_bills'])
        <div>
            <ul class="space-y-2">
                @can('view_quotations')
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.quotations.index') }}">
                        <x-ui.svg.qutation class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Quotations</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                @endcan

                @can('view_challans')
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.challans.index') }}">
                        <x-ui.svg.chalan class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Challans</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                @endcan

                @can('view_bills')
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.bills.index') }}">
                        <x-ui.svg.bill class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Billing</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                @endcan
            </ul>
        </div>
        @endcanany

        @canany(['view_feedback', 'create_feedback'])
        <div>
            <h3 class="text-xs font-semibold text-gray-800 dark:text-slate-500 uppercase tracking-wider mb-3 px-4">Support</h3>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.feedback.index') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Feedback</span>
                    </x-dashboard.common.sidebar-link>
                </li>
            </ul>
        </div>
        @endcanany

        @if(\Illuminate\Support\Facades\Auth::user()->isOwnerOf(\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId()))
        <div>
            <h3 class="text-xs font-semibold text-gray-800 dark:text-slate-500 uppercase tracking-wider mb-3 px-4">Account</h3>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.subscription.index') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Subscription</span>
                    </x-dashboard.common.sidebar-link>
                </li>
            </ul>
        </div>
        @endif

        {{-- Fallback for development if no roles set --}}
        @if(!\Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId() && !\Illuminate\Support\Facades\Auth::user()->hasRole('super_admin'))
        <div>
            <h3 class="text-xs font-semibold text-gray-800 dark:text-slate-500 uppercase tracking-wider mb-3 px-4">Getting Started</h3>
            <ul class="space-y-2">
                <li>
                    <x-dashboard.common.sidebar-link url="{{ route('tenant.dashboard') }}">
                        <x-ui.svg.dashboard class="h-5 w-5" />
                        <span class="flex-1 ml-3 whitespace-nowrap">Dashboard</span>
                    </x-dashboard.common.sidebar-link>
                </li>
                @if(\Illuminate\Support\Facades\Auth::user()->subscription && \Illuminate\Support\Facades\Auth::user()->getCurrentCompanyId())
                <li>
                    <x-dashboard.common.sidebar-dropdown targetId="sidebar-dropdown-catalog-fallback" :childLinks="[route('tenant.customers.index'), route('tenant.images.index'), route('tenant.products.index')]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="flex-1 ml-3 whitespace-nowrap">Catalog</span>
                        <x-slot name="dropdownContent">
                            <ul class="space-y-1">
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.customers.index') }}" class="pl-8">
                                        <x-ui.svg.customer class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Customers</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.images.index') }}" class="pl-8">
                                        <x-ui.svg.image class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Image Library</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                                <li>
                                    <x-dashboard.common.sidebar-link url="{{ route('tenant.products.index') }}" class="pl-8">
                                        <x-ui.svg.product class="h-4 w-4" />
                                        <span class="flex-1 ml-3 whitespace-nowrap">Products</span>
                                    </x-dashboard.common.sidebar-link>
                                </li>
                            </ul>
                        </x-slot>
                    </x-dashboard.common.sidebar-dropdown>
                </li>
                @endif
            </ul>
        </div>
        @endif
    </div>
</x-dashboard.common.sidebar-wrapper>
