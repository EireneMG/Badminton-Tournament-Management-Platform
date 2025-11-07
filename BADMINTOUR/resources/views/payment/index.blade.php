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
                        ⚠️ After making the payment, please upload the proof of payment below and submit this form.
                    </p>
                </div>
            </div>
