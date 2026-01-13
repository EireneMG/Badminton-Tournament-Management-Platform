<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
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
                Remember your password? 
                <a href="{{ route('login.select') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Back to Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center">
            <div class="w-full max-w-md px-8">
                <h2 class="text-[#2C5F4F] text-4xl font-bold mb-6 text-center">Reset Password</h2>
                <p class="text-gray-700 text-sm mb-6 text-center">Enter your new password below.</p>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-medium">Error</p>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            placeholder="Email Address"
                            value="{{ old('email', $request->email) }}"
                            required 
                            autofocus
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            placeholder="New Password (minimum 8 characters)"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            placeholder="Confirm New Password"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            class="w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200"
                        >
                            Reset Password
                        </button>
                    </div>
                </form>

                <!-- Back to Login Link -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login.select') }}" class="text-[#2C5F4F] hover:text-[#234A3D] text-sm font-semibold">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
