<!DOCTYPE html>
<html lang="en">

<head>
    <script src="{{ asset('js/dark-theme.js') }}"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Beayar ERP' }}</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    <!-- Enhanced color palette + advanced animated background -->
    <style>
        :root{
            --primary-start: #06b6d4; /* cyan-500 */
            --primary-end: #7c3aed;   /* violet-600 */
            --card-glass: rgba(255,255,255,0.06);
            --muted: rgba(15,23,42,0.7);
        }

        /* Multi-layered animated gradient background */
        body {
            min-height: 100vh;
            position: relative;
            background: linear-gradient(135deg, #2a2a4a 0%, #818196 25%, #929cc3 50%, #2f5480 75%, #2a4483 100%);
            overflow: hidden;
            color-scheme: light dark;
        }

        /* Primary animated gradient layer */
        body::before {
            content: "";
            position: absolute;
            inset: -50%;
            background:
                radial-gradient(circle at 20% 30%, rgba(147, 51, 234, 0.25), transparent 25%),
                radial-gradient(circle at 80% 10%, rgba(6, 182, 212, 0.2), transparent 20%),
                radial-gradient(circle at 60% 80%, rgba(99, 102, 241, 0.18), transparent 22%),
                radial-gradient(circle at 10% 90%, rgba(168, 85, 247, 0.15), transparent 18%),
                radial-gradient(circle at 90% 60%, rgba(14, 165, 233, 0.12), transparent 16%);
            filter: blur(80px);
            animation: cosmicDrift 25s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        /* Secondary floating elements */
        body::after {
            content: "";
            position: absolute;
            width: 150vw;
            height: 150vh;
            left: -25%;
            top: -25%;
            background:
                radial-gradient(circle at 15% 25%, rgba(124, 58, 237, 0.08), transparent 30%),
                radial-gradient(circle at 85% 75%, rgba(59, 130, 246, 0.06), transparent 35%),
                radial-gradient(circle at 45% 15%, rgba(168, 85, 247, 0.05), transparent 25%),
                radial-gradient(circle at 75% 45%, rgba(6, 182, 212, 0.04), transparent 28%);
            filter: blur(120px);
            transform: rotate(10deg);
            animation: nebula 35s ease-in-out infinite alternate;
            z-index: 0;
            pointer-events: none;
        }

        /* Floating particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: radial-gradient(circle, rgba(147, 197, 253, 0.8), transparent);
            border-radius: 50%;
            animation: float 20s linear infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 25s; }
        .particle:nth-child(2) { left: 20%; animation-delay: -5s; animation-duration: 30s; }
        .particle:nth-child(3) { left: 30%; animation-delay: -10s; animation-duration: 35s; }
        .particle:nth-child(4) { left: 40%; animation-delay: -15s; animation-duration: 28s; }
        .particle:nth-child(5) { left: 50%; animation-delay: -20s; animation-duration: 32s; }
        .particle:nth-child(6) { left: 60%; animation-delay: -25s; animation-duration: 27s; }
        .particle:nth-child(7) { left: 70%; animation-delay: -30s; animation-duration: 33s; }
        .particle:nth-child(8) { left: 80%; animation-delay: -35s; animation-duration: 26s; }
        .particle:nth-child(9) { left: 90%; animation-delay: -40s; animation-duration: 29s; }

        @keyframes cosmicDrift {
            0% {
                transform: translate3d(-15%, -10%, 0) rotate(0deg) scale(1);
                opacity: 1;
            }
            25% {
                transform: translate3d(5%, -15%, 0) rotate(90deg) scale(1.1);
                opacity: 0.8;
            }
            50% {
                transform: translate3d(15%, 5%, 0) rotate(180deg) scale(0.95);
                opacity: 1;
            }
            75% {
                transform: translate3d(-5%, 15%, 0) rotate(270deg) scale(1.05);
                opacity: 0.9;
            }
            100% {
                transform: translate3d(-15%, -10%, 0) rotate(360deg) scale(1);
                opacity: 1;
            }
        }

        @keyframes nebula {
            0% {
                transform: translateY(0) rotate(10deg) scale(1);
                opacity: 0.6;
            }
            33% {
                transform: translateY(-8%) rotate(5deg) scale(1.1);
                opacity: 0.8;
            }
            66% {
                transform: translateY(4%) rotate(15deg) scale(0.9);
                opacity: 0.7;
            }
            100% {
                transform: translateY(0) rotate(10deg) scale(1);
                opacity: 0.6;
            }
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(-10px) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
                transform: translateY(90vh) translateX(10px) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(10vh) translateX(-10px) scale(1);
            }
            100% {
                transform: translateY(-10vh) translateX(10px) scale(0);
                opacity: 0;
            }
        }

        /* Aurora-like effect for special occasions */
        .aurora {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(147, 51, 234, 0.03),
                rgba(6, 182, 212, 0.02),
                rgba(99, 102, 241, 0.03),
                transparent
            );
            animation: aurora 45s ease-in-out infinite;
            z-index: 2;
            pointer-events: none;
        }

        @keyframes aurora {
            0%, 100% {
                transform: translateX(-100%) skewX(-5deg);
                opacity: 0;
            }
            50% {
                transform: translateX(100%) skewX(5deg);
                opacity: 1;
            }
        }

        /* Make the layout sits above background effects */
        main, .flex, .w-full {
            position: relative;
            z-index: 10;
        }

        /* Glassy card look (works in both light/dark) */
        .w-full.bg-white {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(6px) saturate(120%);
            box-shadow: 0 10px 30px rgba(2,6,23,0.5);
            color: #e6eef8;
        }

        /* Headline color improvement */
        h1.text-xl {
            color: #e6eef8 !important;
        }

        hr.border-t {
            border-color: rgba(255,255,255,0.06) !important;
        }

        /* Inputs: darker, cleaner, better focus ring */
        input, textarea, select {
            background: rgba(255,255,255,0.02) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            color: #f8fafc !important;
            transition: box-shadow .15s ease, border-color .15s ease, transform .06s ease;
        }

        input::placeholder { color: rgba(234, 239, 245, 0.45) !important; }

        input:focus {
            outline: none !important;
            border-color: rgba(124,58,237,0.9) !important;
            box-shadow: 0 6px 20px rgba(124,58,237,0.12);
            transform: translateY(-1px);
        }

        label { color: rgba(226,238,248,0.9) !important; }

        /* Primary button gradient + subtle lift */
        .bg-primary-600 {
            background-image: linear-gradient(90deg, var(--primary-start), var(--primary-end)) !important;
            border: none !important;
            box-shadow: 0 8px 24px rgba(124,58,237,0.18);
        }
        .bg-primary-600:hover, .hover\:bg-primary-700:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(124,58,237,0.2);
        }

        .text-gray-900 { color: #e6eef8 !important; }
        .dark\:text-white { color: #e6eef8 !important; }

        /* Make the card narrower on large screens for elegance */
        @media (min-width: 1024px) {
            .lg\:w-1\/2 { max-width: none; }
        }

        /* Mobile responsiveness for company info */
        @media (max-width: 1023px) {
            .hidden.lg\:flex { display: none !important; }
        }

        /* Reduce motion for users who prefer reduced motion */
        @media (prefers-reduced-motion: reduce) {
            body::before, body::after, .aurora, .particle { animation: none; }
            .bg-primary-600:hover { transform: none; }
        }
    </style>
</head>

<body class="grid place-items-center min-h-screen dark:bg-gray-700 dark:divide-gray-600">
    <!-- Floating particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Aurora effect -->
    <div class="aurora"></div>

    <main class="w-full max-w-6xl mx-auto">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="absolute bottom-0 left-0 right-0 z-20 py-4">
        <div class="text-center">
            <p class="text-sm text-gray-900">© {{ date('Y') }} Beayar ERP all rights reserved</p>
        </div>
    </footer>
</body>

</html>