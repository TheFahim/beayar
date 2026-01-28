<x-layouts.guest>
    <x-slot:title>Register - Beayar ERP</x-slot:title>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create an account</h1>

        <form class="space-y-4" action="{{ route('register') }}" method="POST">
            @csrf
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="John Doe"
                    required
                    value="{{ old('name') }}"
                >
                @error('name')
                    <span class="text-red-500 text-xs"> {{$message}} <span>
                @enderror
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="name@company.com"
                    required
                    value="{{ old('email') }}"
                >
                @error('email')
                    <span class="text-red-500 text-xs"> {{$message}} <span>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                    required
                >
                @error('password')
                    <span class="text-red-500 text-xs"> {{$message}} <span>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                Create account
            </button>

            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                Already have an account? <a href="{{ route('login') }}" class="text-primary-600 dark:text-primary-500 hover:underline">Login here</a>
            </p>
        </form>
    </div>
</x-layouts.guest>
