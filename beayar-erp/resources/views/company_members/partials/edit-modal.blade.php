<div id="editMemberModal{{ $member->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm bg-gray-900/50">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-xl shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Edit Member: {{ $member->name }}
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white transition-colors" data-modal-toggle="editMemberModal{{ $member->id }}">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form class="p-4 md:p-5" action="{{ route('company-members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="avatar">Avatar</label>
                        <div class="flex items-center gap-4">
                            @if($member->avatar)
                                <img class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-600" src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}">
                            @else
                                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold border border-blue-200 dark:border-blue-800">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="avatar" name="avatar" type="file" accept="image/*">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">Leave blank to keep current avatar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ $member->name }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ $member->email }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ $member->phone }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="+1234567890">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="employee_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ $member->pivot->employee_id }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="EMP-001">
                    </div>

                    <div class="col-span-2 sm:col-span-1" x-data="{
                        search: '',
                        open: false,
                        selected: {{ json_encode($member->roles->pluck('name')) }},
                        options: [
                            @foreach($roles as $role)
                                { value: '{{ $role->name }}', label: '{{ ucfirst(str_replace('_', ' ', $role->name)) }}' },
                            @endforeach
                        ],
                        get selectedLabels() {
                            if (this.selected.length === 0) return 'Select roles...';
                            return this.selected.map(v => this.options.find(o => o.value === v)?.label).join(', ');
                        },
                        toggle(value) {
                            if (this.selected.includes(value)) {
                                this.selected = this.selected.filter(v => v !== value);
                            } else {
                                this.selected.push(value);
                            }
                        }
                    }">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Roles</label>
                        <div class="relative" @click.away="open = false">
                            <button type="button" @click="open = !open" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-left dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 min-h-[42px] flex items-center justify-between">
                                <span x-text="selectedLabels" class="block truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                                <div class="p-2 sticky top-0 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                    <input x-model="search" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search roles...">
                                </div>
                                <div class="p-1">
                                    <template x-for="option in options.filter(o => o.label.toLowerCase().includes(search.toLowerCase()))" :key="option.value">
                                        <div @click="toggle(option.value)" class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" :value="option.value" :checked="selected.includes(option.value)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 pointer-events-none">
                                            </div>
                                            <div class="ms-2 text-sm">
                                                <label class="font-medium text-gray-900 dark:text-gray-300 select-none" x-text="option.label"></label>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="options.filter(o => o.label.toLowerCase().includes(search.toLowerCase())).length === 0" class="p-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                                        No roles found
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <select name="roles[]" multiple class="sr-only" x-model="selected">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="is_active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                        <select id="is_active" name="is_active" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="1" {{ $member->pivot->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$member->pivot->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="joined_at" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Joined Date</label>
                        <input type="date" name="joined_at" id="joined_at" value="{{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('Y-m-d') : '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New Password (Optional)</label>
                        <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-50 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 transition-colors" data-modal-toggle="editMemberModal{{ $member->id }}">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors shadow-sm">
                        Update Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
