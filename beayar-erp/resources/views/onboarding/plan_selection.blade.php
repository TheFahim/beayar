<x-layouts.app>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Whoops!</strong>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                    Choose Your Plan
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                    Select the plan that fits your business needs.
                </p>
            </div>

            <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 pb-8 hide-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
                @foreach($plans as $plan)
                    @if($plan->slug === 'custom')
                        <!-- Custom Plan -->
                        <div class="min-w-[85vw] sm:min-w-[45%] lg:min-w-[30%] snap-center bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-blue-500 transition-all" onclick="showCustomForm()">
                            <div class="px-6 py-8">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">{{ $plan->name }}</h3>
                                <p class="mt-4 text-center text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                                <p class="mt-8 text-center text-5xl font-extrabold text-gray-900 dark:text-white">Custom</p>
                                <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">pricing based on usage</p>

                                <ul class="mt-8 space-y-4">
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">Unlimited Companies</span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">Unlimited Users</span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">Custom Modules</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                                <button type="button" class="w-full bg-indigo-600 text-white rounded-md py-2 hover:bg-indigo-700 transition">Configure Now</button>
                            </div>
                        </div>
                    @else
                        <!-- Standard Plan -->
                        <div class="min-w-[85vw] sm:min-w-[45%] lg:min-w-[30%] snap-center bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all flex flex-col">
                            <div class="px-6 py-8 flex-1">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center">{{ $plan->name }}</h3>
                                <p class="mt-4 text-center text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                                <p class="mt-8 text-center text-5xl font-extrabold text-gray-900 dark:text-white">${{ number_format($plan->base_price, 0) }}</p>
                                <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">per {{ $plan->billing_cycle }}</p>

                                <ul class="mt-8 space-y-4">
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $plan->limits['sub_companies'] ?? 1 }} Company/ies</span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $plan->limits['employees'] ?? 1 }} User/s</span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">
                                            @if(($plan->limits['quotations'] ?? 0) == -1)
                                                Unlimited Quotations
                                            @else
                                                {{ $plan->limits['quotations'] ?? 0 }} Quotations/mo
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                                <form id="plan-form-{{ $plan->slug }}" action="{{ route('onboarding.plan.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_type" value="{{ $plan->slug }}">
                                    <button type="button" onclick="confirmStandardPlan('{{ $plan->name }}', '${{ number_format($plan->base_price, 0) }}', '{{ ucfirst($plan->billing_cycle) }}', {{ json_encode([
                                        ($plan->limits['sub_companies'] ?? 1) . ' Company/ies',
                                        ($plan->limits['employees'] ?? 1) . ' User/s',
                                        (($plan->limits['quotations'] ?? 0) == -1 ? 'Unlimited' : ($plan->limits['quotations'] ?? 0)) . ' Quotations/mo'
                                    ]) }}, 'plan-form-{{ $plan->slug }}')" class="w-full bg-blue-600 text-white rounded-md py-2 hover:bg-blue-700 transition">Select {{ $plan->name }}</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Custom Plan Questionnaire Modal/Section -->
            <div id="custom-form-container" class="hidden mt-12 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 border border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Configure Your Custom Plan</h3>
                <form id="custom-plan-form" action="{{ route('onboarding.plan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_type" value="custom">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Count -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">How many companies do you manage?</label>
                            <input type="number" name="company_count" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border dark:bg-gray-700 dark:text-white">
                        </div>

                        <!-- Total Employees -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total employees across all companies?</label>
                            <input type="number" name="total_employees" min="1" value="5" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border dark:bg-gray-700 dark:text-white">
                        </div>

                        <!-- Quotation Volume -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Quotations (est.)</label>
                            <input type="number" name="quotation_volume" min="0" value="50" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border dark:bg-gray-700 dark:text-white">
                        </div>

                        <!-- Modules -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Modules</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="crm" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700" checked>
                                    <span class="ml-2 dark:text-gray-300">CRM</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="hr" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700">
                                    <span class="ml-2 dark:text-gray-300">HR</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="accounts" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700">
                                    <span class="ml-2 dark:text-gray-300">Accounts</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="inventory" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700">
                                    <span class="ml-2 dark:text-gray-300">Inventory</span>
                                </label>
                            </div>
                        </div>

                         <!-- Separate Modules Toggle -->
                         <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="separate_modules" value="1" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">I want separate Accounts & HR modules for each company (Higher Cost)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button type="button" onclick="confirmCustomPlan()" class="w-full bg-indigo-600 text-white text-lg font-semibold rounded-md py-3 hover:bg-indigo-700 transition">Confirm Custom Plan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="relative mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Confirm Plan Selection</h3>
                    <div class="mt-2 px-2 py-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">You are about to select the <span id="modal-plan-name" class="font-bold text-gray-800 dark:text-gray-200"></span>.</p>
                        <div class="text-left bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-4 border border-gray-100 dark:border-gray-600">
                            <div class="flex justify-between items-baseline mb-2">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white" id="modal-plan-price"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" id="modal-billing-cycle"></p>
                            </div>
                            <ul class="text-sm text-gray-600 dark:text-gray-300 list-disc list-inside space-y-1" id="modal-features">
                                <!-- Features will be injected here -->
                            </ul>
                        </div>
                        <p class="text-xs text-gray-400">By confirming, you agree to our terms of service and billing policy.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 px-2 py-3">
                        <button onclick="closeModal()" class="flex-1 px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-white text-base font-medium rounded-md shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button id="confirm-btn" class="flex-1 px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-colors">
                            Confirm Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedPlanFormId = null;

        function showCustomForm() {
            document.getElementById('custom-form-container').classList.remove('hidden');
            document.getElementById('custom-form-container').scrollIntoView({ behavior: 'smooth' });
        }

        function selectPlan(type) {
            if (type === 'custom') {
                showCustomForm();
            }
        }

        function openModal(planName, price, billing, features, formId) {
            document.getElementById('modal-plan-name').innerText = planName;
            document.getElementById('modal-plan-price').innerText = price;
            document.getElementById('modal-billing-cycle').innerText = billing;

            const featuresList = document.getElementById('modal-features');
            featuresList.innerHTML = '';
            features.forEach(feature => {
                const li = document.createElement('li');
                li.innerText = feature;
                featuresList.appendChild(li);
            });

            selectedPlanFormId = formId;
            document.getElementById('confirmation-modal').classList.remove('hidden');
            trackEvent('modal_open', { plan: planName });
        }

        function closeModal() {
            document.getElementById('confirmation-modal').classList.add('hidden');
            selectedPlanFormId = null;
            trackEvent('modal_close', {});
        }

        function confirmStandardPlan(name, price, billing, features, formId) {
            openModal(
                name,
                price,
                billing,
                features,
                formId
            );
        }

        function confirmCustomPlan() {
            const form = document.getElementById('custom-plan-form');
            // Get checked modules names
            const modules = Array.from(form.querySelectorAll('input[name="modules[]"]:checked'))
                .map(cb => cb.parentElement.querySelector('span').innerText.trim());

            // Add separate modules info if checked
            const separateModules = form.querySelector('input[name="separate_modules"]:checked');
            if (separateModules) {
                modules.push('Separate Accounts & HR per company');
            }

            const companyCount = form.querySelector('input[name="company_count"]').value;
            // total_employees is input, but let's just list it
            const employees = form.querySelector('input[name="total_employees"]').value;

            const features = [
                `${companyCount} Company${companyCount > 1 ? 's' : ''}`,
                `${employees} Total Employees`,
                ...modules
            ];

            openModal(
                'Custom Plan',
                'Custom Pricing',
                'Billed Monthly',
                features,
                'custom-plan-form'
            );
        }

        document.getElementById('confirm-btn').addEventListener('click', function() {
            if (selectedPlanFormId) {
                const form = document.getElementById(selectedPlanFormId);
                if(form) {
                    trackEvent('plan_confirmed', { formId: selectedPlanFormId });
                    form.submit();
                }
            }
        });

        function trackEvent(eventName, data) {
            // Placeholder for analytics
            console.log('Analytics Event:', eventName, data);
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('confirmation-modal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</x-layouts.app>
