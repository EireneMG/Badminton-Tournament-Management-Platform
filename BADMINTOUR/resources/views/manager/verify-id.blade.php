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

            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 md:p-12">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-[#2C5F4F] mb-3">Manager Verification</h1>
                    <p class="text-gray-600 text-lg">Please upload a valid government ID to verify your identity.</p>
                </div>

            <!-- Form -->
                <form action="{{ route('manager.verify-id.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <!-- Upload Area -->
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-gray-700">Government-Issued ID</label>
                        
                        <!-- Upload Button -->
                        <div x-show="!idImagePreview" 
                             class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-[#2C5F4F] transition-colors duration-200">
                            <div class="space-y-3">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <div class="text-gray-600">
                                    <label for="id-upload" class="relative cursor-pointer font-semibold text-[#2C5F4F] hover:text-[#244D3E]">
                                        <span>Upload a file</span>
                                        <input id="id-upload" 
                                               name="government_id"
                                               type="file" 
                                               class="sr-only" 
                                               accept="image/*"
                                               required
                                               @change="uploadFile($event)">
                                    </label>
                                    <span class="text-gray-500"> or drag and drop</span>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                            </div>
                        </div>
                </form>
                
    </div>
</body>
</html>