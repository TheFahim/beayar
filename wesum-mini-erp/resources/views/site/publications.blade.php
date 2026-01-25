<x-site.layout title="Daniox - publications">
    <div x-data="publicationData" class="my-5 space-y-5">
        <section class="flex items-center">
            <div class="max-w-screen-2xl px-4 mx-auto lg:px-12 w-full">
                <!-- Start coding here -->
                <div class="relative bg-white shadow-md dark:bg-gray-800 sm:rounded-lg">
                    <div
                        class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center space-x-2">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" id="simple-search" x-model="search" @keyup="searchData"
                                        class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        placeholder="Enter Publication Name">
                                </div>
                                <div
                                    class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
                                    {{-- <button type="button"
                                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-50 dark:text-gray-400"
                                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Search
                                    </button> --}}
                                    <div class="flex items-center w-full space-x-3 md:w-auto">

                                        <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                                            class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg md:w-auto focus:outline-none hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                                            type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                                class="w-4 h-4 mr-2 text-gray-400" viewbox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Filter
                                            <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path clip-rule="evenodd" fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div id="filterDropdown"
                                            class="z-10 hidden w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                                            <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">Authors
                                            </h6>
                                            <ul class="space-y-2 text-sm max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600"
                                                aria-labelledby="dropdownDefault">
                                                <template x-for="author in authors" :key="author">
                                                    <li class="flex items-center">
                                                        <input type="checkbox" :id="author"
                                                            :value="author" @change="toggleAuthor(author)"
                                                            class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500" />
                                                        <label :for="author"
                                                            class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                                                            x-text="author"></label>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>



                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>


        <section class="flex items-center">
            <div class="max-w-screen-2xl px-4 mx-auto lg:px-12 w-full h-full">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <template x-for="item in items" :key="item.id">
                        <div class="flex flex-col h-full"> <!-- Added flex container -->
                            <div
                                class="flex-1 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 h-full flex flex-col">
                                <!-- Added flex-col -->
                                <div class="h-56 w-full mb-4">
                                    <a href="#">
                                        <img class="mx-auto h-full object-contain dark:hidden"
                                            :src="item.cover_image ? item.cover_image : 'assets/images/image-1@2x.jpg'"
                                            alt="pub-cover" />
                                    </a>
                                </div>

                                <div class="flex flex-col flex-1"> <!-- Content container with flex -->
                                    <a :href="item.link"
                                        target="_blank"
                                        class="text-lg font-semibold leading-tight text-gray-900 hover:underline dark:text-white mb-2"
                                        x-text="item.title">

                                    </a>

                                    <ul class="mt-2 flex items-center gap-4">
                                        <li class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M9.143 4H4.857A.857.857 0 0 0 4 4.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 10 9.143V4.857A.857.857 0 0 0 9.143 4Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286A.857.857 0 0 0 20 9.143V4.857A.857.857 0 0 0 19.143 4Zm-10 10H4.857a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286A.857.857 0 0 0 9.143 14Zm10 0h-4.286a.857.857 0 0 0-.857.857v4.286c0 .473.384.857.857.857h4.286a.857.857 0 0 0 .857-.857v-4.286a.857.857 0 0 0-.857-.857Z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                                x-text="item.publication_area.name">
                                            </p>
                                        </li>
                                    </ul>

                                    <h2 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Authors:</h2>
                                    <ul
                                        class="mt-2 space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400 flex-1">
                                        <template x-for="author in item.authors">
                                            <li x-text="author.name"></li>
                                        </template>

                                    </ul>

                                    <!-- This section will always stay at the bottom -->
                                    <div class="mt-4 flex items-center justify-between gap-4">
                                        <p class="text-2xl font-extrabold text-gray-900 dark:text-white"
                                            x-text='item.year'>

                                        </p>
                                        <a :href="item.link" target="_blank"
                                            class="inline-flex items-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                                            <svg class="w-5 h-5 text-gray-50 dark:text-white mr-2" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6.03v13m0-13c-2.819-.831-4.715-1.076-8.029-1.023A.99.99 0 0 0 3 6v11c0 .563.466 1.014 1.03 1.007 3.122-.043 5.018.212 7.97 1.023m0-13c2.819-.831 4.715-1.076 8.029-1.023A.99.99 0 0 1 21 6v11c0 .563-.466 1.014-1.03 1.007-3.122-.043-5.018.212-7.97 1.023" />
                                            </svg>
                                            Read Publication
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="hasMorePages">
                    <div class="w-full text-center my-5">
                        <button @click="showMore" type="button"
                            class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">Show
                            more</button>
                    </div>
                </template>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('publicationData', () => ({
                items: [],
                currentPage: 1,
                hasMorePages: false,
                search: '',
                authors: [],
                selectedAuthors: [],
                searchUrl: '/publications/data?page=1',

                init() {
                    this.loadMore();
                    this.fetchAuthors();
                },


                async loadMore() {

                    // console.log(this.selectedAuthors);

                    const url =
                        `${this.searchUrl}&authors=${JSON.stringify(this.selectedAuthors)}`;



                    const response = await fetch(url);

                    if (response.ok) {
                        const publication = await response.json();

                        console.log(publication);


                        if (publication.next_page_url) {
                            this.hasMorePages = true;
                        } else {
                            this.hasMorePages = false;
                        }

                        if (publication.data.length > 0) {
                            this.items = [...this.items, ...publication.data];
                        }

                    }
                },

                async fetchAuthors() {
                    const response = await fetch('/publications/authors');
                    if (response.ok) {
                        this.authors = await response.json();
                    }
                },

                showMore() {
                    this.currentPage++;
                    this.searchUrl =
                        `/publications/data?page=${this.currentPage}&search=${encodeURIComponent(this.search)}`;
                    this.loadMore();
                },

                searchData() {
                    this.currentPage = 1; // Reset page count
                    this.items = []; // Clear current list
                    this.searchUrl =
                        `/publications/data?page=1&search=${encodeURIComponent(this.search)}`;
                    this.loadMore();
                },

                toggleAuthor(author) {
                    if (this.selectedAuthors.includes(author)) {
                        this.selectedAuthors = this.selectedAuthors.filter(a => a !== author);
                    } else {
                        this.selectedAuthors.push(author);
                    }
                    this.searchData();
                }


            }));
        });
    </script>

</x-site.layout>
