<x-dashboard.layout.default title="Edit Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <div x-data="quotationForm(quotationFormConfig)">
        <!-- Fixed Revision History Toggle Button -->
        <div class="fixed top-[4.15rem] right-4 z-50">
            <button id="revisionHistoryToggle"
                class="bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 rounded-full p-3 hover:shadow-xl transition-all duration-300 group transform hover:scale-105"
                title="Revision History">
                <div class="flex items-center gap-2">
                    <x-ui.svg.clock
                        class="h-5 w-5 text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                    <span
                        class="hidden lg:inline text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        History
                    </span>
                    <span
                        class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs px-2 py-1 rounded-full font-medium animate-pulse">
                        {{ $revisions ? $revisions->count() : 0 }}
                    </span>
                    <!-- Visual indicator for panel state -->
                    <div id="panelStateIndicator" class="w-2 h-2 bg-gray-400 rounded-full transition-all duration-300">
                    </div>
                </div>
            </button>
        </div>

        @include('dashboard.quotations.partials.revision-history-panel')

        <!-- Main Content Area - Now uses full width -->
        <div class="w-full max-w-none">
            <!-- Header with improved spacing -->
            <div class="flex items-center justify-between mb-6 px-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Quotation</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Revision {{ $loadRevision->revision_no ?? 'N/A' }}
                    </span>
                    @if ($loadRevision && $loadRevision->is_active)
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Active Revision
                        </span>
                    @endif
                </div>
            </div>

            <form id="quotation-form" x-ref="quotationForm" class="space-y-3"
                action="{{ route('quotations.update', $quotation->id) }}" method="POST"
                enctype="multipart/form-data" @submit="handleSubmit" novalidate>
                @csrf
                @method('PUT')

                @include('dashboard.quotations.partials.basic-information')

                @include('dashboard.quotations.partials.quotation-information')

                {{-- Products Section --}}
                @include('dashboard.quotations.partials.products-section')

                {{-- pricing and totals section --}}
                @include('dashboard.quotations.partials.pricing-totals-section')

                {{-- Action Buttons --}}
                @include('dashboard.quotations.partials.action-buttons-section-edit')

                <!-- Specification Selection Modal -->
                @include('dashboard.quotations.partials.specification-modal')
                <!-- Create Product Modal -->
                @include('dashboard.quotations.partials.create-product-modal')
                <!-- Image Library Modal (reusing existing modal) -->
                {{-- @include('dashboard.quotations.partials.upload-image-modal') --}}
                <!-- Validation Error Modal -->
                @include('dashboard.quotations.partials.validation-modal')

            </form>

            <!-- Upload Image Modal (for new product creation) -->
            @include('dashboard.quotations.partials.upload-new-image-modal')
            <!-- Brand Origins Modal -->
            @include('dashboard.quotations.partials.brand-origins-modal')
        </div>
    </div>


</x-dashboard.layout.default>

<script>
    // Revision History Panel Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('revisionHistoryToggle');
        const panel = document.getElementById('revisionHistoryPanel');
        const closeButton = document.getElementById('closeRevisionPanel');
        const overlay = document.getElementById('revisionHistoryOverlay');
        const panelStateIndicator = document.getElementById('panelStateIndicator');

        // Toggle panel visibility with enhanced visual feedback
        function togglePanel() {
            const isOpen = !panel.classList.contains('translate-x-full');

            if (isOpen) {
                // Close panel
                panel.classList.add('translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                panelStateIndicator.classList.remove('bg-green-500', 'animate-pulse');
                panelStateIndicator.classList.add('bg-gray-400');
            } else {
                // Open panel
                panel.classList.remove('translate-x-full');
                panelStateIndicator.classList.remove('bg-gray-400');
                panelStateIndicator.classList.add('bg-green-500', 'animate-pulse');
                if (window.innerWidth < 1024) { // Mobile
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }
            }
        }

        // Event listeners
        toggleButton.addEventListener('click', togglePanel);
        closeButton.addEventListener('click', togglePanel);
        overlay.addEventListener('click', togglePanel);

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else if (!panel.classList.contains('translate-x-full')) {
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        });

        // Escape key to close panel
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !panel.classList.contains('translate-x-full')) {
                togglePanel();
            }
        });

        // Close panel when clicking outside (optional)
        document.addEventListener('click', function(e) {
            const isOpen = !panel.classList.contains('translate-x-full');
            if (isOpen &&
                !panel.contains(e.target) &&
                !toggleButton.contains(e.target)) {
                togglePanel();
            }
        });
    });

    // Enhanced Revision Card Toggle Functionality with smooth animations
    function toggleRevisionCard(index) {
        const details = document.getElementById(`revisionCardDetails${index}`);
        const chevron = document.getElementById(`revisionCardChevron${index}`);

        if (details.classList.contains('hidden')) {
            // Show details with smooth animation
            details.classList.remove('hidden');
            details.style.maxHeight = '0px';
            details.style.opacity = '0';
            details.style.transform = 'translateY(-10px)';

            // Force reflow
            details.offsetHeight;

            // Animate to full height
            details.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            details.style.maxHeight = details.scrollHeight + 'px';
            details.style.opacity = '1';
            details.style.transform = 'translateY(0)';

            // Rotate chevron
            chevron.classList.add('rotate-180', 'text-blue-500');

            // Clean up after animation
            setTimeout(() => {
                details.style.maxHeight = '';
                details.style.transition = '';
            }, 300);
        } else {
            // Hide details with smooth animation
            details.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            details.style.maxHeight = details.scrollHeight + 'px';

            // Force reflow
            details.offsetHeight;

            // Animate to collapsed state
            details.style.maxHeight = '0px';
            details.style.opacity = '0';
            details.style.transform = 'translateY(-10px)';

            // Reset chevron
            chevron.classList.remove('rotate-180', 'text-blue-500');

            // Hide after animation
            setTimeout(() => {
                details.classList.add('hidden');
                details.style.maxHeight = '';
                details.style.opacity = '';
                details.style.transform = '';
                details.style.transition = '';
            }, 300);
        }
    }

    // Pass configuration to Alpine component
    window.quotationFormConfig = {
        mode: 'edit',
        // Old values from Laravel or existing quotation
        oldQuotation: {
            quotation_no: @json(old('quotation.quotation_no', $quotation->quotation_no ?? '')),
            ship_to: @json(old('quotation.ship_to', $quotation->ship_to ?? '')),
            customer_id: @json(old('quotation.customer_id', $quotation->customer_id ?? ''))
        },
        oldQuotationRevision: {!! json_encode(
            old('quotation_revision', [
                'id' => $loadRevision->id ?? '',
                'type' => $loadRevision->type ?? 'normal',
                'date' => optional($loadRevision->date)->format('Y-m-d') ?? '',
                'validity' => optional($loadRevision->validity)->format('Y-m-d') ?? '',
                'currency' => $loadRevision->currency ?? 'USD',
                'exchange_rate' => $loadRevision->exchange_rate ?? '',
                'discount' => $loadRevision->discount_amount ?? 0,
                'subtotal' => $loadRevision->subtotal ?? 0,
                'total' => $loadRevision->total ?? 0,
                'discount_percentage' => $loadRevision->discount_percentage ?? 0,
                'vat_amount' => $loadRevision->vat_amount ?? 0,
                'shipping' => $loadRevision->shipping ?? 0,
                'vat_percentage' => $loadRevision->vat_percentage ?? 15,
                'saved_as' => $loadRevision->saved_as ?? 'draft',
                'terms_conditions' => $loadRevision->terms_conditions ?? '',
            ]),
        ) !!},
        oldQuotationProducts: {!! json_encode(
            old(
                'quotation_products',
                ($loadRevision->products ?? collect())->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'product_id' => $p->product_id,
                            'size' => $p->size,
                            'specification_id' => $p->specification_id,
                            'specifications' => [],
                            'add_spec' => $p->add_spec,
                            'brand_origin_id' => $p->brand_origin_id,
                            'requision_no' => $p->requision_no,
                            'unit' => $p->unit,
                            'delivery_time' => $p->delivery_time ?? '10 days',
                            'unit_price' => $p->unit_price ?? 0,
                            'quantity' => $p->quantity ?? 1,
                            'foreign_currency_buying' => $p->foreign_currency_buying ?? 0,
                            'bdt_buying' => $p->bdt_buying ?? 0,
                            'air_sea_freight' => $p->air_sea_freight ?? 0,
                            'air_sea_freight_rate' => $p->air_sea_freight_rate ?? 0,
                            'weight' => $p->weight ?? 0,
                            'tax_percentage' => $p->tax_percentage ?? 0,
                            'tax' => $p->tax ?? 0,
                            'att_percentage' => $p->att_percentage ?? 0,
                            'att' => $p->att ?? 0,
                            'margin' => $p->margin ?? 0,
                            'margin_value' => $p->margin_value ?? 0,
                            'showAdvanced' => false,
                        ];
                    })->values(),
            ),
        ) !!},
        validityDays: {{ $loadRevision->date && $loadRevision->validity
            ? \Carbon\Carbon::parse($loadRevision->date)->diffInDays(\Carbon\Carbon::parse($loadRevision->validity), false)
            : (is_numeric(old('quotation_revision.validity_days'))
                ? old('quotation_revision.validity_days')
                : 15) }},
        discount_percentage: {{ $loadRevision->discount_percentage ?? (old('discount_percentage', 0) ?? 0) }},

        // Routes
        routes: {
            exchangeRate: @json(route('exchange.rate')),
            nextNumber: @json(route('quotations.next-number')),
            createProduct: @json(route('quotations.create-product')),
            customersSearch: @json(route('customers.search')),
            productsSearch: @json(route('products.search')),
            storeQuotation: @json(route('quotations.revisions.update', [$quotation->id, $loadRevision->id]))
        },

        // CSRF Token
        csrfToken: @json(csrf_token())
    };

    // Revision History Interactive Functions
    document.addEventListener('DOMContentLoaded', function() {
        const revisionToggle = document.getElementById('revisionToggle');
        const revisionContent = document.getElementById('revisionContent');
        const revisionChevron = document.getElementById('revisionChevron');

        // Initialize collapsed state on mobile
        let isCollapsed = window.innerWidth < 1024; // lg breakpoint

        if (isCollapsed) {
            revisionContent.style.maxHeight = '0';
            revisionContent.style.overflow = 'hidden';
            revisionChevron.style.transform = 'rotate(-90deg)';
        }

        // Toggle revision section
        if (revisionToggle) {
            revisionToggle.addEventListener('click', function() {
                isCollapsed = !isCollapsed;

                if (isCollapsed) {
                    revisionContent.style.maxHeight = '0';
                    revisionContent.style.overflow = 'hidden';
                    revisionChevron.style.transform = 'rotate(-90deg)';
                } else {
                    revisionContent.style.maxHeight = revisionContent.scrollHeight + 'px';
                    revisionContent.style.overflow = 'visible';
                    revisionChevron.style.transform = 'rotate(0deg)';
                }
            });
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024 && isCollapsed) {
                // Auto-expand on desktop
                isCollapsed = false;
                revisionContent.style.maxHeight = 'none';
                revisionContent.style.overflow = 'visible';
                revisionChevron.style.transform = 'rotate(0deg)';
            }
        });

        // Add smooth scroll behavior for revision links
        document.querySelectorAll('a[href*="revision_id"]').forEach(link => {
            link.addEventListener('click', function(e) {
                // Add loading state
                this.style.opacity = '0.7';
                this.style.pointerEvents = 'none';

                // Create loading indicator
                const originalText = this.innerHTML;
                this.innerHTML =
                    '<svg class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';

                // Reset after a delay (in case navigation is slow)
                setTimeout(() => {
                    this.style.opacity = '1';
                    this.style.pointerEvents = 'auto';
                    this.innerHTML = originalText;
                }, 3000);
            });
        });
    });

    // Toggle individual revision details
    function toggleRevisionDetails(index) {
        const details = document.getElementById(`revisionDetails${index}`);
        const chevron = details.parentElement.querySelector('svg');

        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            details.style.maxHeight = details.scrollHeight + 'px';
            if (chevron) chevron.style.transform = 'rotate(90deg)';
        } else {
            details.style.maxHeight = '0';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            setTimeout(() => {
                details.classList.add('hidden');
            }, 300);
        }
    }

    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close all expanded revision details
            document.querySelectorAll('[id^="revisionDetails"]').forEach(detail => {
                if (!detail.classList.contains('hidden')) {
                    detail.style.maxHeight = '0';
                    detail.parentElement.querySelector('svg').style.transform = 'rotate(0deg)';
                    setTimeout(() => {
                        detail.classList.add('hidden');
                    }, 300);
                }
            });
        }
    });


</script>
