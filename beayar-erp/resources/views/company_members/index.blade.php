<x-dashboard.layout.default title="Team Members">
    <div class="max-w-7xl mx-auto py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <x-ui.svg.dashboard class="w-4 h-4 mr-2" />
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <x-ui.svg.right-arrow class="w-4 h-4 text-gray-400 mx-1" />
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Team</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Members</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your team members and their roles.</p>
            </div>

            @if(Auth::user()->roleInCompany($company->id) === 'company_admin')
                <button data-modal-target="addMemberModal" data-modal-toggle="addMemberModal" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Member
                </button>
            @endif
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-100 dark:border-green-800" role="alert">
                <div class="flex items-center">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Success!</span> {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if($errors->any())
            <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 border border-red-100 dark:border-red-800" role="alert">
                <div class="flex items-center mb-2">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <span class="font-medium">There were some problems with your input:</span>
                </div>
                <ul class="list-disc list-inside ml-7">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Members Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-100 dark:border-gray-600">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Member</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Employee ID</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Role</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Joined</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($members as $member)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($member->avatar)
                                                <img class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600" src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold border border-blue-200 dark:border-blue-800">
                                                    {{ substr($member->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $member->name }}
                                                @if($member->id === $owner->id)
                                                    <span class="bg-yellow-100 text-yellow-800 text-[10px] font-medium px-2 py-0.5 rounded-full dark:bg-yellow-900 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">Owner</span>
                                                @endif
                                                @if($member->id === Auth::id())
                                                    <span class="bg-green-100 text-green-800 text-[10px] font-medium px-2 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300 border border-green-200 dark:border-green-800">You</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $member->pivot->employee_id ?: 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($member->pivot->role === 'company_admin')
                                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-purple-900 dark:text-purple-300 border border-purple-200 dark:border-purple-800">Admin</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Employee</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($member->pivot->is_active)
                                        <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border border-green-200 dark:border-green-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : $member->pivot->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if(Auth::user()->roleInCompany($company->id) === 'company_admin' && $member->id !== $owner->id)
                                        <div class="flex items-center justify-end gap-3">
                                            <button data-modal-target="editMemberModal{{ $member->id }}" data-modal-toggle="editMemberModal{{ $member->id }}" class="text-blue-600 dark:text-blue-500 hover:text-blue-900 dark:hover:text-blue-400 font-medium transition-colors">Edit</button>

                                            <form action="{{ route('company-members.destroy', $member->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-500 hover:text-red-900 dark:hover:text-red-400 font-medium transition-colors">Remove</button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        @include('company_members.partials.edit-modal', ['member' => $member])
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($members->count() === 0)
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No members found</h3>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Get started by adding a new member to your team.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="addMemberModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm bg-gray-900/50">
        <div class="relative p-4 w-full max-w-lg max-h-full">
            <div class="relative bg-white rounded-xl shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Add New Member
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white transition-colors" data-modal-toggle="addMemberModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <form class="p-4 md:p-5" action="{{ route('company-members.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="avatar">Avatar</label>
                            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="avatar" name="avatar" type="file" accept="image/*">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">SVG, PNG, JPG or GIF (MAX. 2MB).</p>
                        </div>
                        <div class="col-span-2">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
                            <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="John Doe" required="">
                        </div>
                        <div class="col-span-2">
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address</label>
                            <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@company.com" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="employee_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="EMP-001">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role</label>
                            <select id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="employee" selected>Employee</option>
                                <option value="company_admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password (Optional)</label>
                            <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave blank to auto-generate">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-50 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 transition-colors" data-modal-toggle="addMemberModal">
                            Cancel
                        </button>
                        <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors shadow-sm">
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
