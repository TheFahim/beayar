<x-dashboard.layout.default title="Edit Workspace">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenant.user-companies.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Workspaces
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-900 md:ml-2 dark:text-white">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Workspace</h1>
                <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">Update your company details and settings.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <form action="{{ route('tenant.user-companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="p-8 space-y-8">
                        
                        <!-- Logo Section -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-4">Workspace Logo</label>
                            <div class="flex items-center gap-6">
                                <div class="shrink-0 relative group">
                                    <div class="h-24 w-24 rounded-2xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                        <img id="logo-preview" class="h-full w-full object-cover" src="{{ $company->logo ? asset('storage/'.$company->logo) : asset('assets/images/app-logo.jpeg') }}" alt="Logo preview">
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl cursor-pointer pointer-events-none">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-6 py-8 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors relative">
                                        <div class="text-center">
                                            <svg class="mx-auto h-10 w-10 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400 justify-center">
                                                <label for="logo" class="relative cursor-pointer rounded-md bg-white dark:bg-gray-800 font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500">
                                                    <span>Upload a file</span>
                                                    <input id="logo" name="logo" type="file" class="sr-only" onchange="previewImage(this)" accept="image/*">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3" 
                                    placeholder="e.g. Acme Corp">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3" 
                                        placeholder="info@company.com">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}" 
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3" 
                                        placeholder="+1 (555) 000-0000">
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Address</label>
                                <textarea id="address" name="address" rows="3" 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3" 
                                    placeholder="123 Business St, City, Country">{{ old('address', $company->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 px-8 py-5 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('tenant.user-companies.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                            Update Workspace
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
