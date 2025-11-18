<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Verification - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    idImage: null,
    idImagePreview: null,
    uploadFile(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            this.idImage = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.idImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
    removeImage() {
        this.idImage = null;
        this.idImagePreview = null;
        document.getElementById('id-upload').value = '';
    }
}">

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('images/badmintour-logo.png') }}" alt="BadminTour Logo" class="h-24">
            </div>