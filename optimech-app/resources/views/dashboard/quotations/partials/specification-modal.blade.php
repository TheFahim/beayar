            <!-- Specification Selection Modal -->
            <div x-show="specificationModal.show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

                <!-- Background overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeSpecificationModal()">
                </div>

                <!-- Modal content -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">

                        <!-- Modal Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Select Specification
                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">
                                        (Product #<span x-text="specificationModal.productIndex + 1"></span>)
                                    </span>
                                </h3>
                                <button type="button" @click="closeSpecificationModal()"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-4 max-h-96 overflow-y-auto">
                            <div x-show="specificationModal.specifications.length === 0"
                                class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-sm">No specifications available for this product.</p>
                            </div>

                            <div x-show="specificationModal.specifications.length > 0" class="space-y-3">
                                <!-- Clear Selection Option -->
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    :class="!specificationModal.selectedId ?
                                        'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600' : ''"
                                    @click="selectSpecification(null)">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                :class="!specificationModal.selectedId ? 'border-blue-500 bg-blue-500' :
                                                    'border-gray-300 dark:border-gray-600'">
                                                <div x-show="!specificationModal.selectedId"
                                                    class="w-2 h-2 bg-white rounded-full"></div>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">No
                                                Specification</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Clear the current
                                                selection</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Specification Options -->
                                <template x-for="spec in specificationModal.specifications" :key="spec.id">
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        :class="specificationModal.selectedId === spec.id ?
                                            'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600' : ''"
                                        @click="selectSpecification(spec.id)">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mt-1">
                                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                    :class="specificationModal.selectedId === spec.id ?
                                                        'border-blue-500 bg-blue-500' :
                                                        'border-gray-300 dark:border-gray-600'">
                                                    <div x-show="specificationModal.selectedId === spec.id"
                                                        class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm text-gray-900 dark:text-white leading-relaxed"
                                                    x-html="spec.description"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                            <button type="button" @click="closeSpecificationModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="button" @click="confirmSpecificationSelection()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                                Select Specification
                            </button>
                        </div>
                    </div>
                </div>
            </div>
