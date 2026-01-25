<x-dashboard.layout.default title="Users">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                &nbsp; Users
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">

        <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('users.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>
        </div>

        <hr class="border-t border-gray-300 w-full">

        <div class="relative  sm:rounded-lg py-3 px-2">
            <table id="users" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>

                        </th>
                        <th scope="col" class="px-6 py-3">
                            Photo
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Name
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Username
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Designation
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Phone
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Role
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Status
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>


                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="image-placeholder" data-src="{{ asset($item->photo) }}"></div>
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->name }}
                            </th>
                            <td class="px-6 py-4">
                                {{ $item->username }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->designation }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->phone }}
                            </td>


                            <td class="px-6 py-4">
                                <div
                                    class="max-h-[70px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-500 scrollbar-track-gray-300 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-700">

                                    @foreach ($item->roles as $role)
                                        <span
                                            class="space-y-2 bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $role->name }}
                                        </span>
                                        <br>
                                    @endforeach
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if ($item->is_active == 1)
                                    <span
                                        class="space-y-2 bg-green-600 text-white text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-600 dark:text-white">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="space-y-2 bg-red-500 text-white text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-500 dark:textwhite">
                                        Inactive
                                    </span>
                                @endif
                            </td>
        </div>
        </td>

        <td class="px-6 py-4 text-right">
            <a href="{{ route('users.edit', $item->id) }}"
                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
            {{-- <a href="{{ route('users.roles', $item->id) }}"
                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Assign</a> --}}
        </td>
        </tr>
        @endforeach

        </tbody>
        </table>
        </div>
    </x-ui.card>

</x-dashboard.layout.default>
