<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
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
                Don't have an account yet? 
                <a href="{{ route('register.player') }}" class="text-[#7B1F3C] hover:text-[#5D1730] font-semibold">Register</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center">
            <div class="w-full max-w-md px-8">
                <h2 class="text-[#2C5F4F] text-5xl font-bold mb-8 text-center">Hello, Player!</h2>
                
                <!-- Success Message -->
                @if (session('success'))
                    <div class="bg-green-50 border border-green-400 text-green-800 px-4 py-3 rounded-md mb-6">
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                <!-- General Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            placeholder="Email"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                    </div>

                    <!-- Password Input -->
                    <div>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            placeholder="Password"
                            required
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                    </div>

                    <!-- Forgot Password & Email Links -->
                    <div class="flex justify-between items-center text-sm">
                        <a href="{{ route('password.request') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">
                            Forgot Password?
                        </a>
                        <a href="{{ route('forgot-email') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">
                            Forgot Email?
                        </a>
                    </div>

                    <!-- Login Button -->
                    <div class="pt-8">
                        <button 
                            type="submit"
                            class="w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200"
                        >
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
