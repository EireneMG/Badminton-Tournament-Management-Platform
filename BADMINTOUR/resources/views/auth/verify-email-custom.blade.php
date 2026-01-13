<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
    <div class="flex h-full">
        <!-- Left Side - Black Background -->
        <div class="w-5/12 bg-black flex flex-col justify-between p-12">
            <div class="flex-1 flex flex-col items-center justify-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Badminton Tournament Management Platform" class="w-48 h-48 mb-6 object-contain">
                <h1 class="text-white text-2xl font-bold text-center leading-tight">
                    Badminton Tournament<br>Management Platform
                </h1>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center">
            <div class="w-full max-w-md px-8">
                @if((auth()->check() && auth()->user()->hasVerifiedEmail()) || session('verified'))
                    <!-- Email Verified State -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500 rounded-full mb-6">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-[#2C5F4F] text-4xl font-bold mb-4">Email Verified!</h2>
                        <p class="text-gray-700 text-sm mb-6">
                            Your email has been verified. You can now log in.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <a href="{{ route('login.select') }}" 
                           class="block w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200 text-center">
                            Back to Login
                        </a>
                    </div>
                @else
                    <!-- Email Not Verified State -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-[#2C5F4F] rounded-full mb-6">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-[#2C5F4F] text-4xl font-bold mb-4">Verify Your Email</h2>
                        <p class="text-gray-700 text-sm mb-6">
                            A verification link has been sent to your email.
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="bg-green-50 border border-green-400 text-green-800 px-4 py-3 rounded-md mb-6">
                            <p class="text-sm font-medium text-center">
                                A new verification link has been sent to your email address.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-4">
                        @if(auth()->check())
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button 
                                    type="submit"
                                    class="w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200"
                                >
                                    Resend Verification Email
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button 
                                    type="submit"
                                    class="w-full bg-gray-500 text-white font-semibold py-3 px-6 rounded-lg hover:bg-gray-600 transition duration-200"
                                >
                                    Log Out
                                </button>
                            </form>
                        @else
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-4">
                                    Please log in to resend the verification email.
                                </p>
                                <div class="space-y-3">
                                    <a href="{{ route('login') }}" 
                                       class="block w-full bg-[#2C5F4F] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#234A3D] transition duration-200 text-center">
                                        Login as Player
                                    </a>
                                    <a href="{{ route('login.manager') }}" 
                                       class="block w-full bg-[#7B1F3C] text-white font-semibold py-3 px-6 rounded-lg hover:bg-[#6B1A34] transition duration-200 text-center">
                                        Login as Club Manager
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600">
                            Didn't receive the email? Check your spam folder or click the resend button above.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
