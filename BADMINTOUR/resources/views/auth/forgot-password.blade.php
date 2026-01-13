<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side - Black Background -->
        <div class="w-5/12 bg-black flex flex-col justify-between p-12">
            <div class="flex-1 flex items-center">
                <h1 class="text-white text-4xl font-bold leading-tight">
                    Badminton Tournament<br>Management Platform
                </h1>
            </div>
            <div class="text-white text-sm">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Back to Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center">
            <div class="w-full max-w-md px-8">
                <h2 class="text-[#2C5F4F] text-5xl font-bold mb-6 text-center">Forgot Password?</h2>
                
                <p class="text-gray-600 text-center mb-12 text-sm">
                    No problem. Enter your email address and we'll send you a password reset link.
                </p>

                <!-- Success Message -->
                @if (session('status'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                                @if(session('reset_link') && !config('mail.enabled', false))
                                    <div class="mt-3 p-3 bg-white border border-green-300 rounded">
                                        <p class="text-xs text-gray-600 mb-2 font-semibold">Reset Link (for testing):</p>
                                        <a href="{{ session('reset_link') }}" class="text-xs text-blue-600 break-all hover:underline">
                                            {{ session('reset_link') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

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
                
                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            placeholder="Email Address"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-8">
                        <button 
                            type="submit"
                            class="w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200"
                        >
                            Send Reset Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
