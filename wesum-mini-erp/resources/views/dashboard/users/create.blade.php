<x-dashboard.layout.default title="Add New User">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Users
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New User">

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mx-5 space-y-2">
                <div class="grid grid-cols-2 space-y-3">
                    <div class="px-2 pt-3">
                        <x-ui.form.input id="name" name="name" label="Full Name"
                            placeholder="Enter Full Name of The User" class="w-full p-2 text-lg" required />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="ID" name="username" label="User ID (No Space)" placeholder="Enter User ID"
                            class="w-full p-2 text-lg" required />
                    </div>


                    <div class="px-2">
                        <x-ui.form.input id="password" name="password" label="Password (Min:8)" placeholder="Set a Password for User"
                            class="w-full p-2 text-lg" required />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="designation" name="designation" label="Designation" placeholder="Enter Use Designation"
                            class="w-full p-2 text-lg" />
                    </div>

                    <div class="px-2">
                        <x-ui.form.input id="phone" name="phone" label="Phone" placeholder="User Phone Number"
                            class="w-full p-2 text-lg"/>
                    </div>

                    <div class="px-2" style="margin-top: auto">
                        <x-ui.form.simple-select id="role" name="role"
                            label="User Role" required>

                                <option value="user">User</option>
                                <option value="admin">Admin</option>

                        </x-ui.form.simple-select>
                    </div>

                    <div class="px-2">
                        <div>
                            <h1 class="text-xl font-bold dark:text-white">Upload User Photo</h1>
                        </div>
                        <x-ui.form.image-upload name="photo" />
                    </div>

                </div>
                <div>

                </div>
            </div>
            <div class="my-10 mx-5">
                <button type="submit"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button mx-2">
                    Save
                </button>
            </div>
        </form>

    </x-ui.card>

</x-dashboard.layout.default>
