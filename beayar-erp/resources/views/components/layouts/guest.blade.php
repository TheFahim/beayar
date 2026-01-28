<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Beayar ERP' }}</title>
    <script src="{{ asset('js/dark-theme.js') }}"></script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    
    <style>
        /* Smooth fade-in animation */
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-gray-900 dark:text-white">
    <div class="min-h-screen flex">
        
        <!-- Left Side: Branding (Hidden on mobile) -->
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-600 to-indigo-900 relative overflow-hidden items-center justify-center">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                </svg>
            </div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

            <!-- Content -->
            <div class="relative z-10 text-center px-12">
                <div class="mb-8 flex justify-center">
                    <!-- Placeholder for Logo if available, otherwise Icon -->
                    <div class="p-4 bg-white/10 rounded-2xl backdrop-blur-sm">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-4 tracking-tight">Beayar ERP</h1>
                <p class="text-blue-100 text-lg leading-relaxed">
                    Streamline your business operations with our comprehensive management solution. Efficient, secure, and scalable.
                </p>
                
                <!-- Feature List (Optional) -->
                <div class="mt-12 grid grid-cols-2 gap-4 text-left">
                    <div class="flex items-center space-x-2 text-blue-100">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Multi-Tenancy</span>
                    </div>
                    <div class="flex items-center space-x-2 text-blue-100">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Finance & Billing</span>
                    </div>
                    <div class="flex items-center space-x-2 text-blue-100">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>CRM Integration</span>
                    </div>
                    <div class="flex items-center space-x-2 text-blue-100">
                        <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Real-time Analytics</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 bg-white dark:bg-gray-900">
            <div class="w-full max-w-md space-y-8 fade-in">
                <!-- Mobile Logo (Visible only on small screens) -->
                <div class="lg:hidden text-center mb-8">
                    <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400">Beayar ERP</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Management System</p>
                </div>

                {{ $slot }}
                
                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
                    <p class="text-xs text-gray-400">
                        &copy; {{ date('Y') }} Beayar ERP. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</body>
</html>