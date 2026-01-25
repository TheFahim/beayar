<x-site.layout title="Daniox - blog">

    <main class="pt-8 pb-16 lg:pt-16 lg:pb-24 antialiased">
        <div class="flex justify-between px-4 mx-auto max-w-screen-xl ">
            <article
                class="mx-auto w-full max-w-2xl format format-sm sm:format-base lg:format-lg format-blue dark:format-invert">
                <header class="mb-4 lg:mb-6 not-format">
                    <address class="flex items-center mb-6 not-italic">
                        <div class="inline-flex items-center mr-3 text-sm text-gray-900 dark:text-white">

                            <div>
                                <div
                                    class="text-xl font-bold text-gray-300 dark:text-white">{{$blog->author}}</div>

                                <p class="text-base text-gray-50"><time pubdate
                                        datetime="2022-02-08" title="February 8th, 2022">{{$blog->created_at->format('d-M-Y')}}</time></p>
                            </div>
                        </div>
                    </address>
                    <h1
                        class="mb-4 text-3xl font-extrabold leading-tight text-gray-300 lg:mb-6 lg:text-4xl dark:text-white">
                        {{$blog->title}}
                    </h1>
                </header>
                <div>
                    {!!$blog->body!!}
                </div>
            </div>
        </main>
    </footer>
</x-site.layout>
