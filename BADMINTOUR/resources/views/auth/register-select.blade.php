<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Registration Type - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-gradient-to-br from-[#1B4965] to-[#2C5F4F]" x-data="{ showManagerWarning: false }">
    <div class="h-full flex items-center justify-center p-8">
        <div class="max-w-5xl w-full">
            <!-- Back to Landing -->
            <div class="mb-8">
                <a href="/" class="inline-flex items-center text-white hover:text-[#D4A574] transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Home
                </a>
            </div>

            <!-- Title -->
            <div class="text-center mb-12">
                <h1 class="text-white text-5xl font-bold mb-3">Choose Registration Type</h1>
                <p class="text-white text-lg opacity-90">Select how you want to join the platform</p>
            </div>