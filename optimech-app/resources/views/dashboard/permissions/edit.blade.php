<x-dashboard.layout.default title="Edit Area of Publications">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('publication-areas.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Publication Areas
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

<x-ui.card heading="Edit the Area of Publications">
    <form action="{{ route('publication-areas.update',$publicationArea->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="flex items-center justify-center">
            <div class="space-y-4 w-2/3 p-6 ">
                <div class="grid grid-cols-5 gap-4 items-center">
                    <!-- Input Field -->
                    <div class="col-span-4">
                        <x-ui.form.input id="pa-name" name="name" label="Publication Area Name"
                            placeholder="Enter Publication Area Name" class="w-full p-2 text-lg" value="{{$publicationArea->name}}" required />
                    </div>

                    <!-- Save Button -->
                    <button type="submit"
                        class=" py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mt-6 save-button">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-ui.card>


</x-dashboard.layout.default>
