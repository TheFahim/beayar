<x-dashboard.layout.default title="Edit User">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Users
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Edit User">

        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="mx-5 space-y-2">
                <div class="grid grid-cols-2 space-y-3">
                    <div class="px-2 pt-3">
                        <x-ui.form.input id="name" name="name" label="Full Name"
                            placeholder="Enter Full Name of The User" class="w-full p-2 text-lg" value="{{ $user->name }}" required />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="username" name="username" label="User ID (No Space)" placeholder="Enter User ID"
                            class="w-full p-2 text-lg" value="{{ $user->username }}" required />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="password" name="password" label="Password (Min:8)" placeholder="Update User Password"
                            class="w-full p-2 text-lg" />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="designation" name="designation" label="Designation" placeholder="Enter Use Designation"
                            class="w-full p-2 text-lg" value="{{ $user->designation }}" />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="phone" name="phone" label="Phone" placeholder="User Phone Number"
                            class="w-full p-2 text-lg" value="{{ $user->phone }}"/>
                    </div>



                    <div class="px-2" >
                        <x-ui.form.simple-select id="role" name="role"
                            label="User Role" required>

                                <option value="user" {{ $userRole == 'user' ? 'selected' : ''  }}>User</option>
                                <option value="admin" {{ $userRole == 'admin' ? 'selected' : ''  }}>Admin</option>

                        </x-ui.form.simple-select>
                    </div>

                    <div class="px-2">
                        <div>
                            <h1 class="text-xl font-bold dark:text-white">Upload User Photo</h1>
                        </div>
                        <x-ui.form.image-upload name="photo" oldData="{{ $user->photo ? asset($user->photo) : '' }}" />
                    </div>

                </div>
                <div>

                </div>
            </div>
            <div class="mx-5 grid grid-cols-7 gap-2">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
                @if ($user->is_active == 1)
                <button id="disable" type="button" data-url="{{ route('users.disable',$user->id) }}"
                    class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800">Disable</button>
                @else
                <button id="disable" type="button" data-url="{{ route('users.disable',$user->id) }}"
                    class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">Enable</button>
                @endif

            </div>
        </form>

    </x-ui.card>

</x-dashboard.layout.default>
