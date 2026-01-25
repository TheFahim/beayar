<x-dashboard.layout.default title="User role">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                &nbsp; Users
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="roles" />
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Assign {{$user->name}} a Role">
        <form action="{{ route('users.assign', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="flex items-center ">
                <div class="space-y-4 w-2/3 p-6 ">
                    <div class="grid grid-cols-3">
                        <!-- Input Field -->

                        <div>

                            <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Roles</h3>
                            <ul
                                class="w-48 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                                @foreach ($roles as $item)
                                    <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">
                                        <div class="flex items-center ps-3">
                                            <input id="role-{{$item->id}}" type="checkbox" name="roles[]" value="{{$item->name}}"
                                                {{in_array($item->id, $userRoles) ? 'checked' : ''}}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                                            <label for="role-{{$item->id}}"
                                                class="w-full py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{$item->name}}</label>
                                        </div>
                                    </li>
                                @endforeach



                            </ul>

                        </div>


                        <!-- Save Button -->

                    </div>
                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">Save</button>
                </div>
            </div>
        </form>
    </x-ui.card>
</x-dashboard.layout.default>
