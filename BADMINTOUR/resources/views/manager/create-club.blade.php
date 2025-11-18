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

               <!-- Form -->
                <form action="{{ route('manager.create-club.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <!-- Club Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Club Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="club_name"
                               placeholder="Enter your club name"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                    </div>

                    <!-- Club Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Club Description <span class="text-red-500">*</span></label>
                        <textarea name="club_description"
                                  rows="4"
                                  placeholder="Describe your club's mission, values, and what makes it unique..."
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimum 50 characters</p>
                    </div>

                    <!-- Two Column Grid: Club Logo and Location -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Club Logo -->
                        <div class="space-y-4">
                            <label class="block text-sm font-semibold text-gray-700">Club Logo <span class="text-red-500">*</span></label>
                            
                            <!-- Upload Area -->
                            <div x-show="!clubLogoPreview" 
                                 class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#2C5F4F] transition-colors duration-200">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <label for="logo-upload" class="mt-3 cursor-pointer block">
                                    <span class="text-[#2C5F4F] hover:text-[#244D3E] font-semibold">Upload Logo</span>
                                    <input id="logo-upload" 
                                           name="club_logo"
                                           type="file" 
                                           class="sr-only" 
                                           accept="image/*"
                                           required
                                           @change="uploadLogo($event)">
                                </label>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG (Max 5MB)</p>
                            </div>

                            <!-- Logo Preview -->
                            <div x-show="clubLogoPreview" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="relative">
                                <div class="border-2 border-[#2C5F4F] rounded-lg p-4 bg-gray-50">
                                    <img :src="clubLogoPreview" 
                                         alt="Logo Preview" 
                                         class="w-full h-40 object-contain rounded">
                                    <button type="button" 
                                            @click="removeLogo()"
                                            class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 shadow-lg transition duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Club Location -->
                        <div class="space-y-4">
                            <label class="block text-sm font-semibold text-gray-700">Club Location <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="club_location"
                                   placeholder="City, Province"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                            
                            <div class="space-y-3 pt-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Information</label>
                                
                                <input type="email" 
                                       name="club_email"
                                       placeholder="Club Email Address"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                                
                                <input type="tel" 
                                       name="club_phone"
                                       placeholder="Club Contact Number"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F] focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-green-50 border-l-4 border-[#2C5F4F] p-4 rounded">
                        <div class="flex">
                            <svg class="h-5 w-5 text-[#2C5F4F] mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-green-800">
                                <p class="font-semibold mb-1">What happens next?</p>
                                <ul class="list-disc list-inside space-y-1 text-green-700">
                                    <li>Your club will be reviewed by our admin team</li>
                                    <li>You'll receive a notification once approved (usually within 24 hours)</li>
                                    <li>You can then start inviting players and managing tournaments</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                     <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <a href="{{ route('manager.verify-id') }}" 
                           class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold text-center transition duration-200">
                            Back
                        </a>
                        <button type="submit" 
                                class="flex-1 bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                            Create Club
                        </button>
                    </div>
                </form>

                <!-- Help Text -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        Questions? <a href="#" class="text-[#2C5F4F] hover:text-[#244D3E] font-semibold">View Club Guidelines</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
