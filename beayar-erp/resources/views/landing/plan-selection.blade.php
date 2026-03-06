<!DOCTYPE html>
<html lang="en" x-data="{
    billingYearly: false,
    selectedPlan: {{ isset($plan) ? json_encode($plan->slug) : '"pro-plus"' }},
    businessLocation: 'Bangladesh',
    billingPeriod: 'annually',
    @if(isset($plan))
        planData: {{ json_encode($plan->toArray()) }},
        pricing: {
          monthly: {
            subtotal: {{ $plan->base_price }},
            original: {{ $plan->base_price * 1.2 }},
            savings: {{ ($plan->base_price * 1.2) - $plan->base_price }}
          },
          annually: {
            subtotal: {{ $plan->base_price * 12 * 0.8 }},
            original: {{ $plan->base_price * 12 }},
            savings: {{ ($plan->base_price * 12) - ($plan->base_price * 12 * 0.8) }}
          }
        },
    @else
        planData: null,
        pricing: {
          monthly: { subtotal: 2.10, original: 2.52, savings: 0.42 },
          annually: { subtotal: 113.00, original: 141.60, savings: 28.60 }
        },
    @endif
    vatRate: 0.15,
    get currentPricing() {
      return this.pricing[this.billingPeriod];
    },
    get subtotal() {
      return this.currentPricing.subtotal;
    },
    get vatAmount() {
      return this.subtotal * this.vatRate;
    },
    get total() {
      return this.subtotal + this.vatAmount;
    }
  }">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Select Your Plan | Beayar ERP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

    .radio-custom {
      appearance: none;
      width: 20px;
      height: 20px;
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 50%;
      background: transparent;
      position: relative;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .radio-custom:checked {
      border-color: #4F46E5;
      background: #4F46E5;
    }
    .radio-custom:checked::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 6px;
      height: 6px;
      background: white;
      border-radius: 50%;
      transform: translate(-50%, -50%);
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
    <div class="w-full max-w-6xl">
      <!-- Back link -->
      <a href="/" class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Homepage
      </a>

      <div class="grid lg:grid-cols-2 gap-8">
        <!-- Left Panel -->
        <div class="space-y-8">
          <!-- Top Benefits -->
          {{-- <div class="glass rounded-2xl p-6">
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(20,184,166,0.2);">
                  <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-white font-medium">Unlimited support</span>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(20,184,166,0.2);">
                  <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-white font-medium">No annual contract</span>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: rgba(20,184,166,0.2);">
                  <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-white font-medium">Cancel anytime</span>
              </div>
            </div>
          </div> --}}

          <!-- Business Location -->
          <div class="glass rounded-2xl p-6">
            <h3 class="font-display font-semibold text-white text-lg mb-4">Your business location</h3>
            <div class="relative">
              <select x-model="businessLocation" class="input-field w-full px-4 py-3 rounded-xl text-sm appearance-none cursor-pointer">
                <option value="Bangladesh">Bangladesh</option>
                <option value="India">India</option>
                <option value="Pakistan">Pakistan</option>
                <option value="Sri Lanka">Sri Lanka</option>
                <option value="Nepal">Nepal</option>
                <option value="Myanmar">Myanmar</option>
              </select>
              <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-xs text-slate-400">Why is this important?</span>
            </div>
          </div>

          <!-- Your Subscription -->
          <div class="glass rounded-2xl p-6">
            <h3 class="font-display font-semibold text-white text-lg mb-4">Your subscription</h3>
            <div class="space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="subscription" value="buy" x-model="selectedPlan" class="radio-custom" checked>
                <div class="flex-1">
                  <span class="text-white font-medium">Buy now</span>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-500 text-white font-semibold">LIMITED OFFER</span>
                    <span class="text-xs text-slate-300">50% off for 12 months</span>
                  </div>
                  <span class="text-xs text-teal-300 block mt-1">plus free guided setup</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Billing Period -->
          <div class="glass rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-display font-semibold text-white text-lg">Billing period</h3>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs text-slate-400">Why am I seeing US$?</span>
              </div>
            </div>
            <div class="space-y-4">
              <!-- Monthly -->
              <label class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border transition-colors"
                     :class="billingPeriod === 'monthly' ? 'border-indigo-400 bg-indigo-400/10' : 'border-slate-600 hover:border-indigo-400'">
                <input type="radio" name="billing" value="monthly" x-model="billingPeriod" class="radio-custom">
                <div class="flex-1">
                  <div class="flex items-baseline gap-2">
                    <span class="text-slate-500 line-through text-lg">US$<span x-text="pricing.monthly.subtotal"></span>/mo</span>
                    <span class="text-teal-400 font-semibold text-xl">0 Taka/mo</span>
                  </div>
                  <span class="text-xs text-green-400">100% Free Forever</span>
                </div>
              </label>

              <!-- Annually -->
              <label class="flex items-center gap-3 cursor-pointer p-4 rounded-xl border transition-colors"
                     :class="billingPeriod === 'annually' ? 'border-indigo-400 bg-indigo-400/10' : 'border-slate-600 hover:border-indigo-400'">
                <input type="radio" name="billing" value="annually" x-model="billingPeriod" class="radio-custom">
                <div class="flex-1">
                  <div class="flex items-baseline gap-2">
                    <span class="text-slate-500 line-through text-lg">US$<span x-text="pricing.annually.subtotal"></span>/yr</span>
                    <span class="text-teal-400 font-semibold text-xl">0 Taka/yr</span>
                  </div>
                  <span class="text-xs text-green-400">100% Free Forever</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Right Panel -->
        <div class="glass rounded-2xl p-6">
          <div class="mb-6">
            <h3 class="font-display font-bold text-2xl text-white mb-2" x-text="planData ? planData.name : 'Simple Start'"></h3>
            <p class="text-slate-400 text-sm" x-text="planData ? planData.description : 'Perfect for small businesses getting started'"></p>
          </div>

          <!-- Features List -->
          <div class="space-y-3 mb-8">
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Track income & expenses</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Send unlimited custom invoices & quotes</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Connect your bank</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Track GST and VAT</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Insights & reports</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Progress invoicing</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">Up to 250 items in Chart of Accounts</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(20,184,166,0.2);">
                <svg class="w-3 h-3 text-teal-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-slate-300 text-sm">For one user, plus your accountant</span>
            </div>
          </div>

          <!-- Pricing Summary -->
          <div class="border-t border-slate-700 pt-6">
            <div class="space-y-3 mb-6">
              <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">Subtotal</span>
                <span class="text-white font-semibold">US$<span x-text="subtotal.toFixed(2)"></span></span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-slate-400 text-sm">+VAT @15% (if applicable)</span>
                <span class="text-slate-400 text-sm">US$<span x-text="vatAmount.toFixed(2)"></span></span>
              </div>
              <div class="flex justify-between items-center pt-3 border-t border-slate-700">
                <span class="text-white font-semibold">Total</span>
                <span class="text-white font-bold text-lg">US$<span x-text="total.toFixed(2)"></span></span>
              </div>
            </div>

            <!-- Continue Button -->
            <a :href="'/checkout/' + (planData ? planData.slug : 'pro-plus') + '?billing=' + billingPeriod" class="btn-primary w-full text-white font-semibold py-4 rounded-xl text-center block">
              Try for free
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
