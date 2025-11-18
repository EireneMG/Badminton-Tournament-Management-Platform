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

        