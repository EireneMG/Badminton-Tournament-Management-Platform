<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login - Badminton Tournament Management</title>
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
                Don't have an account yet? 
                <a href="{{ route('register') }}" class="text-[#7B1F3C] hover:text-[#5D1730] font-semibold">Register</a>
            </div>
        </div>
    </div>
</body>
</html>