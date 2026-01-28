<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Beayar ERP' }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <main class="w-full max-w-md mx-auto px-4 py-12">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-6">
        <p class="text-center text-xs text-gray-500 dark:text-gray-400">© {{ date('Y') }} Beayar ERP</p>
    </footer>
</body>

</html>
