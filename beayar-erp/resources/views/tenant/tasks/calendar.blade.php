<x-dashboard.layout.default title="Task Calendar">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Task Calendar</h1>
            <button @click="$dispatch('open-task-modal', { date: new Date().toISOString().split('T')[0] })"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition shadow-md flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                New Task
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-4 border border-gray-100 dark:border-gray-700">
            <div id="calendar" class="min-h-[700px]"></div>
        </div>
    </div>

    <!-- Task Modal (Alpine.js) -->
    <div x-data="taskModal()" 
         @open-task-modal.window="openModal($event.detail)"
         x-show="isOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity" 
                 aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-700" 
                 role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                
                <form @submit.prevent="saveTask">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-headline" x-text="editMode ? 'Edit Task' : 'Create New Task'"></h3>
                        <button type="button" @click="isOpen = false" class="text-gray-400 hover:text-gray-500 transition">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                            <input type="text" x-model="form.title" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea x-model="form.description" rows="3"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input type="datetime-local" x-model="form.start_date" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input type="datetime-local" x-model="form.end_date" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assign To</label>
                                <select x-model="form.assigned_to" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select x-model="form.status" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <button x-show="editMode" type="button" @click="deleteTask"
                                class="text-red-600 hover:text-red-700 font-medium text-sm transition flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="isOpen = false"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-md transition flex items-center gap-2"
                                :disabled="loading">
                                <template x-if="loading">
                                    <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="editMode ? 'Update Task' : 'Save Task'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- FullCalendar CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                editable: true,
                selectable: true,
                events: "{{ route('tenant.tasks.data') }}",
                
                select: function(info) {
                    window.dispatchEvent(new CustomEvent('open-task-modal', { 
                        detail: { 
                            date: info.startStr,
                            allDay: info.allDay 
                        } 
                    }));
                },

                eventClick: function(info) {
                    window.dispatchEvent(new CustomEvent('open-task-modal', { 
                        detail: { 
                            task: {
                                id: info.event.id,
                                title: info.event.title,
                                description: info.event.extendedProps.description,
                                start_date: info.event.start.toISOString().slice(0, 16),
                                end_date: info.event.end ? info.event.end.toISOString().slice(0, 16) : info.event.start.toISOString().slice(0, 16),
                                assigned_to: info.event.extendedProps.assigned_to,
                                status: info.event.extendedProps.status
                            }
                        } 
                    }));
                },

                eventDrop: function(info) {
                    updateTaskDates(info.event);
                },

                eventResize: function(info) {
                    updateTaskDates(info.event);
                }
            });
            calendar.render();

            // Function to update task dates after drag/drop or resize
            function updateTaskDates(event) {
                const data = {
                    start_date: event.start.toISOString(),
                    end_date: event.end ? event.end.toISOString() : event.start.toISOString()
                };

                fetch(`/tasks/${event.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating task: ' + data.message);
                        calendar.refetchEvents();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    calendar.refetchEvents();
                });
            }

            window.calendar = calendar;
        });

        function taskModal() {
            return {
                isOpen: false,
                editMode: false,
                loading: false,
                form: {
                    id: null,
                    title: '',
                    description: '',
                    start_date: '',
                    end_date: '',
                    assigned_to: '',
                    status: 'pending'
                },

                openModal(detail) {
                    this.resetForm();
                    if (detail.task) {
                        this.editMode = true;
                        this.form = { ...detail.task };
                    } else if (detail.date) {
                        this.editMode = false;
                        // Default start time to 09:00 and end time to 10:00 on the selected date
                        this.form.start_date = `${detail.date}T09:00`;
                        this.form.end_date = `${detail.date}T10:00`;
                    }
                    this.isOpen = true;
                },

                resetForm() {
                    this.form = {
                        id: null,
                        title: '',
                        description: '',
                        start_date: '',
                        end_date: '',
                        assigned_to: '',
                        status: 'pending'
                    };
                },

                saveTask() {
                    this.loading = true;
                    const url = this.editMode ? `/tasks/${this.form.id}` : '/tasks';
                    const method = this.editMode ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.isOpen = false;
                            window.calendar.refetchEvents();
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong'));
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error('Error:', error);
                        alert('Error saving task');
                    });
                },

                deleteTask() {
                    if (!confirm('Are you sure you want to delete this task?')) return;

                    this.loading = true;
                    fetch(`/tasks/${this.form.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            this.isOpen = false;
                            window.calendar.refetchEvents();
                        } else {
                            alert('Error deleting task');
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error('Error:', error);
                        alert('Error deleting task');
                    });
                }
            };
        }
    </script>

    <style>
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dark .fc-daygrid-dot-event:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .dark .fc-col-header-cell-cushion, 
        .dark .fc-daygrid-day-number,
        .dark .fc-daygrid-day-top {
            color: #d1d5db;
        }
        .dark .fc-toolbar-title {
            color: #f3f4f6;
        }
        .dark .fc-button-primary {
            background-color: #374151;
            border-color: #4b5563;
        }
        .dark .fc-button-primary:hover {
            background-color: #4b5563;
        }
        .dark .fc-button-primary:disabled {
            background-color: #1f2937;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
    </style>
    @endpush
</x-dashboard.layout.default>
