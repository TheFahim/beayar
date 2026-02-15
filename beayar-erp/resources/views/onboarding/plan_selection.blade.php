<x-layouts.app>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
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
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Choose Your Plan
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    Select the plan that fits your business needs.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- Free Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-transparent hover:border-blue-500 transition-all">
                    <div class="px-6 py-8">
                        <h3 class="text-2xl font-bold text-gray-900 text-center">Free Plan</h3>
                        <p class="mt-4 text-center text-gray-500">Perfect for getting started.</p>
                        <p class="mt-8 text-center text-5xl font-extrabold text-gray-900">$0</p>
                        <p class="mt-2 text-center text-sm text-gray-500">forever</p>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">1 Company</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">2 Users</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">5 Quotations/mo</span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 py-4 bg-gray-50">
                        <form id="free-plan-form" action="{{ route('onboarding.plan.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_type" value="free">
                            <button type="button" onclick="confirmFreePlan()" class="w-full bg-blue-600 text-white rounded-md py-2 hover:bg-blue-700 transition">Select Free</button>
                        </form>
                    </div>
                </div>

                <!-- Custom Plan -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-blue-500 transition-all" onclick="showCustomForm()">
                    <div class="px-6 py-8">
                        <h3 class="text-2xl font-bold text-gray-900 text-center">Custom Plan</h3>
                        <p class="mt-4 text-center text-gray-500">Tailored to your scale.</p>
                        <p class="mt-8 text-center text-5xl font-extrabold text-gray-900">Custom</p>
                        <p class="mt-2 text-center text-sm text-gray-500">pricing based on usage</p>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">Unlimited Companies</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">Unlimited Users</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="ml-3 text-gray-700">Custom Modules</span>
                            </li>
                        </ul>
                    </div>
                    <div class="px-6 py-4 bg-gray-50">
                        <button type="button" class="w-full bg-indigo-600 text-white rounded-md py-2 hover:bg-indigo-700 transition">Configure Now</button>
                    </div>
                </div>
            </div>

            <!-- Custom Plan Questionnaire Modal/Section -->
            <div id="custom-form-container" class="hidden mt-12 bg-white rounded-lg shadow-xl p-8 border border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Configure Your Custom Plan</h3>
                <form id="custom-plan-form" action="{{ route('onboarding.plan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_type" value="custom">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Count -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">How many companies do you manage?</label>
                            <input type="number" name="company_count" min="1" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        </div>

                        <!-- Total Employees -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total employees across all companies?</label>
                            <input type="number" name="total_employees" min="1" value="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        </div>

                        <!-- Quotation Volume -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monthly Quotations (est.)</label>
                            <input type="number" name="quotation_volume" min="0" value="50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        </div>

                        <!-- Modules -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Modules</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="crm" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" checked>
                                    <span class="ml-2">CRM</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="hr" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2">HR</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="accounts" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2">Accounts</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="modules[]" value="inventory" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2">Inventory</span>
                                </label>
                            </div>
                        </div>

                         <!-- Separate Modules Toggle -->
                         <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="separate_modules" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">I want separate Accounts & HR modules for each company (Higher Cost)</span>
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
            <div class="relative mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Confirm Plan Selection</h3>
                    <div class="mt-2 px-2 py-3">
                        <p class="text-sm text-gray-500 mb-4">You are about to select the <span id="modal-plan-name" class="font-bold text-gray-800"></span>.</p>
                        <div class="text-left bg-gray-50 p-4 rounded-md mb-4 border border-gray-100">
                            <div class="flex justify-between items-baseline mb-2">
                                <p class="text-2xl font-bold text-gray-900" id="modal-plan-price"></p>
                                <p class="text-xs text-gray-500" id="modal-billing-cycle"></p>
                            </div>
                            <ul class="text-sm text-gray-600 list-disc list-inside space-y-1" id="modal-features">
                                <!-- Features will be injected here -->
                            </ul>
                        </div>
                        <p class="text-xs text-gray-400">By confirming, you agree to our terms of service and billing policy.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 px-2 py-3">
                        <button onclick="closeModal()" class="flex-1 px-4 py-2 bg-white text-gray-700 text-base font-medium rounded-md shadow-sm border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
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

        function confirmFreePlan() {
            openModal(
                'Free Plan',
                '$0',
                'Forever',
                ['1 Company', '2 Users', '5 Quotations/mo'],
                'free-plan-form'
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
