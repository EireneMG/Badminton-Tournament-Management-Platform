<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Registration - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden" x-data="managerSteps()">
    <!-- Back to Home Button -->
    <div class="absolute top-4 left-4 z-10">
        <a href="/" class="inline-flex items-center text-gray-700 hover:text-[#2C5F4F] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Home
        </a>
    </div>
    
    <div class="flex h-full">
        <!-- Left Side - White Background -->
        <div class="w-5/12 bg-white flex flex-col justify-between p-12">
            <div class="flex-1 flex flex-col items-center justify-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Badminton Tournament Management Platform" class="w-48 h-48 mb-6 object-contain">
                <h1 class="text-black text-2xl font-bold text-center leading-tight">
                    Badminton Tournament<br>Management Platform
                </h1>
            </div>
            <div class="text-black text-sm">
                Already have an account? 
                <a href="{{ route('login.manager') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center p-8">
            <div class="w-full max-w-2xl px-8">
                <h2 class="text-[#7B1F3C] text-5xl font-bold mb-8 text-center">Register</h2>
                
                <!-- Error Messages (only for Step 1 fields, excluding email/password) -->
                @php
                    $step1Errors = array_filter(
                        $errors->getMessages(),
                        function ($messages, $field) {
                            return !in_array($field, ['email', 'password', 'password_confirmation'], true);
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                @endphp
                @if (!empty($step1Errors))
                    <div class="bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-md mb-4">
                        <p class="font-semibold mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($step1Errors as $fieldErrors)
                                @foreach ($fieldErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" class="space-y-4" id="manager-register-form">
                    @csrf
                    <input type="hidden" name="role" value="manager">

                    <!-- Step 1: Personal Information -->
                    <div x-show="currentStep === 1" class="space-y-4">
                        <!-- First Name -->
                        <div>
                            <input 
                                type="text" 
                                name="first_name" 
                                placeholder="First name"
                                value="{{ old('first_name') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Middle Name and Last Name -->
                        <div class="grid grid-cols-2 gap-4">
                            <input 
                                type="text" 
                                name="middle_name" 
                                placeholder="Middle name"
                                value="{{ old('middle_name') }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            <input 
                                type="text" 
                                name="last_name" 
                                placeholder="Last name"
                                value="{{ old('last_name') }}"
                                required
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gender and Contact Number -->
                        <div class="grid grid-cols-2 gap-4">
                            <select 
                                name="gender"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Gender (Optional)</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <input 
                                type="text" 
                                name="contact_number" 
                                placeholder="Contact number"
                                value="{{ old('contact_number') }}"
                                required
                                pattern="[0-9()+\-\s]+"
                                title="Please enter a valid contact number"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('contact_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ID Upload Section -->
                        <!-- Manager ID upload removed from registration; will be collected during club creation -->

                        <!-- Next Button -->
                        <div class="pt-4 flex justify-end">
                            <button 
                                type="button"
                                @click="goToStep(2)"
                                class="bg-[#7B1F3C] text-white px-20 py-2.5 rounded-md hover:bg-[#6B1A34] transition font-semibold">
                                Next
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Account Credentials -->
                    <div x-show="currentStep === 2" x-cloak class="space-y-4">
                        <!-- Email Address -->
                        <div>
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="Email Address"
                                value="{{ old('email') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Create Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="Create password (minimum 8 characters)"
                                required
                                minlength="8"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                placeholder="Confirm password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('password_confirmation')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="pt-4 flex justify-between items-center">
                            <button 
                                type="button"
                                @click="goToStep(1)"
                                class="text-gray-600 hover:text-gray-900 font-semibold">
                                ← Back
                            </button>
                            <button 
                                type="submit"
                                class="bg-[#7B1F3C] text-white px-20 py-2.5 rounded-md hover:bg-[#6B1A34] transition font-semibold">
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
    [x-cloak] { display: none !important; }
    </style>

    <script>
        // Toggle required fields by step to avoid hidden-field validation errors
        function setManagerRequired(step) {
            const step1Fields = ['first_name','last_name','contact_number','id_document'];
            const step2Fields = ['email','password','password_confirmation'];

            step1Fields.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.required = (step === 1);
            });
            step2Fields.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.required = (step === 2);
            });
        }

        document.addEventListener('alpine:init', () => {
            // Alpine helper to move steps and manage required attrs
            Alpine.data('managerSteps', () => ({
                currentStep: {{ $errors->has('email') || $errors->has('password') || $errors->has('password_confirmation') ? 2 : 1 }},
                init() {
                    setManagerRequired(this.currentStep);
                },
                goToStep(step) {
                    this.currentStep = step;
                    setManagerRequired(step);
                }
            }));
        });
    </script>
</body>
</html>
