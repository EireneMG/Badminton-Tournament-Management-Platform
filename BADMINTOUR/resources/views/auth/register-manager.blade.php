<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Registration - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden" x-data="{ currentStep: 1 }">
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
                <a href="{{ route('login') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center p-8">
            <div class="w-full max-w-2xl px-8">
                <h2 class="text-[#7B1F3C] text-5xl font-bold mb-8 text-center">Register</h2>
                
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
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
                        </div>

                        <!-- Gender and Contact Number -->
                        <div class="grid grid-cols-2 gap-4">
                            <select 
                                name="gender"
                                required
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <input 
                                type="text" 
                                name="contact_number" 
                                placeholder="Contact number"
                                value="{{ old('contact_number') }}"
                                required
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Next Button -->
                        <div class="pt-4 flex justify-end">
                            <button 
                                type="button"
                                @click="currentStep = 2"
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
                        </div>

                        <!-- Create Username -->
                        <div>
                            <input 
                                type="text" 
                                name="username" 
                                placeholder="Create username"
                                value="{{ old('username') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Create Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="Create password"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                placeholder="Confirm password"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Buttons -->
                        <div class="pt-4 flex justify-between items-center">
                            <button 
                                type="button"
                                @click="currentStep = 1"
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
</body>
</html>