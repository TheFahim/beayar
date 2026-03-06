<x-layouts.guest>
    <x-slot:title>
        Verify Email - Beayar ERP
    </x-slot>

    <div class="space-y-6 text-center">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Verify Your Email Address</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Before proceeding, please check your email for a verification link.
            If you did not receive the email, click the button below to request another.
        </p>

        @if (session('status') == 'Verification link sent!')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                Click here to request another
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 hover:underline">
                Log Out
            </button>
        </form>
    </div>
</x-layouts.guest>
