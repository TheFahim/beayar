<x-dashboard.layout.default title="New Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <div x-data="quotationForm(quotationFormConfig)">
        <h2 class="mx-5 text-xl font-extrabold dark:text-white">Add New Quotation</h2>

        <form id="quotation-form" x-ref="quotationForm" class="space-y-3" action="{{ route('tenant.quotations.store') }}" method="POST" enctype="multipart/form-data"
            @submit="handleSubmit" novalidate>
        {{-- <form class="space-y-3" action="{{ route('tenant.quotations.store') }}" method="POST" enctype="multipart/form-data"> --}}
            @csrf

            @include('tenant.quotations.partials.basic-information')

            @include('tenant.quotations.partials.quotation-information')

            {{-- Products Section --}}
            @include('tenant.quotations.partials.products-section')

            {{-- pricing and totals section --}}
            @include('tenant.quotations.partials.pricing-totals-section')

            {{-- Action Buttons --}}
            @include('tenant.quotations.partials.action-buttons-section')

            <!-- Specification Selection Modal -->
            @include('tenant.quotations.partials.specification-modal')
            <!-- Create Product Modal -->
            @include('tenant.quotations.partials.create-product-modal')
            <!-- Image Library Modal (reusing existing modal) -->
            {{-- @include('tenant.quotations.partials.upload-image-modal') --}}
            <!-- Validation Error Modal -->
            @include('tenant.quotations.partials.validation-modal')



        </form>

        <!-- Upload Image Modal (for new product creation) -->
        @include('tenant.quotations.partials.upload-new-image-modal')
        <!-- Brand Origins Modal -->
        @include('tenant.quotations.partials.brand-origins-modal')
    </div>

</x-dashboard.layout.default>

<script>
    // Pass configuration to Alpine component
    window.quotationFormConfig = {
        mode: 'create',
        // Old values from Laravel
        oldQuotation: {
            quotation_no: @json(old('quotation.quotation_no', '')),
            ship_to: @json(old('quotation.ship_to', '')),
            quotation_id: @json(old('quotation.quotation_id', ''))
        },
        oldQuotationRevision: {!! json_encode(
            old('quotation_revision', [
                'type' => 'normal',
                'date' => '',
                'validity' => '',
                'currency' => 'USD',
                'exchange_rate' => '',
                'discount' => 0,
                'shipping' => 0,
                'vat_percentage' => 15,
                'saved_as' => 'draft',
                'terms_conditions' => '',
            ])
        ) !!},
        oldQuotationProducts: {!! json_encode(
            old('quotation_products', [
                [
                    'product_id' => '',
                    'size' => '',
                    'specification_id' => '',
                    'specifications' => [],
                    'brand_origin_id' => '',
                    'add_spec' => '',
                    'unit' => '',
                    'delivery_time' => "10 days",
                    'unit_price' => 0,
                    'quantity' => 1,
                    'requision_no' => '',
                    'foreign_currency_buying' => 0,
                    'bdt_buying' => 0,
                    'air_sea_freight' => 0,
                    'weight' => 0,
                    'tax' => 0,
                    'att' => 0,
                    'margin' => 0,
                    'showAdvanced' => false,
                ]
            ])
        ) !!},
        validityDays: {{ old('quotation_revision.validity_days', 15) }},
        discount_percentage: {{ old('discount_percentage', 0) }},

        // Routes for AJAX calls
        routes: {
            exchangeRate: @json(route('exchange.rate') ?? '#'),
            nextNumber: @json(route('tenant.quotations.next-number') ?? '#'),
            createProduct: @json(route('tenant.quotations.create-product') ?? '#'),
            customersSearch: @json(route('companies.search')),
            productsSearch: @json(route('tenant.products.search')),
            imagesStore: @json(route('tenant.images.store')),
            // storeQuotation is handled by form submission
        },

        // CSRF Token
        csrfToken: @json(csrf_token())
    };
</script>
