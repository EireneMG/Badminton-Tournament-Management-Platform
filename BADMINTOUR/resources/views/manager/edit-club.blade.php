<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    clubLogo: null,
    clubLogoPreview: '{{ $club->logo ? asset('storage/' . $club->logo) : '' }}',
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
        this.clubLogoPreview = '';
        document.getElementById('logo-upload').value = '';
    }
}">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.manager-sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center">
                    <a href="{{ route('manager.club') }}" class="mr-4 text-gray-600 hover:text-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-semibold text-[#2C5F4F]">Edit Club Information</h1>
                        <p class="text-sm text-gray-500">Update your club's details</p>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-red-800">
                            <p class="font-semibold mb-1">Please fix the following errors:</p>
                            <ul class="list-disc list-inside space-y-1 text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-white rounded-lg shadow-sm p-8 border border-gray-200 max-w-4xl">
                    <form action="{{ route('manager.club.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Club Name *</label>
                                    <input type="text" name="name" value="{{ old('name', $club->name) }}" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="4"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F] resize-none">{{ old('description', $club->description) }}</textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Province *</label>
                                        <input type="text" name="province" value="{{ old('province', $club->province) }}" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                        <input type="text" name="city" value="{{ old('city', $club->city) }}" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $club->contact_email) }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $club->contact_phone) }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Club Logo</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#2C5F4F] transition-colors duration-200">
                                    <template x-if="clubLogoPreview">
                                        <div class="relative">
                                            <img :src="clubLogoPreview" alt="Club Logo Preview" class="max-h-48 mx-auto rounded-lg shadow-md">
                                            <button type="button" @click="removeLogo()" 
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!clubLogoPreview">
                                        <div>
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-600">Click to upload a new logo</p>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                        </div>
                                    </template>
                                    <input type="file" name="logo" id="logo-upload" accept="image/*" class="hidden" @change="uploadLogo">
                                    <label for="logo-upload" class="mt-4 inline-block cursor-pointer">
                                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                            Choose File
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                            <a href="{{ route('manager.club') }}" 
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-6 py-3 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#244D3E] transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
