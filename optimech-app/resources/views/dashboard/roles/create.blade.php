<x-dashboard.layout.default title="Add New Role">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('permissions.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.command class="h-4 w-4 me-2" />
                Create Role
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New Permission">
        <form action="{{ route('roles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center justify-center">
                <div class="space-y-4 w-2/3 p-6 ">
                    <div class="space-y-4">
                        <!-- Input Field -->
                        <div class="col-span-4">
                            <x-ui.form.input id="role-name" name="name" label="Role Name"
                                placeholder="Enter Role Name" class="w-full p-2 text-lg" required />
                        </div>

                        <div>

                            <div class="relative overflow-x-auto sm:rounded-lg py-3 px-2">
                                <table
                                    class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                                        <tr class="dark:text-white">
                                            <th scope="col" class="px-6 py-3">
                                                <span class="flex items-center">
                                                    S/L
                                                </span>
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                <span class="flex items-center">
                                                    Name
                                                </span>
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                <span class="flex items-center">
                                                    Permissions
                                                </span>
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permissions as $key => $item)
                                            <tr
                                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                <td class="px-6 py-4">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <th scope="row"
                                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $key }}
                                                </th>
                                                <td class="px-6 py-4 text-right">
                                                    @foreach ($item as $perm)
                                                        <div class="flex items-center mb-4">
                                                            <input id="permission-{{$perm->id}}" name="permissions[]" type="checkbox" value="{{$perm->name}}"
                                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                            <label for="permission-{{$perm->id}}"
                                                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{$perm->name}}</label>
                                                        </div>
                                                    @endforeach
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>


                        </div>

                        <!-- Save Button -->
                        <button type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>

                    </div>
                </div>
            </div>
        </form>
    </x-ui.card>
</x-dashboard.layout.default>
