@props([
    'value' => '', // The selected ID
    'options' => [], // The list of options to search from
    'apiEndpoint' => '', // The API endpoint to fetch data
])

<div
    x-data="searchableSelect({
        value: '{{ $value }}',
        endpoint: '{{ $apiEndpoint }}'
    })"
    x-init="init"
    class="relative"
    @click.away="open = false"
>
    {{-- This hidden input will hold the actual value (e.g., bill_id) for form submission --}}
    <input type="hidden" name="{{ $attributes->get('name') }}" x-bind:value="selectedValue">

    {{-- The visible input for searching and displaying the selected item --}}
    <div class="relative">
        <input
            type="text"
            x-model="searchTerm"
            @focus="open = true"
            @input.debounce.300ms="filterOptions"
            placeholder="Search and select a bill..."
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
    >
        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
            <template x-if="loading">
                 <li class="px-4 py-2 text-gray-500">Loading...</li>
            </template>
            <template x-if="!loading && filteredOptions.length === 0">
                 <li class="px-4 py-2 text-gray-500">No bills found.</li>
            </template>
            <template x-for="option in filteredOptions" :key="option.id">
                <li
                    @click="selectOption(option)"
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-text="option.bill_no"
                ></li>
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
            allOptions: [],
            filteredOptions: [],
            loading: true,
            endpoint: config.endpoint,

            init() {
                if (!this.endpoint) {
                    this.loading = false;
                    console.error('SearchableSelect: API endpoint is not defined.');
                    return;
                }

                fetch(this.endpoint)
                    .then(response => response.json())
                    .then(data => {
                        this.allOptions = data;
                        // If a value is pre-selected (e.g., from old() helper), find and display it.
                        const selected = this.allOptions.find(opt => opt.id == this.selectedValue);
                        if (selected) {
                            this.searchTerm = selected.bill_no;
                        }
                        this.filterOptions(); // Initial filter
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        this.loading = false;
                    });
            },

            filterOptions() {
                if (!this.searchTerm) {
                    this.filteredOptions = this.allOptions;
                    return;
                }
                this.filteredOptions = this.allOptions.filter(option => {
                    return option.bill_no.toLowerCase().includes(this.searchTerm.toLowerCase());
                });
            },

            selectOption(option) {
                this.selectedValue = option.id;
                this.searchTerm = option.bill_no;
                this.open = false;
            }
        }));
    });
</script>
