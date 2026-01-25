                    <div x-data="{ isOpen: false, selected: [], search: '', items: ['Bonnie Green', 'Jese Leos', 'Michael Gough', 'Robert Wall', 'Joseph Mcfall', 'Leslie Livingston', 'Roberta Casas'] }" class="relative">
                        <!-- Dropdown Button -->
                        <button @click="isOpen = !isOpen"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                            type="button">
                            <span
                                x-text="selected.length > 0 ? `${selected.length} Selected` : 'Dropdown search'"></span>
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="isOpen" @click.away="isOpen = false"
                            class="absolute z-10 mt-2 bg-white rounded-lg shadow w-60 dark:bg-gray-700">
                            <!-- Search -->
                            <div class="p-3">
                                <input x-model="search" type="text" placeholder="Search user"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                            </div>

                            <!-- Items List -->
                            <ul class="h-48 px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200">
                                <template
                                    x-for="item in items.filter(i => i.toLowerCase().includes(search.toLowerCase()))"
                                    :key="item">
                                    <li>
                                        <div
                                            class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <input type="checkbox" :id="`checkbox-${item}`" :value="item"
                                                @change="toggleSelection(item)" :checked="selected.includes(item)"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label :for="`checkbox-${item}`"
                                                class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">
                                                <span x-text="item"></span>
                                            </label>
                                        </div>
                                    </li>
                                </template>
                            </ul>

                            <!-- Delete Button -->
                            <button @click="clearSelection"
                                class="flex items-center p-3 text-sm font-medium text-red-600 border-t border-gray-200 rounded-b-lg bg-gray-50 dark:border-gray-600 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-500 hover:underline">
                                <svg class="w-4 h-4 me-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 18">
                                    <path
                                        d="M6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Zm11-3h-6a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2Z" />
                                </svg>
                                Delete all selected
                            </button>
                        </div>
                    </div>
