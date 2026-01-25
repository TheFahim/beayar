<x-dashboard.layout.default title="Products">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Products
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">

        <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('products.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>
        </div>

        <hr class="border-t border-gray-300 dark:border-gray-600 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="data-table-simple" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-3 py-3 w-16">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-4 py-3 w-20">
                            Image
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Product Name
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Specifications
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>

                        <th scope="col" class="px-3 py-3 w-32">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 hover:shadow-lg hover:scale-[1.01]">
                            <td class="px-3 py-4 font-medium text-gray-900 dark:text-white w-16">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-4 w-20">
                                <div class="relative w-16 h-16 rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300 group">
                                    @if($product->image)
                                        <img src="{{ asset($product->image->path) }}"
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                             loading="lazy">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span class="truncate max-w-xs" title="{{ $product->name }}">{{ $product->name }}</span>
                                </div>
                            </th>
                            <td class="px-6 py-4">
                                <div class="max-w-sm max-h-32 overflow-y-auto">
                                    @if($product->specifications->count() > 0)
                                        <div class="space-y-2 pr-2">
                                            @foreach($product->specifications as $spec)
                                                <div class="flex items-start gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <svg class="w-4 h-4 text-blue-500 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{!! $spec->description !!}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 italic text-sm">No specifications available</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-4 text-right w-32">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- <a href="{{ route('products.show', $product) }}"
                                        class="group relative px-3 py-2 text-blue-600 hover:text-white font-medium text-sm rounded-lg overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-105 active:scale-95"
                                        title="View Product">
                                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-blue-500 to-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                        <span class="relative flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </span>
                                    </a> --}}
                                    <a href="{{ route('products.edit', $product) }}"
                                        class="group relative px-2 py-1.5 text-green-600 hover:text-white font-medium text-xs rounded-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-105 active:scale-95"
                                        title="Edit Product">
                                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-green-500 to-green-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                        <span class="relative flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </span>
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" id="delete-form-{{ $product->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" form="delete-form-{{ $product->id }}"
                                            class="group relative px-2 py-1.5 text-red-600 hover:text-white font-medium text-xs rounded-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-105 active:scale-95 delete-button"
                                            title="Delete Product">
                                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-red-500 to-red-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                            <span class="relative flex items-center gap-1">
                                                <svg class="w-3 h-3 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">No Products Found</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first product.</p>
                                        <a href="{{ route('products.create') }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Create Product
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>



    </x-ui.card>

</x-dashboard.layout.default>
