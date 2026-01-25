<!-- Fixed Revision History Panel -->
        <div id="revisionHistoryPanel"
            class="fixed top-0 right-0 h-full w-96 bg-white dark:bg-gray-800 shadow-2xl border-l border-gray-200 dark:border-gray-700 transform translate-x-full transition-transform duration-300 ease-in-out z-40 overflow-hidden">

            <!-- Panel Header -->
            <div
                class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-blue-900/20">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <x-ui.svg.clock class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revision History</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $revisions ? $revisions->count() : 0 }}
                            revision{{ $revisions && $revisions->count() !== 1 ? 's' : '' }} available</p>
                    </div>
                </div>
                <button id="closeRevisionPanel"
                    class="px-2 hover:bg-white/50 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 group"
                    title="Close Panel">
                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-red-500 transition-colors"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            @if ((isset($hasAnyChallan) && $hasAnyChallan) || (isset($hasAnyBill) && $hasAnyBill))
                <div class="px-4 py-2 bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 border-b border-yellow-200 dark:border-yellow-800">
                    Activation locked due to existing {{ (isset($hasAnyBill) && $hasAnyBill) ? 'bill' : 'challan' }} for this quotation.
                </div>
            @endif

            <!-- Panel Content -->
            <div class="h-full overflow-y-auto pb-20">
                <div class="mt-10"></div>
                @if ($revisions && $revisions->count() > 0)
                    <div class="p-4 space-y-3">
                        @foreach ($revisions as $index => $revision)
                            <div
                                class="revision-card bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-all duration-200 {{ $loadRevision && $loadRevision->id === $revision->id ? 'ring-2 ring-blue-500 dark:ring-blue-400 bg-blue-50/50 dark:bg-blue-900/20' : '' }}">

                                <!-- Card Header -->
                                <div class="p-4 cursor-pointer group" onclick="toggleRevisionCard({{ $index }})">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center {{ $loadRevision && $loadRevision->id === $revision->id ? 'ring-2 ring-blue-400' : '' }}">
                                                    <span
                                                        class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                        {{ $revision->revision_no }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    @if ($revision->is_active)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 animate-pulse">
                                                            <span
                                                                class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1 animate-ping"></span>
                                                            Active
                                                        </span>
                                                    @endif
                                                    @if ($loadRevision && $loadRevision->id === $revision->id)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                            <span
                                                                class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1"></span>
                                                            Current
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $revision->created_at->format('M j, Y g:i A') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <div class="text-right">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $revision->type == 'normal' ? 'BDT' : $revision->currency }}
                                                    {{ number_format($revision->total, 0) }}
                                                </p>
                                            </div>
                                            <!-- Enhanced chevron with rotation indicator -->
                                            <div
                                                class="p-1 rounded-full group-hover:bg-gray-200 dark:group-hover:bg-gray-600 transition-colors">
                                                <svg id="revisionCardChevron{{ $index }}"
                                                    class="h-4 w-4 text-gray-400 transform transition-transform duration-300 group-hover:text-blue-500"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expandable Details with enhanced visual feedback -->
                                <div id="revisionCardDetails{{ $index }}"
                                    class="hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <div class="p-4 space-y-3">
                                        <!-- Detailed Information -->
                                        <div class="grid grid-cols-1 gap-3">
                                            <div
                                                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                @if ($revision->createdBy)
                                                    <div class="p-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                        <x-ui.svg.users class="h-4 w-4" />
                                                    </div>
                                                    <span>Created by {{ $revision->createdBy->name }}</span>
                                                @endif
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $revision->saved_as === 'quotation' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300' }}">
                                                    {{ ucfirst($revision->saved_as) }}
                                                </span>
                                            </div>


                                        </div>

                                        <!-- Action Button with enhanced styling -->
                                        @if ($loadRevision && $loadRevision->id !== $revision->id)
                                            <div class="pt-2">
                                                <a href="{{ route('quotations.edit', ['quotation' => $quotation->id, 'revision_id' => $revision->id]) }}"
                                                    class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 rounded-lg transition-all duration-200 hover:shadow-lg transform hover:scale-105"
                                                    onclick="event.stopPropagation()">
                                                    <x-ui.svg.eye class="h-4 w-4 mr-2" />
                                                    View This Revision
                                                </a>
                                            </div>
                                        @endif

                                        @php
                                            $isCurrent = $loadRevision && $loadRevision->id === $revision->id;
                                            $isActive = $revision->is_active;
                                            $hasBill = isset($hasAnyBill) && $hasAnyBill;
                                            $canDelete = !$isActive && !$isCurrent && !$hasBill;
                                        @endphp
                                        <div class="pt-2">
                                            <form method="POST"
                                                action="{{ route('quotations.revisions.destroy', ['quotation' => $quotation->id, 'revision' => $revision->id]) }}"
                                                onsubmit="event.stopPropagation()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="delete-button inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-lg transition-all duration-200 {{ $canDelete ? 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 dark:from-red-600 dark:to-red-700 dark:hover:from-red-700 dark:hover:to-red-800 hover:shadow-lg transform hover:scale-105' : 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-60' }}"
                                                    {{ $canDelete ? '' : 'disabled' }}
                                                    title="{{ $canDelete ? 'Delete This Revision' : ($hasBill ? 'Cannot delete: Quotation has bills' : ($isActive ? 'Cannot delete active revision' : 'Cannot delete the current revision')) }}">
                                                    <x-ui.svg.close class="h-4 w-4 mr-2" />
                                                    Delete This Revision
                                                </button>
                                            </form>
                                            @if (!$canDelete)
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    @if ($hasBill)
                                                        Cannot delete revision because quotation has associated bills.
                                                    @elseif ($isActive)
                                                        Cannot delete an active revision.
                                                    @elseif ($isCurrent)
                                                        Cannot delete the currently selected revision.
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center p-6">
                        <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                            <x-ui.svg.file class="h-12 w-12 text-gray-400" />
                        </div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Revisions Found</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This quotation doesn't have any revisions
                            yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Overlay for mobile -->
        <div id="revisionHistoryOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>
