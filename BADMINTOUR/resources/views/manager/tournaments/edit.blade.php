<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tournament - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        @include('layouts.manager-sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center">
                    <a href="{{ route('manager.tournaments.show', $tournament) }}" class="mr-4 text-gray-600 hover:text-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-black">Edit Tournament</h1>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('manager.tournaments.update', $tournament) }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
                    @csrf
                    @method('PUT')

                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Name</label>
                        <input type="text" name="name" value="{{ old('name', $tournament->name) }}" placeholder="Enter tournament name" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Venue</label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="venue_name" value="{{ old('venue_name', $tournament->venue_name) }}" placeholder="Enter Venue" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('venue_name') border-red-500 @enderror">
                            <input type="number" name="number_of_courts" value="{{ old('number_of_courts', $tournament->number_of_courts) }}" placeholder="Enter # of courts" required min="1" max="50" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('number_of_courts') border-red-500 @enderror">
                        </div>
                        @error('venue_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        @error('number_of_courts') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date', $tournament->start_date->format('Y-m-d')) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('start_date') border-red-500 @enderror">
                            @error('start_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date', $tournament->end_date->format('Y-m-d')) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('end_date') border-red-500 @enderror">
                            @error('end_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">Registration Deadline</label>
                            <input type="date" name="registration_deadline" value="{{ old('registration_deadline', $tournament->registration_deadline->format('Y-m-d')) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('registration_deadline') border-red-500 @enderror">
                            @error('registration_deadline') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-base font-semibold text-black mb-2">Withdrawal Deadline</label>
                            <input type="date" name="withdrawal_deadline" value="{{ old('withdrawal_deadline', $tournament->withdrawal_deadline->format('Y-m-d')) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('withdrawal_deadline') border-red-500 @enderror">
                            @error('withdrawal_deadline') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Description / Overview</label>
                        <textarea name="description" rows="5" placeholder="Write a short overview of the tournament" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('description') border-red-500 @enderror">{{ old('description', $tournament->description) }}</textarea>
                        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_dual_meet" value="1" {{ old('is_dual_meet', $tournament->is_dual_meet) ? 'checked' : '' }} class="w-5 h-5 text-[#2C5F4F] border-gray-300 rounded focus:ring-[#2C5F4F]">
                            <span class="ml-2 text-base font-semibold text-black">Dual Meet Tournament</span>
                        </label>
                        <p class="text-sm text-gray-600 mt-1 ml-7">Check if this is a dual meet tournament between two clubs</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Fee</label>
                        <input type="number" name="tournament_fee" value="{{ old('tournament_fee', $tournament->tournament_fee) }}" placeholder="Enter fee (₱)" required min="0" step="0.01" class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('tournament_fee') border-red-500 @enderror">
                        @error('tournament_fee') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-base font-semibold text-black mb-4">Contact Information</label>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <input type="tel" name="contact_phone" value="{{ old('contact_phone', $tournament->contact_phone) }}" placeholder="Phone" required class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('contact_phone') border-red-500 @enderror">
                            <input type="email" name="contact_email" value="{{ old('contact_email', $tournament->contact_email) }}" placeholder="Email" required class="px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('contact_email') border-red-500 @enderror">
                        </div>
                        @error('contact_phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        @error('contact_email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Messenger / Facebook Link (Optional)</label>
                            <input type="url" name="facebook_link" value="{{ old('facebook_link', $tournament->facebook_link) }}" placeholder="https://facebook.com/managerusername" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] @error('facebook_link') border-red-500 @enderror">
                            @error('facebook_link') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-base font-semibold text-black mb-2">Tournament Banner / Logo</label>
                        @if($tournament->banner_path)
                            <div class="mb-3">
                                <img src="{{ Storage::url($tournament->banner_path) }}" alt="Current Banner" class="h-32 rounded-md">
                                <p class="text-sm text-gray-500 mt-1">Current banner</p>
                            </div>
                        @endif
                        <div class="border border-gray-300 rounded-md p-4 bg-white">
                            <input type="file" name="banner" accept="image/jpeg,image/jpg,image/png" class="w-full">
                            <p class="text-sm text-gray-500 mt-1">Upload a new banner to replace the current one (optional)</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('manager.tournaments.show', $tournament) }}" class="bg-white border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-md font-semibold hover:bg-gray-50 transition duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="bg-[#1B4965] hover:bg-[#143850] text-white px-8 py-3 rounded-md font-semibold transition duration-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>
