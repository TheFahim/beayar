<x-site.layout title="Daniox - news">
    <div class=" py-8">
        <h2 class="text-4xl font-bold text-center mb-8">Related News articles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 px-4 sm:px-8 lg:px-16">
            @forelse ($news as $item)
                <article class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{$item->link}}" target="_blank">
                        <img src="{{$item->cover_image}}"
                            class="w-full h-40 object-cover" alt="Image 1">
                    </a>
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-2">
                            <a href="{{$item->link}}" target="_blank" class="hover:underline">{{$item->title}}</a>
                        </h2>
                        <p class="text-sm text-gray-400 mb-4">
                            {{$item->author}}
                        </p>
                        <a href="{{$item->link}}" target="_blank" class="text-blue-500 hover:underline text-sm font-medium">
                            Read now
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-4">
                    <p class="text-center text-gray-100">No news articles found</p>

                </div>
            @endforelse


        </div>
    </div>

</x-site.layout>
