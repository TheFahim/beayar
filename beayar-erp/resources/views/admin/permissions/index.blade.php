<x-admin.layout.default title="Manage Permissions">
    <div class="p-4" x-data="{
        createModalOpen: false,
        editModalOpen: false,
        currentPermission: {},
        openEditModal(perm) {
            this.currentPermission = JSON.parse(JSON.stringify(perm));
            this.editModalOpen = true;
        }
    }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Permissions</h2>
            <button @click="createModalOpen = true" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                + New Permission
            </button>
        </div>

        {{-- Permissions Table --}}
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Guard Name</th>
                        <th scope="col" class="px-6 py-3">Created At</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $permission->id }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $permission->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-800 text-xs font-mono px-2 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">
                                    {{ $permission->guard_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $permission->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 flex gap-3">
                                <button @click="openEditModal({{ $permission->toJson() }})"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                                <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this permission? This may break functionality relying on it.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No permissions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Create Permission Modal --}}
        <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="createModalOpen" @click="createModalOpen = false" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
                
                <div x-show="createModalOpen" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-auto z-10">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create New Permission</h3>
                        <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form action="{{ route('admin.permissions.store') }}" method="POST" class="p-6">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permission Name</label>
                                <input type="text" name="name" id="name" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                                    placeholder="e.g. create_products">
                                <p class="mt-1 text-xs text-gray-500">Use snake_case format (e.g. view_reports)</p>
                            </div>
                            
                            <div>
                                <label for="guard_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guard Name</label>
                                <select name="guard_name" id="guard_name" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                    <option value="web">web (Default)</option>
                                    <option value="admin">admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="createModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-600 dark:hover:bg-blue-700">
                                Create Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit Permission Modal --}}
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="editModalOpen" @click="editModalOpen = false" class="fixed inset-0 bg-gray-900/50 transition-opacity"></div>
                
                <div x-show="editModalOpen" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-auto z-10">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Permission</h3>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form :action="`{{ route('admin.permissions.index') }}/${currentPermission.id}`" method="POST" class="p-6">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permission Name</label>
                                <input type="text" name="name" x-model="currentPermission.name" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Guard Name</label>
                                <select name="guard_name" x-model="currentPermission.guard_name" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                    <option value="web">web</option>
                                    <option value="admin">admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-600 dark:hover:bg-blue-700">
                                Update Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout.default>
