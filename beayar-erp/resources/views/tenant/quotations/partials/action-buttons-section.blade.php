<x-ui.card>
    <div class="flex gap-3 justify-end mx-5">
        <a href="{{ route('tenant.quotations.index') }}"
            class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
            Cancel
        </a>

        <!-- Save As Dropdown (teleported to body to avoid clipping) -->
        <div class="relative"
             x-data="{
                 menuWidth: 192,
                 margin: 8,
                 top: 0,
                 left: 0,
                 menuHeight: 0,
                 measureMenu() {
                     const el = this.$refs.saveMenu;
                     if (el) this.menuHeight = el.offsetHeight;
                 },
                 computePositions() {
                     const rect = this.$refs.saveBtn.getBoundingClientRect();
                     const vw = window.innerWidth, vh = window.innerHeight;
                     let left = rect.right - this.menuWidth;
                     left = Math.min(left, vw - this.menuWidth - this.margin);
                     left = Math.max(left, this.margin);
                     let top = rect.bottom + this.margin;
                     const overflowBottom = top + this.menuHeight > vh - this.margin;
                     if (overflowBottom) {
                         top = Math.max(rect.top - this.menuHeight - this.margin, this.margin);
                     }
                     this.left = left;
                     this.top = top;
                 },
                 updatePosition() {
                     this.measureMenu();
                     this.computePositions();
                 }
             }"
             @keydown.escape.window="showSaveDropdown = false"
             @resize.window="if (showSaveDropdown) updatePosition()"
             @scroll.window="if (showSaveDropdown) updatePosition()"
        >
            <button type="button"
                x-ref="saveBtn"
                @click="showSaveDropdown = !showSaveDropdown; if (showSaveDropdown) $nextTick(() => updatePosition())"
                class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                :disabled="isSubmitting">
                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span x-text="isSubmitting ? 'Saving...' : 'Save As'"></span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Teleported Dropdown Menu -->
            <template x-teleport="body">
                <div x-show="showSaveDropdown" x-ref="saveMenu" @click.away="showSaveDropdown = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="fixed z-50 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800 dark:ring-gray-700"
                     :style="`top: ${top}px; left: ${left}px; width: ${menuWidth}px; max-height: calc(100vh - ${margin * 2}px); overflow-y: auto;`">
                    <div class="py-1">
                        <button type="button"
                            @click="saveQuotation('draft'); showSaveDropdown = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 flex items-center"
                            :disabled="isSubmitting">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Save as Draft
                        </button>
                        <button type="button"
                            @click="saveQuotation('quotation'); showSaveDropdown = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 flex items-center"
                            :disabled="isSubmitting">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Save as Quotation
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-ui.card>
