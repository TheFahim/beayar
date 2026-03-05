<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout — {{ $plan->name }} | Beayar ERP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy: #0B0F1A;
      --navy-mid: #111827;
      --navy-light: #1a2236;
      --indigo: #4F46E5;
      --indigo-bright: #6366F1;
      --indigo-glow: #818CF8;
      --teal: #14B8A6;
      --slate: #94A3B8;
      --white: #F8FAFC;
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--navy);
      color: var(--white);
      min-height: 100vh;
    }
    h1, h2, h3, h4, h5, .font-display { font-family: 'Syne', sans-serif; }

    .glass {
      background: rgba(255,255,255,0.04);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,0.08);
    }

    .btn-primary {
      background: linear-gradient(135deg, #4F46E5 0%, #6366F1 50%, #818CF8 100%);
      background-size: 200% auto;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(79,70,229,0.5);
    }

    .input-field {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.12);
      transition: all 0.2s ease;
      color: #F8FAFC;
    }
    .input-field:focus {
      outline: none;
      border-color: rgba(99,102,241,0.6);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
      background: rgba(255,255,255,0.08);
    }
    .input-field::placeholder { color: #64748B; }

    .highlight-word {
      background: linear-gradient(135deg, #818CF8, #14B8A6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .blob-1 {
      position: absolute; width: 600px; height: 600px; border-radius: 50%;
      background: radial-gradient(circle, rgba(79,70,229,0.18) 0%, transparent 70%);
      top: -150px; left: -150px; pointer-events: none;
    }
    .blob-2 {
      position: absolute; width: 500px; height: 500px; border-radius: 50%;
      background: radial-gradient(circle, rgba(20,184,166,0.12) 0%, transparent 70%);
      bottom: -100px; right: -100px; pointer-events: none;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
      pointer-events: none; z-index: 0; opacity: 0.4;
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--navy); }
    ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 3px; }
  </style>
</head>
<body class="relative">
  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="min-h-screen flex items-center justify-center px-6 py-16 relative z-10">
    <div class="w-full max-w-lg">
      <!-- Back link -->
      <a href="/" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Homepage
      </a>

      <!-- Plan info card -->
      <div class="glass rounded-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-medium mb-4">
            <span class="w-2 h-2 rounded-full {{ $plan->base_price > 0 ? 'bg-indigo-400' : 'bg-teal-400' }}"></span>
            <span class="{{ $plan->base_price > 0 ? 'text-indigo-300' : 'text-teal-300' }}">{{ $plan->name }} Plan</span>
          </div>
          <h1 class="font-display font-extrabold text-3xl text-white mb-2">
            Create Your <span class="highlight-word">Account</span>
          </h1>
          <p class="text-slate-400 text-sm">
            @if($plan->base_price > 0)
              {{ $plan->name }} plan — <span class="text-white font-semibold">${{ number_format($plan->base_price, 0) }}/mo</span>
            @else
              Free forever — no credit card required
            @endif
          </p>
        </div>

        <!-- Error display -->
        @if(session('error'))
          <div class="mb-6 p-4 rounded-xl text-sm" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #FCA5A5;">
            {{ session('error') }}
          </div>
        @endif

        <form method="POST" action="/checkout/{{ $plan->slug }}">
          @csrf

          <!-- Registration Fields -->
          <div class="space-y-4 mb-6">
            <div>
              <label for="name" class="block text-xs font-medium text-slate-400 mb-1.5">Full Name</label>
              <input type="text" id="name" name="name" value="{{ old('name') }}" required
                class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="John Doe">
              @error('name')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="email" class="block text-xs font-medium text-slate-400 mb-1.5">Email Address</label>
              <input type="email" id="email" name="email" value="{{ old('email') }}" required
                class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="john@company.com">
              @error('email')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password" class="block text-xs font-medium text-slate-400 mb-1.5">Password</label>
              <input type="password" id="password" name="password" required
                class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="Min. 8 characters">
              @error('password')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password_confirmation" class="block text-xs font-medium text-slate-400 mb-1.5">Confirm Password</label>
              <input type="password" id="password_confirmation" name="password_confirmation" required
                class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="Confirm your password">
            </div>
          </div>

          <!-- Fake Payment Fields (only for paid plans) -->
          @if($plan->base_price > 0)
            <div class="mb-6">
              <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full flex items-center justify-center" style="background: rgba(79,70,229,0.2);">
                  <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <span class="text-sm font-medium text-slate-300">Payment Details</span>
                <span class="ml-auto text-xs px-2 py-0.5 rounded-full" style="background:rgba(20,184,166,0.15); color:#5EEAD4;">Mock / Demo</span>
              </div>
              <div class="space-y-4 p-5 rounded-xl" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">
                <div>
                  <label class="block text-xs font-medium text-slate-400 mb-1.5">Card Number</label>
                  <input type="text" class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242">
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Expiry Date</label>
                    <input type="text" class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="12/28" value="12/28">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">CVV</label>
                    <input type="text" class="input-field w-full px-4 py-3 rounded-xl text-sm" placeholder="123" value="123">
                  </div>
                </div>
                <p class="text-xs text-slate-600 italic">This is a demo. No real payment will be processed.</p>
              </div>
            </div>
          @endif

          <!-- Submit -->
          <button type="submit" class="btn-primary w-full text-white font-semibold py-3.5 rounded-xl text-sm">
            @if($plan->base_price > 0)
              Complete Purchase — ${{ number_format($plan->base_price, 0) }}/mo
            @else
              Create Free Account
            @endif
          </button>

          <!-- Footer text -->
          <p class="text-center text-slate-600 text-xs mt-4">
            Already have an account? <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Sign in</a>
          </p>
        </form>
      </div>

      <!-- Security badges -->
      <div class="flex items-center justify-center gap-6 mt-6">
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          SSL Encrypted
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          Secure Checkout
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          Cancel Anytime
        </div>
      </div>
    </div>
  </div>
</body>
</html>
