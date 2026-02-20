<x-dashboard.layout.default title="Edit Company">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.user-companies.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M19.728 10.686c-2.38 2.256-6.153 3.381-9.875 3.381-3.722 0-7.4-1.126-9.571-3.371L0 10.437V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7.6l-.272.286Z"/>
                </svg>
                My Companies
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Edit</span>
            </div>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto max-w-3xl">
        <div class="p-6 border-b dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                Edit Workspace
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Update your company details and settings.
            </p>
        </div>

        <form action="{{ route('tenant.user-companies.update', $company->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid gap-6 mb-6 md:grid-cols-2">
                <div class="col-span-2">
                     <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="logo">Company Logo</label>
                     <div class="flex items-center gap-4">
                        <div class="shrink-0">
                            <img class="h-16 w-16 object-cover rounded-full border border-gray-200 dark:border-gray-700" src="{{ $company->logo ? asset('storage/'.$company->logo) : asset('assets/images/app-logo.jpeg') }}" alt="Current profile photo" id="logo-preview">
                        </div>
                        <label class="block">
                            <span class="sr-only">Choose profile photo</span>
                            <input type="file" name="logo" id="logo" onchange="previewImage(this)" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-gray-700 dark:file:text-gray-300
                            "/>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 2MB).</p>
                </div>

                <div class="col-span-2">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="e.g. Acme Corp" required>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="info@company.com">
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="+1 (555) 000-0000">
                </div>

                <div class="col-span-2">
                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                    <textarea id="address" name="address" rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="123 Business St, City, Country">{{ old('address', $company->address) }}</textarea>            
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t dark:border-gray-700 pt-4">
                <a href="{{ route('tenant.user-companies.index') }}" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                    Cancel
                </a>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Update Workspace
                </button>
            </div>
        </form>
    </x-ui.card>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('logo-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-dashboard.layout.default>