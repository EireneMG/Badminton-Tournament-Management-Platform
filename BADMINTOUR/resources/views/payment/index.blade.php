@extends('components.dashboard-layout')

@section('content')
<div class="min-h-screen bg-gray-50 p-6" x-data="paymentData()">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Tournament Payment</h1>
            <p class="text-gray-600">Complete your registration payment to confirm your tournament participation.</p>
        </div>

        <!-- Payment Form Card -->
        <div class="bg-white rounded-lg border-2 border-[#D4A574] p-6">
            <!-- Tournament Information -->
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-[#D4A574]">Tournament Information</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Tournament Name</p>
                            <p class="font-semibold text-gray-900">National Badminton Championship 2025</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Tournament Date</p>
                            <p class="font-semibold text-gray-900">January 15-20, 2025</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Location</p>
                            <p class="font-semibold text-gray-900">Metro Sports Complex</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 mb-1">Categories Joined</p>
                            <p class="font-semibold text-gray-900">Men's Singles, Mixed Doubles</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Fee -->
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-[#D4A574]">Registration Fee</h2>
                <div class="bg-[#E8DCC4] rounded-lg p-6 border-2 border-[#D4A574]">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-700">Men's Singles</span>
                        <span class="font-semibold text-gray-900">₱ 500.00</span>
                    </div>
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-[#D4A574]">
                        <span class="text-gray-700">Mixed Doubles</span>
                        <span class="font-semibold text-gray-900">₱ 800.00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-gray-900">Total Amount</span>
                        <span class="text-3xl font-bold text-[#C85A54]">₱ 1,300.00</span>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b-2 border-[#D4A574]">Payment Instructions</h2>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <p class="text-sm text-gray-800 mb-3">
                        <strong>Please transfer the registration fee to the following account:</strong>
                    </p>
                    <div class="space-y-2 text-sm text-gray-800">
                        <p><strong>Bank Name:</strong> Philippine National Bank (PNB)</p>
                        <p><strong>Account Name:</strong> Badminton Tournament Management</p>
                        <p><strong>Account Number:</strong> 1234-5678-9012-3456</p>
                        <p><strong>Payment Reference:</strong> Your Full Name + Tournament Name</p>
                    </div>
                    <p class="text-xs text-gray-600 mt-3">
                        ⚠️ After making the payment, contact the tournament manager directly to confirm your payment and send your proof of payment.
                    </p>
                </div>
            </div>

            <!-- Payment Confirmation Note -->
            <div class="mb-6">
                <div class="bg-[#E8DCC4] border-l-4 border-[#D4A574] rounded-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 text-[#C85A54] mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        How to Complete Payment
                    </h3>
                    <ol class="text-sm text-gray-800 space-y-2 ml-7">
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">1.</span>
                            <span>Transfer the payment to the account details shown above.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">2.</span>
                            <span>Contact the tournament manager directly using the provided contact information.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">3.</span>
                            <span>Send your payment proof (screenshot or receipt) to the manager.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="font-semibold mr-2">4.</span>
                            <span>The manager will verify your payment and update your registration status.</span>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Payment Deadline Warning -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Payment Deadline:</strong> January 8, 2025 (11:59 PM). Late payments will not be accepted, and your registration will be cancelled.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex items-center justify-between">
                <a href="/tournaments" class="text-gray-600 hover:text-gray-900 font-semibold">
                    ← Back to Tournaments
                </a>
                <a 
                    href="/tournaments"
                    class="px-6 py-2.5 bg-[#C85A54] hover:bg-[#B54A44] text-white rounded-lg font-semibold transition">
                    I Understand
                </a>
            </div>
        </div>

    </div>
</div>

<script>
function paymentData() {
    return {
        showSuccess: false
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
