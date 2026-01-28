<x-layouts.guest>
    <x-slot:title>
        Reset Password - Beayar ERP
    </x-slot>

    <section>
        <div class="flex items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">

            <!-- Company Information Section -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-center px-8">
                <div class="max-w-lg">
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <h1 class="text-3xl font-sans drop-shadow-md font-bold text-white text-left pl-10 leading-tight">
                                Beayar ERP<br>Management System
                            </h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reset Password Form Section -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center">
                <div class="w-full bg-white rounded-lg shadow-lg dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                    <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                            Reset Password
                        </h1>
                        
                        <hr class="border-t border-gray-500 w-full">

                        <form class="space-y-4 md:space-y-6" action="{{ route('password.store') }}" method="POST">
                            @csrf
                            
                            <!-- Password Reset Token -->
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your Email</label>
                                <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@company.com" required value="{{ old('email', $request->email) }}">
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

                            <div>
                                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
                                @error('password_confirmation')
                                    <span class="text-red-500 text-sm"> {{$message}} <span>
                                @enderror
                            </div>

                            <button type="submit" class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>