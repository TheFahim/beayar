<x-dashboard.layout.default title="Add New Permission">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('permissions.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.lock class="h-3 w-3 me-2" />
                Permissions
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Add New Permission">
        <form action="{{ route('permissions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center justify-center">
                <div class="space-y-4 w-2/3 p-6 ">
                    <div class="space-y-4">
                        <!-- Input Field -->
                        <div class="col-span-4">
                            <x-ui.form.input id="permission-name" name="name" label="Permission Name"
                                placeholder="Enter Permission Name" class="w-full p-2 text-lg" required />
                        </div>

                        <div>

                            <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Actions</h3>
                            <ul
                                class="w-48 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                                <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">
                                    <div class="flex items-center ps-3">
                                        <input id="list-checkbox" type="checkbox" name="action[list]" value="1" checked
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="list-checkbox"
                                            class="w-full py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">List</label>
                                    </div>
                                </li>

                                <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">
                                    <div class="flex items-center ps-3">
                                        <input id="create-checkbox" type="checkbox" name="action[create]" value="1" checked
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="create-checkbox"
                                            class="w-full py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Create</label>
                                    </div>
                                </li>
                                <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">
                                    <div class="flex items-center ps-3">
                                        <input id="edit-checkbox" type="checkbox" name="action[edit]" value="1" checked
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="edit-checkbox"
                                            class="w-full py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Edit
                                        </label>
                                    </div>
                                </li>

                                <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">
                                    <div class="flex items-center ps-3">
                                        <input id="delete-checkbox" type="checkbox" name="action[delete]" value="1" checked
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                        <label for="delete-checkbox"
                                            class="w-full py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Delete</label>
                                    </div>
                                </li>
                            </ul>

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
