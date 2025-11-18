<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side - Black Background -->
        <div class="w-5/12 bg-black flex flex-col justify-between p-12">
            <div class="flex-1 flex items-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Badminton Tournament Management Platform" class="w-48 h-48 mb-6 object-contain">
                <h1 class="text-white text-4xl font-bold leading-tight">
                    Badminton Tournament<br>Management Platform
                </h1>
            </div>
            <div class="text-white text-sm">
                Don't have an account yet? 
                <a href="{{ route('register') }}" class="text-[#7B1F3C] hover:text-[#5D1730] font-semibold">Register</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center">
            <div class="w-full max-w-md px-8">
                <h2 class="text-[#2C5F4F] text-5xl font-bold mb-16 text-center">Hello, Player!</h2>
                
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Username/Email Input -->
                    <div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            placeholder="Username"
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] text-gray-700 placeholder-gray-400"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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