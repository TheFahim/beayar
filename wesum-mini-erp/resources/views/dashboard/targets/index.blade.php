<x-dashboard.layout.default title="Expenses">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('targets.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                User Taegets
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>


    <x-ui.card class="mx-auto">
        <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('targets.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>



        </div>
        <hr class="border-t border-gray-300 w-full">

        <div class="relative  sm:rounded-lg py-3 px-2">
            <table id="team-members"
                class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>

                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Year
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>


                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Amount
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                User
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($targets as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                {{ $loop->iteration }}
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->year }}
                            </th>

                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->target_amount }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->user->name }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('targets.edit', $item->id) }}"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </x-ui.card>









</x-dashboard.layout.default>
