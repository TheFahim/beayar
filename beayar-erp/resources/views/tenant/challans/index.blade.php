@push('scripts')
    <script>
        (function() {
            const setupDropdownPositioning = () => {
                if (window.DropdownPositioning) return;

                window.DropdownPositioning = {
                    activeDropdown: null,
                    activeButton: null,
                    positionCache: new Map(),
                    scrollListener: null,
                    resizeListener: null,

                    updatePosition(dropdownElement, buttonElement) {
                        if (!dropdownElement || !buttonElement) return;

                        // Get the button's position relative to viewport
                        const buttonRect = buttonElement.getBoundingClientRect();

                        // Get dropdown dimensions
                        const dropdownWidth = dropdownElement.offsetWidth || 200; // fallback width
                        const dropdownHeight = dropdownElement.offsetHeight || 100; // fallback height

                        // Get viewport dimensions
                        const viewportWidth = window.innerWidth;
                        const viewportHeight = window.innerHeight;

                        // Calculate position - prefer below the button
                        let top = buttonRect.bottom + window.scrollY;
                        let left = buttonRect.left + window.scrollX;

                        // Check if dropdown would go off the right edge
                        if (left + dropdownWidth > viewportWidth + window.scrollX) {
                            left = buttonRect.right - dropdownWidth + window.scrollX;
                            // Ensure it doesn't go off the left edge
                            left = Math.max(window.scrollX + 8, left);
                        }

                        // Check if dropdown would go off the bottom
                        if (top + dropdownHeight > viewportHeight + window.scrollY) {
                            top = buttonRect.top - dropdownHeight + window.scrollY;
                            // Ensure it doesn't go off the top edge
                            top = Math.max(window.scrollY + 8, top);
                        }

                        // Apply positioning with scroll offset
                        dropdownElement.style.position = 'fixed';
                        dropdownElement.style.left = `${left - window.scrollX}px`;
                        dropdownElement.style.top = `${top - window.scrollY}px`;
                        dropdownElement.style.zIndex = '9999';

                        // Cache position data
                        this.positionCache.set(dropdownElement, {
                            left: left - window.scrollX,
                            top: top - window.scrollY,
                            width: dropdownWidth,
                            height: dropdownHeight
                        });
                    },

                    startTracking(dropdownElement, buttonElement) {
                        if (!dropdownElement || !buttonElement) return;

                        this.activeDropdown = dropdownElement;
                        this.activeButton = buttonElement;

                        // Improved scroll listener with multiple scroll containers
                        if (!this.scrollListener) {
                            this.scrollListener = () => {
                                if (this.activeDropdown && this.activeButton) {
                                    // Only update if elements are still in DOM
                                    if (document.body.contains(this.activeDropdown) &&
                                        document.body.contains(this.activeButton)) {
                                        this.updatePosition(this.activeDropdown, this.activeButton);
                                    } else {
                                        this.stopTracking();
                                    }
                                }
                            };

                            // Add listeners to all possible scroll containers
                            window.addEventListener('scroll', this.scrollListener, { passive: true, capture: true });
                            document.addEventListener('scroll', this.scrollListener, { passive: true, capture: true });

                            // Also listen to scroll on main containers that might scroll
                            const scrollContainers = [
                                document.querySelector('main'),
                                document.querySelector('.content'),
                                document.querySelector('.container'),
                                document.querySelector('[x-data]')
                            ].filter(el => el);

                            scrollContainers.forEach(container => {
                                container.addEventListener('scroll', this.scrollListener, { passive: true });
                            });
                        }

                        // Resize listener
                        if (!this.resizeListener) {
                            this.resizeListener = () => {
                                if (this.activeDropdown && this.activeButton) {
                                    this.updatePosition(this.activeDropdown, this.activeButton);
                                }
                            };
                            window.addEventListener('resize', this.resizeListener, { passive: true });
                        }
                    },

                    stopTracking() {
                        if (this.scrollListener) {
                            window.removeEventListener('scroll', this.scrollListener, { capture: true });
                            document.removeEventListener('scroll', this.scrollListener, { capture: true });

                            // Remove from scroll containers
                            const scrollContainers = [
                                document.querySelector('main'),
                                document.querySelector('.content'),
                                document.querySelector('.container'),
                                document.querySelector('[x-data]')
                            ].filter(el => el);

                            scrollContainers.forEach(container => {
                                container.removeEventListener('scroll', this.scrollListener);
                            });

                            this.scrollListener = null;
                        }

                        if (this.resizeListener) {
                            window.removeEventListener('resize', this.resizeListener);
                            this.resizeListener = null;
                        }

                        this.activeDropdown = null;
                        this.activeButton = null;
                    },

                    clearPosition(dropdownElement) {
                        if (dropdownElement) {
                            this.positionCache.delete(dropdownElement);
                        }
                        if (this.activeDropdown === dropdownElement) {
                            this.stopTracking();
                        }
                    }
                };
            };

            // Robust initialization
            const initDropdownPositioning = () => {
                if (document.readyState !== 'loading') {
                    setupDropdownPositioning();
                } else {
                    document.addEventListener('DOMContentLoaded', setupDropdownPositioning);
                }
                document.addEventListener('alpine:init', setupDropdownPositioning);
            };

            initDropdownPositioning();
            window.addEventListener('load', setupDropdownPositioning);
        })();
    </script>
@endpush


<x-dashboard.layout.default title="Challans">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.challans.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Challans
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        <hr class="border-t border-gray-300 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="data-table-simple"
                class="w-full min-w-full table-fixed text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Chalan
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                P.O
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Total Products
                        </th>
                        @role('admin')
                            <th scope="col" class="px-6 py-3">
                                <span class="flex items-center">
                                    User
                                    <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                                </span>
                            </th>
                        @endrole
                        <th scope="col" class="px-6 py-3">
                            Created
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($challans as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 hover:shadow-lg hover:scale-[1.01]">
                            <td class="flex items-center px-6 py-4">
                                <span>{{ $loop->iteration }}</span>
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <div>{{ $item->challan_no }}</div>
                                <div class="text-gray-600 dark:text-gray-400">
                                    {{ strlen($item->revision->quotation->customer->company->name) > 15
                                        ? substr($item->revision->quotation->customer->company->name, 0, 15) . '...'
                                        : $item->revision->quotation->customer->company->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->revision->quotation->po_no }}
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                @php
                                    $totalProducts = $item->products->count();
                                    $totalQuantity = $item->products->sum('quantity');
                                @endphp
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $totalProducts }} Products</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ $totalQuantity }} Total Qty</div>
                                </div>
                            </td>
                            @role('admin')
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->revision->createdBy->username }}
                                </td>
                            @endrole
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ date('d/m/Y H:i', strtotime($item->created_at)) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-center gap-1">
                                    @php
                                        $quotation = $item->revision->quotation;
                                        $latestAdvance = \App\Models\Bill::where('quotation_id', $quotation->id)
                                            ->where('bill_type', 'advance')
                                            ->orderBy('bill_date', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
                                        $latestBill = \App\Models\Bill::where('quotation_id', $quotation->id)
                                            ->orderBy('bill_date', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();
                                        $hasAdvance = !is_null($latestAdvance);
                                        $continueRegular = $latestBill && $latestBill->bill_type === 'regular' && (float) ($latestBill->due ?? 0) > 0 && !$hasAdvance;

                                        // Placeholder for Bill URL as Bill module is not fully migrated
                                        $billUrl = '#'; 
                                        /* 
                                        $billUrl = $hasAdvance
                                            ? route('tenant.bills.create', ['quotation_id' => $quotation->id, 'parent_bill_id' => $latestAdvance->id])
                                            : route('tenant.bills.create', ['quotation_id' => $quotation->id]);
                                        */

                                        $billLabel = $hasAdvance
                                            ? 'Create Running Bill'
                                            : ($continueRegular ? 'Continue Regular Bill' : 'Create Regular Bill');

                                        $dropdownData = [
                                            'canContinueChallan' => $item->can_continue_challan,
                                            'continueChallanUrl' => route('tenant.challans.create', ['quotation_id' => $item->revision->quotation->id]),
                                            'billUrl' => $billUrl,
                                            'billLabel' => $billLabel,
                                            'viewUrl' => route('tenant.challans.show', $item->id),
                                            'editUrl' => route('tenant.challans.edit', $item->id),
                                            'deleteUrl' => route('tenant.challans.destroy', $item->id),
                                            'showActions' => $item->bills_count == 0
                                        ];
                                    @endphp

                                    <button
                                        type="button"
                                        @click.stop="$dispatch('open-challan-dropdown', {
                                            data: JSON.parse($el.dataset.dropdown),
                                            trigger: $el
                                        })"
                                        data-dropdown="{{ json_encode($dropdownData) }}"
                                        class="group relative px-3 py-1.5 text-gray-600 dark:text-gray-300 hover:text-white dark:hover:text-gray-900 font-medium text-xs rounded-md overflow-hidden transition-all duration-300 hover:shadow-lg dark:hover:shadow-gray-600 hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50 bg-gray-100 dark:bg-gray-700">
                                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-gray-500 to-gray-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                        <span class="relative flex items-center gap-1">
                                            Actions
                                            <svg class="w-3 h-3 transition-transform duration-200"
                                                fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $challans->links() }}
        </div>
    </x-ui.card>

    {{-- Shared Dropdown Component --}}
    <div x-data="{
        open: false,
        data: {
            canContinueChallan: false,
            continueChallanUrl: '',
            billUrl: '',
            billLabel: '',
            viewUrl: '',
            editUrl: '',
            deleteUrl: '',
            showActions: false
        },
        trigger: null,

        init() {
            this.$watch('open', value => {
                if (value) {
                    this.$nextTick(() => this.updatePosition());
                }
            });
            // Close on scroll/resize
            window.addEventListener('resize', () => { this.open = false });
            window.addEventListener('scroll', () => { this.open = false }, true);
        },

        updatePosition() {
            if (!this.trigger || !this.$refs.dropdown) return;

            const triggerRect = this.trigger.getBoundingClientRect();
            const dropdownRect = this.$refs.dropdown.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            let top = triggerRect.bottom + 5;
            let left = triggerRect.left;

            // Check right edge
            if (left + dropdownRect.width > viewportWidth) {
                left = triggerRect.right - dropdownRect.width;
            }

            // Check bottom edge
            if (top + dropdownRect.height > viewportHeight) {
                top = triggerRect.top - dropdownRect.height - 5;
            }

            this.$refs.dropdown.style.top = `${top}px`;
            this.$refs.dropdown.style.left = `${left}px`;
        }
    }"
    @open-challan-dropdown.window="
        data = $event.detail.data;
        trigger = $event.detail.trigger;
        open = true;
    "
    class="relative z-[9999]">

        <div x-show="open"
             x-cloak
             x-ref="dropdown"
             @click.away="open = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed w-48 bg-white dark:bg-gray-800 rounded-md overflow-hidden shadow-xl border border-gray-200 dark:border-gray-600 z-[9999]">

            <div class="py-1">
                <template x-if="data.canContinueChallan">
                    <a :href="data.continueChallanUrl"
                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                        Continue Challan
                    </a>
                </template>

                {{-- Bill links are disabled for now --}}
                {{-- 
                <a :href="data.billUrl"
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
                   x-text="data.billLabel">
                </a> 
                --}}

                <a :href="data.viewUrl"
                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                    View
                </a>

                <template x-if="data.showActions">
                    <div>
                        <a :href="data.editUrl"
                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                            Edit
                        </a>

                        <form :action="data.deleteUrl" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                Delete
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </div>

</x-dashboard.layout.default>
