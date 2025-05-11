<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Donate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body class="bg-white text-gray-800">

    <div x-data="donationApp()" class="relative min-h-screen flex items-center justify-center">

        <button @click="open = true"
            class="bg-yellow-700 text-white font-semibold py-2 px-6 rounded-full flex items-center justify-between hover:bg-yellow-800 transition space-x-4">
            <span>Donate</span>
            <span class="border border-white rounded-full p-1">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7">
                    </path>
                </svg>
            </span>
        </button>


        <!-- Modal -->
        <div x-show="open" x-transition @click.self="open = false"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40" x-cloak>
            <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-lg relative">

                <!-- Step 1: Donation Details -->
                <div x-show="step === 1">
                    <!-- Header: Title Left + Close Icon Right -->
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-xl font-semibold text-gray-900">Missionary Donation</h2>
                        <button @click="open = false"
                            class="text-yellow-700 text-2xl hover:text-yellow-900">&times;</button>
                    </div>

                    <hr class="border-t border-gray-200 mb-4">

                    <!-- Toggle Tabs -->
                    <div class="flex justify-center mb-4">
                        <button @click="type = 'one-time'"
                            :class="type === 'one-time' ? activeTabLeft : inactiveTabLeft">
                            One-Time
                        </button>
                        <button @click="type = 'monthly'"
                            :class="type === 'monthly' ? activeTabRight : inactiveTabRight">
                            Monthly
                        </button>
                    </div>

                    <!-- Name and Email Inputs -->
                    <div class="flex space-x-4 mb-4">
                        <input type="text" placeholder="Donor's Name" x-model="donorName"
                            class="w-1/2 border border-gray-300 rounded px-3 py-2 outline-none focus:border-yellow-700">
                        <input type="email" placeholder="Donor's Email" x-model="donorEmail"
                            class="w-1/2 border border-gray-300 rounded px-3 py-2 outline-none focus:border-yellow-700">
                    </div>

                    <!-- Dropdown -->
                    <div class="mb-4">
                        <select x-model="selectedProject" class="w-full border border-gray-300 rounded px-3 py-2 outline-none focus:border-yellow-700">
                            <option>Night Bright</option>
                            <option>Other Options</option>
                        </select>
                    </div>

                    <!-- Amounts -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <template x-for="(amount, index) in predefinedAmounts" :key="index">
                            <button @click="selectedAmount = amount"
                                :class="selectedAmount == amount ? activeAmount : inactiveAmount"
                                x-text="`$${amount}`"></button>
                        </template>
                    </div>

                    <div class="mb-4">
                        <a href="#" @click.prevent="showMessage = !showMessage" class="text-sm text-yellow-700 hover:underline">+ Add a message</a>
                        <textarea x-show="showMessage" x-model="message" class="w-full mt-2 border border-gray-300 rounded px-3 py-2 outline-none focus:border-yellow-700" rows="3" placeholder="Your message..."></textarea>
                    </div>

                    <!-- Stay Anonymous + Continue -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center">
                            <input type="checkbox" x-model="stayAnonymous" class="form-checkbox text-yellow-700 rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Stay Anonymous</span>
                        </label>

                        <button @click="goToStep(2)"
                            class="bg-yellow-700 text-white px-6 py-2 rounded hover:bg-yellow-800 transition font-semibold">
                            Continue
                        </button>
                    </div>
                </div>

                <!-- Step 2: Final Details -->
                <div x-show="step === 2">
                    <!-- Header with Back Button + Title Left + Close Icon Right -->
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <button @click="goToStep(1)" class="mr-3 text-gray-600 hover:text-gray-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <h2 class="text-xl font-semibold text-gray-900">Final Details</h2>
                        </div>
                        <button @click="open = false"
                            class="text-yellow-700 text-2xl hover:text-yellow-900">&times;</button>
                    </div>

                    <hr class="border-t border-gray-200 mb-4">

                    <!-- Simplified Donation Summary -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center py-2">
                            <span class="font-medium text-gray-800">Donation</span>
                            <span class="font-medium" x-text="`$${selectedAmount}`"></span>
                        </div>
                    </div>

                    <hr class="border-t border-gray-200 mb-4">

                    <!-- Credit Card Processing Fees -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium text-gray-800">Credit card processing fees</span>
                            <span class="font-medium" x-text="`$${processingFee.toFixed(2)}`"></span>
                        </div>
                        <!-- Payment Method Dropdown -->
                        <div class="relative">
                            <div x-data="{ open: false }" class="w-full">
                                <button @click="open = !open" type="button"
                                    class="flex justify-between items-center w-full bg-yellow-700 text-white rounded py-2 px-3">
                                    <span x-text="formatPaymentMethod()"></span>
                                    <svg class="w-5 h-5" :class="{'transform rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg">
                                    <ul class="py-1 max-h-60 overflow-auto">
                                        <li>
                                            <a @click="paymentMethod = 'card-amex'; updateProcessingFee(); open = false"
                                                class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">AMEX Card</a>
                                        </li>
                                        <li>
                                            <a @click="paymentMethod = 'card-visa'; updateProcessingFee(); open = false"
                                                class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">Visa & Others</a>
                                        </li>
                                        <li>
                                            <a @click="paymentMethod = 'bank'; updateProcessingFee(); open = false"
                                                class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">US Bank Account</a>
                                        </li>
                                        <li>
                                            <a @click="paymentMethod = 'cash-app'; updateProcessingFee(); open = false"
                                                class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">Cash App Pay</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-t border-gray-200 mb-4">

                    <!-- Tip Option -->
                    <div class="mb-4 bg-yellow-50 p-4 rounded">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium text-gray-800">Optional appreciation for Night Bright</span>
                            <div class="relative w-32">
                                <div x-data="{ openTip: false }" class="w-full">
                                    <button @click="openTip = !openTip" type="button"
                                        class="flex justify-between items-center w-full bg-white border border-gray-300 rounded py-2 px-3">
                                        <span x-text="`${tipPercent}%`"></span>
                                        <svg class="w-5 h-5" :class="{'transform rotate-180': openTip}" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div x-show="openTip" @click.away="openTip = false"
                                        class="absolute right-0 z-10 w-full mt-1 bg-white border border-gray-300 rounded shadow-lg">
                                        <ul class="py-1">
                                            <li>
                                                <a @click="tipPercent = 0; calculateTip(0); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">0%</a>
                                            </li>
                                            <li>
                                                <a @click="tipPercent = 5; calculateTip(5); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">5%</a>
                                            </li>
                                            <li>
                                                <a @click="tipPercent = 10; calculateTip(10); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">10%</a>
                                            </li>
                                            <li>
                                                <a @click="tipPercent = 12; calculateTip(12); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">12%</a>
                                            </li>
                                            <li>
                                                <a @click="tipPercent = 15; calculateTip(15); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">15%</a>
                                            </li>
                                            <li>
                                                <a @click="tipPercent = 20; calculateTip(20); openTip = false"
                                                    class="block px-4 py-2 hover:bg-gray-100 cursor-pointer">20%</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">
                            <span class="font-semibold">Why Tip?</span> Night Bright does not charge any platform fees
                            and relies on your generosity to support this free service.
                        </p>
                    </div>

                    <!-- Contact Permission -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="allowContact" class="form-checkbox h-5 w-5 text-yellow-700 rounded border-gray-300">
                            <span class="ml-2 text-gray-700">Allow Night Bright Inc to contact me</span>
                        </label>
                    </div>

                    <!-- Payment Button -->
                    <div class="flex justify-end">
                        <button @click="redirectToStripe()"
                            class="bg-yellow-700 text-white px-6 py-2 rounded hover:bg-yellow-800 transition font-semibold">
                            <span x-text="`Pay Now (${formatCurrency(totalAmount + tipAmount)})`"></span>
                        </button>
                    </div>
                </div>

                <!-- Loading overlay -->
                <div x-show="isLoading" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center">
                    <div class="flex flex-col items-center">
                        <svg class="animate-spin h-10 w-10 text-yellow-700 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-700">Processing your donation...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden form for Stripe redirect -->
        <form id="payment-form" method="POST" action="/process-payment" style="display: none;">
            <input type="hidden" name="donorName" :value="donorName">
            <input type="hidden" name="donorEmail" :value="donorEmail">
            <input type="hidden" name="selectedProject" :value="selectedProject">
            <input type="hidden" name="selectedAmount" :value="selectedAmount">
            <input type="hidden" name="processingFee" :value="processingFee">
            <input type="hidden" name="tipAmount" :value="tipAmount">
            <input type="hidden" name="totalAmount" :value="totalAmount + tipAmount">
            <input type="hidden" name="donationType" :value="type">
            <input type="hidden" name="message" :value="message">
            <input type="hidden" name="stayAnonymous" :value="stayAnonymous">
            <input type="hidden" name="allowContact" :value="allowContact">
            <input type="hidden" name="paymentMethod" :value="paymentMethod">
            <!-- Stripe will insert elements here -->
        </form>
    </div>

    <script>
        function donationApp() {
            return {
                open: false,
                step: 1,
                type: 'one-time',
                donorName: '',
                donorEmail: '',
                selectedProject: 'Night Bright',
                predefinedAmounts: [10, 25, 50, 100, 250, 500, 1000],
                selectedAmount: 25,
                showMessage: false,
                message: '',
                stayAnonymous: false,
                paymentMethod: 'card-visa',
                processingFee: 0,
                tipAmount: 3,
                tipPercent: 12,
                allowContact: false,
                isLoading: false,
                stripeKey: 'pk_test_51Qk1VAJPC0lZC5odysZ3tSvOEDPRUlM4ZhBJjpbFGRly574lS07cUyHiwHDGx7HEmMKT42RgmgRqF1JM53Q3KIp500UzmKEmVm', // Replace with your actual publishable key

                // Styling classes
                activeTabLeft: 'bg-yellow-700 text-white px-4 py-2 rounded-l font-semibold',
                activeTabRight: 'bg-yellow-700 text-white px-4 py-2 rounded-r font-semibold',
                inactiveTabLeft: 'bg-gray-100 text-gray-700 px-4 py-2 rounded-l hover:bg-gray-200',
                inactiveTabRight: 'bg-gray-100 text-gray-700 px-4 py-2 rounded-r hover:bg-gray-200',
                activeAmount: 'bg-yellow-700 text-white py-2 rounded font-semibold',
                inactiveAmount: 'bg-gray-100 text-gray-800 py-2 rounded hover:bg-gray-200',

                get totalAmount() {
                    return this.selectedAmount + this.processingFee;
                },

                calculateTip(percentage) {
                    const tipValue = (this.selectedAmount * (percentage / 100));
                    this.tipAmount = Math.round(tipValue * 100) / 100;
                    return this.tipAmount;
                },

                goToStep(stepNumber) {
                    this.step = stepNumber;
                    if (stepNumber === 2) {
                        this.updateProcessingFee();
                        this.calculateTip(this.tipPercent); // Use stored percentage
                    }
                },

                updateProcessingFee() {
                    if (this.paymentMethod === 'card-amex') {
                        this.processingFee = this.selectedAmount * 0.037; // 3.7%
                    } else if (this.paymentMethod === 'card-visa') {
                        this.processingFee = this.selectedAmount * 0.032; // 3.2%
                    } else if (this.paymentMethod === 'cash-app') {
                        this.processingFee = this.selectedAmount * 0.025; // 2.5%
                    } else {
                        this.processingFee = 0; // No fee for bank transfers
                    }

                    this.processingFee = Math.round(this.processingFee * 100) / 100;
                },

                formatPaymentMethod() {
                    const methods = {
                        'card-visa': 'Visa & Others',
                        'card-amex': 'AMEX Card',
                        'bank': 'US Bank Account',
                        'cash-app': 'Cash App Pay'
                    };
                    return methods[this.paymentMethod] || this.paymentMethod;
                },

                formatCurrency(amount) {
                    return `$${amount.toFixed(2)}`;
                },

                redirectToStripe() {
                    // Show loading state
                    this.isLoading = true;

                    fetch('/create-checkout-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            donorName: this.donorName,
                            donorEmail: this.donorEmail,
                            selectedProject: this.selectedProject,
                            selectedAmount: this.selectedAmount,
                            processingFee: this.processingFee,
                            tipAmount: this.tipAmount,
                            totalAmount: this.totalAmount + this.tipAmount,
                            donationType: this.type,
                            message: this.message,
                            stayAnonymous: this.stayAnonymous,
                            allowContact: this.allowContact,
                            paymentMethod: this.paymentMethod
                        })
                    })
                    .then(response => response.json())
                    .then(session => {
                        // Redirect to Stripe Checkout
                        const stripe = Stripe(this.stripeKey);
                        return stripe.redirectToCheckout({ sessionId: session.id });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.isLoading = false;
                        alert('There was an error processing your payment. Please try again.');
                    });
                },

                resetForm() {
                    this.open = false;
                    setTimeout(() => {
                        this.step = 1;
                        this.type = 'one-time';
                        this.donorName = '';
                        this.donorEmail = '';
                        this.selectedProject = 'Night Bright';
                        this.selectedAmount = 25;
                        this.showMessage = false;
                        this.message = '';
                        this.stayAnonymous = false;
                        this.paymentMethod = 'card-visa';
                        this.processingFee = 0;
                        this.tipAmount = 3;
                        this.tipPercent = 12;
                        this.allowContact = false;
                        this.isLoading = false;
                    }, 300);
                }
            };
        }
    </script>

</body>

</html>
