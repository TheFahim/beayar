<x-dashboard.layout.default title="New Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <div x-data="quotationForm(quotationFormConfig)">
        <h2 class="mx-5 text-xl font-extrabold dark:text-white">Add New Quotation</h2>

        <form id="quotation-form" x-ref="quotationForm" class="space-y-3" action="{{ route('quotations.store') }}" method="POST" enctype="multipart/form-data"
            @submit="handleSubmit" novalidate>
        {{-- <form class="space-y-3" action="{{ route('quotations.store') }}" method="POST" enctype="multipart/form-data"> --}}
            @csrf

            @include('dashboard.quotations.partials.basic-information')

            @include('dashboard.quotations.partials.quotation-information')

            {{-- Products Section --}}
            @include('dashboard.quotations.partials.products-section')

            {{-- pricing and totals section --}}
            @include('dashboard.quotations.partials.pricing-totals-section')

            {{-- Action Buttons --}}
            @include('dashboard.quotations.partials.action-buttons-section')

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
                'vat_percentage' => 10,
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
                ],
            ]),
        ) !!},
        validityDays: {{ is_numeric(old('quotation_revision.validity_days')) ? old('quotation_revision.validity_days') : 15 }},
        discount_percentage: {{ old('discount_percentage', 0) }},

        // Routes
        routes: {
            exchangeRate: @json(route('exchange.rate')),
            nextNumber: @json(route('quotations.next-number')),
            createProduct: @json(route('quotations.create-product')),
            customersSearch: @json(route('customers.search')),
            productsSearch: @json(route('products.search')),
            storeQuotation: @json(route('quotations.store'))
        },

        // CSRF Token
        csrfToken: @json(csrf_token())
    };
</script>
