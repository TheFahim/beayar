@props([
    'value' => '', // The selected ID
    'options' => [], // The list of options to search from
    'apiEndpoint' => '', // The API endpoint to fetch data
    'displayField' => 'name', // The field to display in options (e.g., 'customer_name', 'bill_no') - used when displayTemplate is not provided
    'displayTemplate' => '', // Template for display format (e.g., '{customer_name} - {company.name}' or '{customer_name} ({company.name})')
    'searchFields' => ['name'], // Fields to search in (array)
    'placeholder' => 'Search and select an option...', // Placeholder text
    'noResultsText' => 'No results found.', // Text when no results
    'createEvent' => null, // Event to dispatch when create is clicked
    'createLabel' => 'Create New', // Label for create button
    'showImages' => false, // Whether to show images in dropdown options
    'imageField' => 'image', // The field that contains image data
    'imagePath' => 'path', // The path to the image within the image object
    'perPage' => 20, // Page size for server-side pagination
    'debounceMs' => 300, // Debounce delay for search input
])
@php
    $nameAttr = $attributes->get('name');
    $providedValue = $attributes->get('value');
    // $dotKey = null;
    // if ($nameAttr) {
    //     $dotKey = preg_replace(['\/\]/', '\/\[/'], ['', '.'], $nameAttr);
    //     $dotKey = trim($dotKey, '.');
    // }
    // $finalValue = $dotKey !== null ? old($dotKey, $providedValue) : $providedValue;
@endphp

<div
    x-data="searchableSelect({
        value: @js($value),
        endpoint: @js($apiEndpoint),
        displayField: @js($displayField),
        displayTemplate: @js($displayTemplate),
        searchFields: @js($searchFields),
        placeholder: @js($placeholder),
        noResultsText: @js($noResultsText),
        createEvent: @js($createEvent),
        createLabel: @js($createLabel),
        showImages: @js($showImages),
        imageField: @js($imageField),
        imagePath: @js($imagePath),
        perPage: @js($perPage),
        debounceMs: @js($debounceMs)
    })"
    x-init="init()"
    x-modelable="selectedValue"
    x-effect="updateDisplayFromSelected()"
    @click.away="open = false"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    {{-- This hidden input will hold the actual value (e.g., customer_id, bill_id) for form submission --}}
    <input type="hidden"
        @if($attributes->get('name')) name="{{ $attributes->get('name') }}" @endif
        @if($attributes->get('x-bind:name')) x-bind:name="{{ $attributes->get('x-bind:name') }}" @endif
        x-bind:value="selectedValue">
    @if ($dotKey ?? false)
        @error($dotKey)
            <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
        @enderror
    @endif
    {{-- The visible input for searching and displaying the selected item --}}
    <div class="relative">
        <input
            type="text"
            x-model="searchTerm"
            @focus="open = true; isInputFocused = true"
            @blur="isInputFocused = false"
            @input.debounce.300ms="onSearchInput"
            x-bind:placeholder="placeholder"
            @keydown.arrow-down.prevent="highlightNext()"
            @keydown.arrow-up.prevent="highlightPrev()"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.escape="open = false"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        >
        {{-- Dropdown Arrow --}}
        <div class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
             <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
        </div>
    </div>
    {{-- The dropdown list --}}
    <div
        x-show="open"
        x-transition
        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-md shadow-lg max-h-60 overflow-y-auto border dark:border-gray-600"
        style="display: none;"
        x-ref="dropdown"
        @scroll="onDropdownScroll"
    >
        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
            <template x-if="loading">
                 <li class="px-4 py-2 text-gray-500">Loading...</li>
            </template>
            <template x-if="!loading && filteredOptions.length === 0">
                 <li class="px-4 py-3 text-gray-500 text-center">
                    <p class="text-sm" x-text="noResultsText"></p>
                    <template x-if="createEvent">
                        <button type="button"
                            @click="open = false; $dispatch(createEvent)"
                            class="mt-2 text-blue-600 hover:text-blue-800 text-xs font-bold uppercase tracking-wide hover:underline transition-all"
                            x-text="createLabel">
                        </button>
                    </template>
                 </li>
            </template>
            <template x-for="(option, index) in filteredOptions" :key="option.id">
                <li
                    @click="selectOption(option)"
                    @mouseenter="activeIndex = index"
                    :class="{ 'bg-blue-50 dark:bg-blue-900/30': activeIndex === index, 'hover:bg-gray-100 dark:hover:bg-gray-700': activeIndex !== index }"
                    :data-index="index"
                    class="px-4 py-2 cursor-pointer flex items-center gap-3 transition-colors"
                >
                    <template x-if="showImages && getImageUrl(option)">
                        <img
                            :src="getImageUrl(option)"
                            :alt="getDisplayText(option)"
                            class="w-10 h-10 object-cover rounded-md flex-shrink-0"
                            onerror="this.style.display='none'"
                        >
                    </template>
                    <div class="flex-1">
                        <div x-text="getDisplayText(option)" class="text-sm font-medium text-gray-900 dark:text-white"></div>
                        {{-- <template x-if="showImages && option.id">
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="'ID: ' + option.id"></div>
                        </template> --}}
                    </div>
                </li>
            </template>
        </ul>
    </div>
</div>
<script>
    document.addEventListener('alpine:initializing', () => {
        Alpine.data('searchableSelect', (config) => ({
            open: false,
            searchTerm: '',
            selectedValue: config.value || '',
            lastSelectedValue: null,
            isInputFocused: false,
            hasSetInitialDisplay: false,
            allOptions: [],
            filteredOptions: [],
            loading: true,
            isFetching: false,
            serverPagination: false,
            endpoint: config.endpoint,
            displayField: config.displayField || 'name',
            displayTemplate: config.displayTemplate || '',
            searchFields: config.searchFields || ['name'],
            placeholder: config.placeholder || 'Search and select an option...',
            noResultsText: config.noResultsText || 'No results found.',
            createEvent: config.createEvent || null,
            createLabel: config.createLabel || 'Create New',
            showImages: config.showImages || false,
            imageField: config.imageField || 'image',
            imagePath: config.imagePath || 'path',
            page: 1,
            perPage: Number(config.perPage) || 20,
            lastPage: 1,
            hasMore: false,
            debounceMs: Number(config.debounceMs) || 300,
            activeIndex: -1,
            init() {
                if (!this.endpoint) {
                    this.loading = false;
                    console.error('SearchableSelect: API endpoint is not defined.');
                    return;
                }

                // Track initial selected value for sync logic
                this.lastSelectedValue = this.selectedValue || null;

                // Listen for product creation events to refresh options
                window.addEventListener('product-created', () => {
                    this.fetchOptions(true);
                });

                // Initial fetch with empty query
                this.fetchOptions(true);
            },
            async fetchOptions(reset = false) {
                try {
                    this.isFetching = true;
                    this.loading = this.page === 1 && reset;
                    const url = new URL(this.endpoint, window.location.origin);
                    if (this.searchTerm) url.searchParams.set('q', this.searchTerm);
                    url.searchParams.set('page', this.page);
                    url.searchParams.set('per_page', this.perPage);

                    const response = await fetch(url.toString());
                    const payload = await response.json();

                    // Determine if API uses paginated structure
                    if (payload && Array.isArray(payload)) {
                        this.serverPagination = false;
                        this.allOptions = payload;
                        this.lastPage = 1;
                        this.hasMore = false;
                    } else {
                        this.serverPagination = true;
                        const data = payload.data || [];
                        if (reset) {
                            this.allOptions = data;
                        } else {
                            this.allOptions = this.allOptions.concat(data);
                        }
                        this.lastPage = payload.last_page || 1;
                        this.hasMore = (payload.current_page || 1) < this.lastPage;
                    }

                    // If a value is pre-selected (e.g., from old()), set the display text ONLY on initial load.
                    // Avoid overwriting user-entered search terms during subsequent fetches.
                    if (!this.hasSetInitialDisplay && this.selectedValue && !this.open && this.searchTerm === '') {
                        const selected = this.allOptions.find(opt => opt.id == this.selectedValue);
                        if (selected) {
                            this.searchTerm = this.getDisplayText(selected);
                            this.hasSetInitialDisplay = true;
                            this.lastSelectedValue = this.selectedValue;
                        }
                    }

                    this.filterOptions();
                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    this.loading = false;
                    this.isFetching = false;
                }
            },
            getDisplayText(option) {
                // If displayTemplate is provided, use it for formatting
                if (this.displayTemplate) {
                    return this.formatTemplate(this.displayTemplate, option);
                }

                // Fallback to original displayField logic
                if (this.displayField.includes('.')) {
                    // Handle nested fields like 'company.name'
                    const fields = this.displayField.split('.');
                    let value = option;
                    for (const field of fields) {
                        value = value?.[field];
                    }
                    return value || '';
                }
                return option[this.displayField] || '';
            },
            formatTemplate(template, option) {
                // Replace placeholders in template with actual values
                // Supports both simple fields {field} and nested fields {field.subfield}
                return template.replace(/\{([^}]+)\}/g, (match, fieldPath) => {
                    if (fieldPath.includes('.')) {
                        // Handle nested fields
                        const fields = fieldPath.split('.');
                        let value = option;
                        for (const field of fields) {
                            value = value?.[field];
                        }
                        return value || '';
                    }
                    return option[fieldPath] || '';
                });
            },
            updateDisplayFromSelected() {
                // Only sync display if selection actually changed and user isn't editing
                if (!this.selectedValue) return;

                // Don't override user input while typing or dropdown open
                if (this.open || this.isInputFocused) return;

                // Skip if selection hasn't changed since last sync
                if (this.selectedValue === this.lastSelectedValue && this.hasSetInitialDisplay) return;

                const selected = this.allOptions.find(opt => opt.id == this.selectedValue);
                if (selected) {
                    this.searchTerm = this.getDisplayText(selected);
                    this.hasSetInitialDisplay = true;
                    this.lastSelectedValue = this.selectedValue;
                }
            },
            getImageUrl(option) {
                if (!this.showImages || !option[this.imageField]) {
                    return null;
                }

                const imageObj = option[this.imageField];
                if (!imageObj) return null;

                // Get the image path from the image object
                const imagePath = imageObj[this.imagePath];
                if (!imagePath) return null;

                // Return the full URL - assuming images are stored in public/uploads
                return `/${imagePath}`;
            },
            filterOptions() {
                // For server-side pagination, we don't filter locally; just display fetched options
                if (this.serverPagination) {
                    this.filteredOptions = this.allOptions;
                    return;
                }

                if (!this.searchTerm) {
                    this.filteredOptions = this.allOptions;
                    return;
                }

                const searchTerm = this.searchTerm.toLowerCase();
                this.filteredOptions = this.allOptions.filter(option => {
                    return this.searchFields.some(field => {
                        if (field.includes('.')) {
                            const fields = field.split('.');
                            let value = option;
                            for (const f of fields) {
                                value = value?.[f];
                            }
                            return value?.toString().toLowerCase().includes(searchTerm);
                        }
                        return option[field]?.toString().toLowerCase().includes(searchTerm);
                    });
                });
            },
            onSearchInput() {
                if (this.serverPagination) {
                    // Reset to first page and fetch server-side
                    this.page = 1;
                    this.fetchOptions(true);
                } else {
                    // Client-side filtering
                    this.filterOptions();
                }
                this.activeIndex = -1;
            },
            highlightNext() {
                if (!this.open) {
                    this.open = true;
                    return;
                }
                if (this.activeIndex < this.filteredOptions.length - 1) {
                    this.activeIndex++;
                    this.scrollToActive();
                }
            },
            highlightPrev() {
                if (this.activeIndex > 0) {
                    this.activeIndex--;
                    this.scrollToActive();
                }
            },
            selectHighlighted() {
                if (this.activeIndex >= 0 && this.activeIndex < this.filteredOptions.length) {
                    this.selectOption(this.filteredOptions[this.activeIndex]);
                } else if (this.filteredOptions.length === 1) {
                     this.selectOption(this.filteredOptions[0]);
                }
            },
            scrollToActive() {
                this.$nextTick(() => {
                    const activeEl = this.$refs.dropdown.querySelector(`[data-index='${this.activeIndex}']`);
                    if (activeEl) {
                        activeEl.scrollIntoView({ block: 'nearest' });
                    }
                });
            },
            onDropdownScroll(e) {
                if (!this.serverPagination || !this.hasMore || this.isFetching) return;
                const target = e.target;
                const nearBottom = target.scrollTop + target.clientHeight >= target.scrollHeight - 50;
                if (nearBottom) {
                    this.page += 1;
                    this.fetchOptions(false);
                }
            },

            selectOption(option) {
                this.selectedValue = option.id;
                this.searchTerm = this.getDisplayText(option);
                this.hasSetInitialDisplay = true;
                this.lastSelectedValue = this.selectedValue;
                this.open = false;

                // Sync with parent via x-modelable/x-model
                this.$dispatch('input', this.selectedValue);

                // Dispatch custom event with selected option data
                this.$el.dispatchEvent(new CustomEvent('option-selected', {
                    detail: { option: option },
                    bubbles: true
                }));
            }
        }));
    });
</script>
