<x-layouts.guest>
    <x-slot:title>
        Register - Beayar ERP
    </x-slot>

    <section>
        <div class="flex items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">

            <!-- Company Information Section -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-center px-8">
                <div class="max-w-lg">
                    <!-- Company Logo and System Title -->
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <!-- System Title -->
                            <h1 class="text-3xl font-sans drop-shadow-md font-bold text-white text-left pl-10 leading-tight">
                                Beayar ERP<br>Management System
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Register Form Section -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center">
                <div class="w-full bg-white rounded-lg shadow-lg dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            Create an account
                        </h1>
                        <hr class="border-t border-gray-500 w-full">
                        <form class="space-y-4 md:space-y-6" action="{{ route('register') }}" method="POST">
                            @csrf
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Name</label>
                                <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="John Doe" required value="{{ old('name') }}">
                                @error('name')
                                    <span class="text-red-500 text-sm"> {{$message}} <span>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Email</label>
                                <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@company.com" required value="{{ old('email') }}">
                                @error('email')
                                    <span class="text-red-500 text-sm"> {{$message}} <span>
                                @enderror
                            </div>
                            <div>
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                                <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                                @error('password')
                                    <span class="text-red-500 text-sm"> {{$message}} <span>
                                @enderror
                            </div>
                            <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Create account</button>
                            <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                                Already have an account? <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:underline dark:text-primary-500">Login here</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>