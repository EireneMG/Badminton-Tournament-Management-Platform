<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Club - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    clubLogo: null,
    clubLogoPreview: null,
    uploadLogo(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            this.clubLogo = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.clubLogoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
    removeLogo() {
        this.clubLogo = null;
        this.clubLogoPreview = null;
        document.getElementById('logo-upload').value = '';
    }
}">
    <div class="min-h-screen flex items-center justify-center p-4 py-12">
        <div class="w-full max-w-4xl">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/badmintour-logo.png') }}" alt="BadminTour Logo" class="h-24">
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-[#2C5F4F] mb-3">Create Your Club</h1>
                    <p class="text-gray-600 text-lg">Complete the details below to register your club in the BadminTour system.</p>
                </div>

               
