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

                        <!-- Image Preview -->
                        <div x-show="idImagePreview" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="relative">
                            <div class="border-2 border-[#2C5F4F] rounded-lg p-4 bg-gray-50">
                                <img :src="idImagePreview" 
                                     alt="ID Preview" 
                                     class="w-full h-64 object-contain rounded">
                                <button type="button" 
                                        @click="removeImage()"
                                        class="absolute top-6 right-6 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                ID uploaded successfully
                            </p>
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Important Information</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-600">
                                    <li>Accepted documents: National ID, Driver's License, Passport</li>
                                    <li>Ensure all details are clearly visible</li>
                                    <li>Your information will be kept secure and confidential</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </form>

    </div>
</body>
</html>