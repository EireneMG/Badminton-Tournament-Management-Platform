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

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
                        {{ session('status') }}
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
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
