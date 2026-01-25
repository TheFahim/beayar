<x-site.layout title="Daniox - services">
    <section class="space-y-10 mx-32 my-5">

        <div class="max-w-screen-xl mx-auto">
            <h1 class="mb-4 text-3xl font-extrabold text-gray-300 dark:text-white md:text-5xl lg:text-6xl"><span
                    class="text-transparent bg-clip-text bg-gradient-to-r to-emerald-300 from-sky-200">Better
                    Data</span>
                Scalable AI.</h1>
            <p class="text-lg font-normal text-white lg:text-xl dark:text-gray-400">Here at Daniox we focus on markets
                where
                technology, innovation, and capital can unlock long-term value and drive economic growth.</p>
        </div>

        <div class="max-w-screen-xl mx-auto space-y-5">
            @foreach ($technologies as $technology)
                <div class="bg-white scroll-smooth dark:bg-gray-900 rounded-lg shadow-lg">
                    <div
                        class="space-y-5 gap-16 py-4 px-4 mx-auto max-w-screen-xl lg:grid lg:grid-cols-2 lg:py-16 lg:px-6">
                        {{-- Text Column --}}
                        <div
                            class="font-light text-gray-500 sm:text-lg dark:text-gray-400 {{ $loop->even ? 'lg:order-2' : 'lg:order-1' }}">
                            <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">
                                {{ $technology->name }}
                            </h2>
                            <div class="mb-4">
                                {!! $technology->description !!}
                            </div>
                        </div>

                        {{-- Image Column --}}
                        <div class="gap-4 {{ $loop->even ? 'lg:order-1' : 'lg:order-2' }}">
                            <img class="rounded-lg w-full" src="{{ asset($technology->cover_image) }}"
                                alt="cover image">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('showContact', () => ({
                show: false,
                toggle() {
                    this.show = !this.show
                }
            }))
        })
    </script>
</x-site.layout>
