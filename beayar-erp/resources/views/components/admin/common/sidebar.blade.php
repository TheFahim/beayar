<x-dashboard.common.sidebar-wrapper>
    <ul class="space-y-2 font-medium">
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.dashboard') }}">
                <x-ui.svg.dashboard class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
            </x-dashboard.common.sidebar-link>
        </li>
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.tenants.index') }}">
                <x-ui.svg.company class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Tenants</span>
            </x-dashboard.common.sidebar-link>
        </li>
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.plans.index') }}">
                <x-ui.svg.bill class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Plans</span>
            </x-dashboard.common.sidebar-link>
        </li>
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.modules.index') }}">
                <x-ui.svg.product class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Modules</span>
            </x-dashboard.common.sidebar-link>
        </li>
        <li>
            <x-dashboard.common.sidebar-link url="{{ route('admin.coupons.index') }}">
                <x-ui.svg.bill class="h-5 w-5" />
                <span class="flex-1 ms-3 whitespace-nowrap">Coupons</span>
            </x-dashboard.common.sidebar-link>
        </li>
    </ul>
</x-dashboard.common.sidebar-wrapper>
