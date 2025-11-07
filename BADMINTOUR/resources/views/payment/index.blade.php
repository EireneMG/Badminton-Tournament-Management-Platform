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