<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Manager Profile - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <div class="max-w-6xl mx-auto" x-data="{ activeTab: 'overview' }">
                    <!-- Profile Header Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <!-- Profile Picture and Info -->
                            <div class="flex items-center gap-6">
                                <div class="relative">
                                    @if($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile" class="w-24 h-24 rounded-full object-cover border-4 border-[#D4A574] shadow-md">
                                    @else
                                        <div class="w-24 h-24 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white text-3xl font-bold border-4 border-[#D4A574] shadow-md">
                                            {{ strtoupper(substr($user->first_name ?? 'M', 0, 1) . substr($user->last_name ?? 'N', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ $user->first_name }} {{ $user->last_name }}</h1>
                                    <p class="text-lg text-gray-600 mb-2">{{ $club?->name ?? 'No Club' }}</p>
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 max-w-md">
                                            <span class="flex items-center gap-1 min-w-0">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                                            </span>
                                            @if($user->contact_number)
                                            <span class="flex items-center gap-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $user->contact_number }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                            <!-- Action Button -->
                            <button @click="activeTab = 'edit'" class="px-4 py-2 bg-[#2C5F4F] hover:bg-[#234b3f] text-white rounded-lg font-semibold transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Profile
                            </button>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-1 px-4" aria-label="Tabs">
                                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                                    Overview
                                </button>
                                <button @click="activeTab = 'club'" :class="activeTab === 'club' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                                    Club
                                </button>
                                <button @click="activeTab = 'account'" :class="activeTab === 'account' ? 'border-[#2C5F4F] text-[#2C5F4F]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 border-b-2 font-semibold text-sm transition">
                                    Account Settings
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <!-- Edit Profile Tab -->
                        <div x-show="activeTab === 'edit'" x-cloak>
                            <div class="space-y-6">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">EDIT PROFILE</h3>
                            <form action="{{ route('manager.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                @method('PUT')
                                
                                <!-- Success Message -->
                                @if (session('success'))
                                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                                    </div>
                                @endif

                                <!-- Error Messages -->
                                @if ($errors->any())
                                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                                        <p class="text-sm text-red-700 font-medium mb-2">Please fix the following errors:</p>
                                        <ul class="text-sm text-red-700 list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Profile Picture -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                                    @if($user->profile_photo)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Current Profile" class="w-24 h-24 rounded-full object-cover border-2 border-[#D4A574]">
                                        </div>
                                    @endif
                                    <input 
                                        type="file" 
                                        name="profile_photo" 
                                        accept="image/*"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                    <p class="text-xs text-gray-500 mt-1">Max size: 2MB. Formats: JPEG, PNG, JPG</p>
                                    @error('profile_photo')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- First Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                                    <input 
                                        type="text" 
                                        name="first_name" 
                                        value="{{ old('first_name', $user->first_name) }}" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                    @error('first_name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500">*</span></label>
                                    <input 
                                        type="text" 
                                        name="last_name" 
                                        value="{{ old('last_name', $user->last_name) }}" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                    @error('last_name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Contact Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                                    <input 
                                        type="text" 
                                        name="contact_number" 
                                        value="{{ old('contact_number', $user->contact_number) }}" 
                                        pattern="[0-9+\-\s()]+"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                    @error('contact_number')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-2 rounded-lg font-medium transition">
                                        Update Profile
                                    </button>
                                </div>
                            </form>
                            </div>
                        </div>

                        <!-- Overview Tab -->
                        <div x-show="activeTab === 'overview'">
                        <!-- Manager Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center overflow-hidden">
                                <p class="text-xs text-gray-600 mb-1">EMAIL</p>
                                <p class="text-sm font-bold truncate" title="{{ $user->email }}">{{ $user->email }}</p>
                            </div>
                            <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center overflow-hidden">
                                <p class="text-xs text-gray-600 mb-1">CONTACT</p>
                                <p class="text-sm font-bold truncate">{{ $user->contact_number ?? 'N/A' }}</p>
                            </div>
                            <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center overflow-hidden">
                                <p class="text-xs text-gray-600 mb-1">GENDER</p>
                                <p class="text-lg font-bold">{{ ucfirst($user->gender ?? 'N/A') }}</p>
                            </div>
                            <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center overflow-hidden">
                                <p class="text-xs text-gray-600 mb-1">CLUB MEMBERS</p>
                                <p class="text-lg font-bold">{{ $clubMemberCount ?? 0 }}</p>
                            </div>
                        </div>

                        <!-- Manager Statistics Section -->
                        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-[#2C5F4F]">TOURNAMENT MANAGEMENT STATISTICS</h3>
                                <a href="{{ route('manager.statistics.export') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export Statistics
                                </a>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">TOURNAMENTS ORGANIZED</p>
                                    <p class="text-2xl font-bold text-[#2C5F4F]">{{ $managerStatistics['tournaments_organized'] ?? 0 }}</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">TOTAL PARTICIPANTS</p>
                                    <p class="text-2xl font-bold text-[#2C5F4F]">{{ $managerStatistics['total_participants'] ?? 0 }}</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">MATCHES MANAGED</p>
                                    <p class="text-2xl font-bold text-[#2C5F4F]">{{ $managerStatistics['matches_managed'] ?? 0 }}</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">AVG TOURNAMENT SIZE</p>
                                    <p class="text-2xl font-bold text-[#2C5F4F]">{{ $managerStatistics['average_tournament_size'] ?? 0 }}</p>
                                </div>
                                <div class="border-2 border-[#D4A574] rounded-lg p-4 text-center">
                                    <p class="text-xs text-gray-600 mb-1">COMPLETION RATE</p>
                                    <p class="text-2xl font-bold text-[#2C5F4F]">{{ $managerStatistics['completion_rate'] ?? 0 }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Club Information -->
                        @if($club)
                        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">CLUB INFORMATION</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Club Name</p>
                                    <p class="font-semibold text-lg">{{ $club->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Location</p>
                                    <p class="font-semibold">{{ $club->city }}, {{ $club->province }}</p>
                                </div>
                                @if($club->description)
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600 mb-1">Description</p>
                                    <p class="font-semibold">{{ $club->description }}</p>
                                </div>
                                @endif
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('manager.club') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition">
                                    Manage Club
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 mb-6 text-center">
                            <p class="text-gray-500 mb-4">No club created yet</p>
                            <a href="{{ route('manager.create-club') }}" class="inline-block bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-2 rounded-lg font-semibold transition">
                                Create Your Club
                            </a>
                        </div>
                        @endif
                        </div>

                        <!-- Club Tab -->
                        <div x-show="activeTab === 'club'">
                        @if($club)
                        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                            <h3 class="text-lg font-bold mb-4 text-[#2C5F4F]">CLUB DETAILS</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Club Name</p>
                                    <p class="font-semibold text-lg">{{ $club->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Location</p>
                                    <p class="font-semibold">{{ $club->city }}, {{ $club->province }}</p>
                                </div>
                                @if($club->description)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Description</p>
                                    <p class="font-semibold">{{ $club->description }}</p>
                                </div>
                                @endif
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Members</p>
                                    <p class="font-semibold">{{ $clubMemberCount }} members</p>
                                </div>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('manager.club') }}" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition">
                                    Manage Club
                                </a>
                            </div>
                        </div>
                        @else
                        <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6 text-center">
                            <p class="text-gray-500 mb-4">No club created yet</p>
                            <a href="{{ route('manager.create-club') }}" class="inline-block bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-2 rounded-lg font-semibold transition">
                                Create Your Club
                            </a>
                        </div>
                        @endif
                        </div>

                        <!-- Account Settings Tab -->
                        <div x-show="activeTab === 'account'">
                        <div class="space-y-6">
                            <!-- Success Message -->
                            @if (session('success'))
                                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                                </div>
                            @endif

                            <!-- Error Messages -->
                            @if ($errors->any())
                                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                                    <p class="text-sm text-red-700 font-medium mb-2">Please fix the following errors:</p>
                                    <ul class="text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Update Email Section -->
                            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                                <h3 class="text-lg font-bold mb-4">Update Email Address</h3>
                                <form action="{{ route('manager.account.update-email') }}" method="POST" class="space-y-4">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Email</label>
                                        <input 
                                            type="email" 
                                            value="{{ $user->email }}" 
                                            disabled
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Email Address <span class="text-red-500">*</span></label>
                                        <input 
                                            type="email" 
                                            name="email" 
                                            value="{{ old('email') }}" 
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                        @error('email')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password <span class="text-red-500">*</span></label>
                                        <input 
                                            type="password" 
                                            name="current_password" 
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                        @error('current_password')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-2 rounded-lg font-medium transition">
                                            Update Email
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Update Password Section -->
                            <div class="bg-white border-2 border-[#D4A574] rounded-lg p-6">
                                <h3 class="text-lg font-bold mb-4">Update Password</h3>
                                <form action="{{ route('manager.account.update-password') }}" method="POST" class="space-y-4">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password <span class="text-red-500">*</span></label>
                                        <input 
                                            type="password" 
                                            name="current_password" 
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                        @error('current_password')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-red-500">*</span></label>
                                        <input 
                                            type="password" 
                                            name="password" 
                                            required
                                            minlength="8"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                                        @error('password')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                                        <input 
                                            type="password" 
                                            name="password_confirmation" 
                                            required
                                            minlength="8"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C5F4F]">
                                        @error('password_confirmation')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <button type="submit" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-6 py-2 rounded-lg font-medium transition">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

