<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Tournament - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{
    categoryCount: 0,
    numberOfCourts: {{ $prefilledData['number_of_courts'] ?? 4 }},
    categories: [],
    bracketType: '{{ $prefilledData['bracket_type'] ?? 'single_elimination' }}',
    showCategorySelection: true,
    categoryMode: 'all', // 'all' or 'single'
    selectedSingleCategory: 'MS',
    
    calculateMatchesForBracket(slots, bracketType) {
        if (bracketType === 'single_elimination') {
            return slots - 1;
        } else if (bracketType === 'round_robin') {
            return (slots * (slots - 1)) / 2;
        }
        return slots - 1;
    },
    
    calculateRoundsForBracket(slots, bracketType) {
        const rounds = [];
        
        if (bracketType === 'single_elimination') {
            const numRounds = Math.ceil(Math.log2(slots));
            let remainingMatches = slots;
            const roundNames = ['Round 1', 'Round of 16', 'Quarterfinals', 'Semifinals', 'Finals'];
            
            for (let i = 0; i < numRounds; i++) {
                const matchesInRound = Math.ceil(remainingMatches / 2);
                const roundName = roundNames[i] || 'Round ' + (i + 1);
                
                rounds.push({
                    name: roundName,
                    matches: matchesInRound,
                });
                
                remainingMatches = matchesInRound;
            }
        } else { // round_robin
            const numRounds = slots - 1;
            const matchesPerRound = Math.floor(slots / 2);
            
            for (let i = 0; i < numRounds; i++) {
                rounds.push({
                    name: 'Round ' + (i + 1),
                    matches: matchesPerRound,
                });
            }
        }
        
        return rounds;
    },
    
    addCategory() {
        const newCategory = {
            id: Date.now(),
            type: '',
            slots: 16,
            skill_level: 'Open',
            age_bracket: 'Open',
            schedule_start_time: '09:00',
            match_duration_minutes: 45,
            break_between_matches_minutes: 5
        };
        this.categories.push(newCategory);
        this.categoryCount = this.categories.length;
    },
    
    removeCategory(index) {
        this.categories.splice(index, 1);
        this.categoryCount = this.categories.length;
    },
    
    updateCategory(index) {
        const category = this.categories[index];
        if (!category) return;
        
        // Auto-set match duration based on category type
        if (category.type === 'MS' || category.type === 'WS') {
            category.match_duration_minutes = 45; // Singles: 45 minutes
        } else if (category.type === 'MD' || category.type === 'WD' || category.type === 'XD') {
            category.match_duration_minutes = 60; // Doubles/Mixed: 60 minutes
        }
    },
    
    updateCourts() {
        const courtsInput = document.querySelector('input[name=number_of_courts]');
        if (courtsInput) {
            this.numberOfCourts = parseInt(courtsInput.value) || 1;
        }
    },
    
    generateCategories() {
        if (this.categoryMode === 'all') {
            // Pre-fill with all standard categories
            this.categories = [
                { id: 1, type: 'MS', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 45, break_between_matches_minutes: 5 },
                { id: 2, type: 'WS', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '10:00', match_duration_minutes: 45, break_between_matches_minutes: 5 },
                { id: 3, type: 'MD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '11:00', match_duration_minutes: 60, break_between_matches_minutes: 5 },
                { id: 4, type: 'WD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '12:00', match_duration_minutes: 60, break_between_matches_minutes: 5 },
                { id: 5, type: 'XD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '13:00', match_duration_minutes: 60, break_between_matches_minutes: 5 },
            ];
        } else {
            // Pre-fill with only the selected single category
            const categoryConfig = {
                'MS': { type: 'MS', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 45 },
                'WS': { type: 'WS', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 45 },
                'MD': { type: 'MD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 60 },
                'WD': { type: 'WD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 60 },
                'XD': { type: 'XD', slots: 16, skill_level: 'Open', age_bracket: 'Open', schedule_start_time: '09:00', match_duration_minutes: 60 },
            };
            const config = categoryConfig[this.selectedSingleCategory] || categoryConfig['MS'];
            this.categories = [
                { id: 1, ...config, break_between_matches_minutes: 5 }
            ];
        }
        this.categoryCount = this.categories.length;
        this.showCategorySelection = false;
    },
    
    init() {
        // Category selection modal will show first, categories will be generated when user confirms
        
        this.$nextTick(() => {
            const courtsInput = document.querySelector('input[name=number_of_courts]');
            if (courtsInput) {
                this.numberOfCourts = parseInt(courtsInput.value) || 4;
                courtsInput.addEventListener('input', () => this.updateCourts());
            }
            
            const bracketTypeSelect = document.querySelector('select[name=bracket_type]');
            if (bracketTypeSelect) {
                const self = this;
                bracketTypeSelect.addEventListener('change', function(e) {
                    self.bracketType = e.target.value;
                });
            }
        });
    }
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center">
                    <a href="/manager/tournaments" class="mr-4 text-gray-600 hover:text-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-black">Generate Tournament</h1>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @if($canCreateTournament)
                <!-- Category Selection Modal -->
                <div x-show="showCategorySelection" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6"
                         @click.away="showCategorySelection = false">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Select Categories</h3>
                            <p class="text-sm text-gray-600">Choose how many categories to generate for this tournament.</p>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <!-- All Categories Option -->
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="categoryMode === 'all' ? 'border-[#2C5F4F] bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       x-model="categoryMode" 
                                       value="all" 
                                       class="mt-1 mr-3 text-[#2C5F4F] focus:ring-[#2C5F4F]">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Generate All Categories</div>
                                    <div class="text-sm text-gray-600 mt-1">Pre-fills all 5 standard categories (MS, WS, MD, WD, XD)</div>
                                </div>
                            </label>
                            
                            <!-- Single Category Option -->
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="categoryMode === 'single' ? 'border-[#2C5F4F] bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       x-model="categoryMode" 
                                       value="single" 
                                       class="mt-1 mr-3 text-[#2C5F4F] focus:ring-[#2C5F4F]">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">Generate Single Category</div>
                                    <div class="text-sm text-gray-600 mt-1">Pre-fills only one selected category</div>
                                    <div x-show="categoryMode === 'single'" 
                                         x-transition
                                         class="mt-3">
                                        <select x-model="selectedSingleCategory" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F] text-sm">
                                            <option value="MS">Men's Singles</option>
                                            <option value="WS">Women's Singles</option>
                                            <option value="MD">Men's Doubles</option>
                                            <option value="WD">Women's Doubles</option>
                                            <option value="XD">Mixed Doubles</option>
                                        </select>
                                    </div>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button type="button" 
                                    @click="showCategorySelection = false; generateCategories()"
                                    class="px-6 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-md font-medium transition duration-200">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!$canCreateTournament)
                    <!-- Insufficient Players Message -->
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Cannot Generate Tournament</h3>
                                    <div class="text-sm text-yellow-700">
                                        <p class="mb-3">
                                            Your club must have at least <strong>5 active players</strong> before you can generate a tournament.
                                        </p>
                                        <div class="bg-white rounded-md p-4 border border-yellow-200">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-medium text-gray-900">Current Active Players:</span>
                                                <span class="text-2xl font-bold {{ $activePlayersCount >= 5 ? 'text-green-600' : 'text-yellow-600' }}">
                                                    {{ $activePlayersCount }}
                                                </span>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-yellow-200">
                                                <p class="text-sm text-gray-600 mb-2">
                                                    <strong>Required:</strong> 5 active players
                                                </p>
                                                <div class="w-full bg-yellow-200 rounded-full h-2.5">
                                                    <div class="bg-yellow-600 h-2.5 rounded-full" style="width: {{ min(($activePlayersCount / 5) * 100, 100) }}%"></div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    {{ max(0, 5 - $activePlayersCount) }} more player(s) needed
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <a href="{{ route('manager.club') }}" class="inline-flex items-center px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white font-medium rounded-md transition duration-200">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                Go to My Club to Invite Players
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="list-disc list-inside mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <!-- Info Banner -->
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800 mb-1">Quick Tournament Generation</h4>
                                <p class="text-xs text-blue-700">This form is pre-filled with standard tournament settings. You can modify any field as needed before creating your tournament.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tournament Creation Form -->
                    <form action="{{ route('manager.tournaments.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl" x-show="!showCategorySelection" x-transition>
                        @csrf
                        
                        <!-- Tournament Name -->
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-black mb-2">Tournament Name</label>
                            <input type="text" name="name" placeholder="Enter tournament name" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>

                        <!-- Venue Row -->
                        <div class="mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-base font-semibold text-black mb-2">Venue</label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            id="venue_name" 
                                            name="venue_name" 
                                            placeholder="Enter Venue (start typing for suggestions)" 
                                            required 
                                            autocomplete="off"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]"
                                        >
                                        <!-- Autocomplete Dropdown -->
                                        <div id="venue-autocomplete" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                            <!-- Suggestions will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-base font-semibold text-black mb-2"># of Courts</label>
                                    <input type="number" name="number_of_courts" placeholder="Enter # of courts" required min="1" max="50" value="{{ $prefilledData['number_of_courts'] ?? 4 }}" x-model="numberOfCourts" @input="updateCourts()" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>
                        </div>

                        <!-- Dates Row -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-base font-semibold text-black mb-2">Start Date</label>
                                <input type="date" name="start_date" value="{{ $prefilledData['start_date'] ?? '' }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                            <div>
                                <label class="block text-base font-semibold text-black mb-2">End Date</label>
                                <input type="date" name="end_date" value="{{ $prefilledData['end_date'] ?? '' }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                        </div>

                        <!-- Deadlines Row -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-base font-semibold text-black mb-2">Registration Deadline</label>
                                <input type="date" name="registration_deadline" value="{{ $prefilledData['registration_deadline'] ?? '' }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                            <div>
                                <label class="block text-base font-semibold text-black mb-2">Withdrawal Deadline</label>
                                <input type="date" name="withdrawal_deadline" value="{{ $prefilledData['withdrawal_deadline'] ?? '' }}" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-black mb-2">Description / Overview</label>
                            <textarea name="description" rows="5" placeholder="Write a short overview of the tournament" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]"></textarea>
                        </div>

                        <!-- Tournament Format -->
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-black mb-2">Tournament Format</label>
                            <select name="bracket_type" x-model="bracketType" required class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                <option value="single_elimination" {{ ($prefilledData['bracket_type'] ?? 'single_elimination') === 'single_elimination' ? 'selected' : '' }}>Single Elimination</option>
                                <option value="round_robin" {{ ($prefilledData['bracket_type'] ?? '') === 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                            </select>
                            <p class="text-sm text-gray-600 mt-1">Select the tournament bracket format.</p>
                        </div>

                        <!-- Tournament Type -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_dual_meet" value="1" class="w-5 h-5 text-[#2C5F4F] border-gray-300 rounded focus:ring-[#2C5F4F]">
                                <span class="ml-2 text-base font-semibold text-black">Dual Meet Tournament</span>
                            </label>
                            <p class="text-sm text-gray-600 mt-1 ml-7">Check if this is a dual meet tournament between two clubs</p>
                        </div>

                        <!-- Category Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-base font-semibold text-black">Categories</label>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-gray-500" x-text="categoryCount + ' added'"></span>
                                    <button type="button" @click="addCategory()" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                        + Add Category
                                    </button>
                                </div>
                            </div>
                            <div class="bg-white border border-gray-300 rounded-md p-4 space-y-4">
                                <template x-for="(category, index) in categories" :key="category.id">
                                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                                        <div class="grid grid-cols-5 gap-3 items-end">
                                            <select :name="'categories[' + index + '][type]'" x-model="category.type" @change="updateCategory(index)" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                                <option value="MS">Men's Singles</option>
                                                <option value="WS">Women's Singles</option>
                                                <option value="MD">Men's Doubles</option>
                                                <option value="WD">Women's Doubles</option>
                                                <option value="XD">Mixed Doubles</option>
                                            </select>
                                            <input type="number" :name="'categories[' + index + '][slots]'" x-model="category.slots" @change="updateCategory(index)" placeholder="Slots (min: 12)" required min="12" max="128" class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                            <select :name="'categories[' + index + '][skill_level_requirements]'" x-model="category.skill_level" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                                <option value="">Select Skill Level</option>
                                                <option value="A">Level A</option>
                                                <option value="B">Level B</option>
                                                <option value="C">Level C</option>
                                                <option value="D">Level D</option>
                                                <option value="Open">Open for Everyone</option>
                                            </select>
                                            <select :name="'categories[' + index + '][age_requirement]'" x-model="category.age_bracket" required class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                                <option value="">Select Age Bracket</option>
                                                <option value="Junior (13-16)">Junior (13-16)</option>
                                                <option value="Senior (17-35)">Senior (17-35)</option>
                                                <option value="Veteran (36-50)">Veteran (36-50)</option>
                                                <option value="Master (51+)">Master (51+)</option>
                                                <option value="Open">Open (All Ages)</option>
                                            </select>
                                            <button type="button" @click="removeCategory(index)" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                                Remove
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-3 gap-3 items-end">
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Category Start Time</label>
                                                <input type="time" :name="'categories[' + index + '][schedule_start_time]'" x-model="category.schedule_start_time" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Match Duration (minutes)</label>
                                                <select :name="'categories[' + index + '][match_duration_minutes]'" x-model="category.match_duration_minutes" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                                    <option value="30">30 minutes</option>
                                                    <option value="45">45 minutes</option>
                                                    <option value="60">60 minutes</option>
                                                    <option value="75">75 minutes</option>
                                                    <option value="90">90 minutes</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Break Between Matches (minutes)</label>
                                                <input type="number" :name="'categories[' + index + '][break_between_matches_minutes]'" x-model="category.break_between_matches_minutes" min="0" max="30" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-black mb-4">Contact Information</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-base font-semibold text-black mb-2">Contact Email</label>
                                    <input type="email" name="contact_email" value="{{ $prefilledData['contact_email'] ?? '' }}" placeholder="contact@example.com" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                                <div>
                                    <label class="block text-base font-semibold text-black mb-2">Contact Phone</label>
                                    <input type="text" name="contact_phone" value="{{ $prefilledData['contact_phone'] ?? '' }}" placeholder="+63 912 345 6789" required class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                </div>
                            </div>
                        </div>

                        <!-- Tournament Fee -->
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-black mb-2">Tournament Fee (₱)</label>
                            <input type="number" name="tournament_fee" value="{{ $prefilledData['tournament_fee'] ?? 500 }}" placeholder="500" min="0" step="0.01" required class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>

                        <!-- Facebook Link -->
                        <div class="mb-6">
                            <label class="block text-base font-semibold text-black mb-2">Facebook Event Link (Optional)</label>
                            <input type="url" name="facebook_link" placeholder="https://facebook.com/events/..." class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4 mt-8">
                            <a href="/manager/tournaments" class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium transition duration-200">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-3 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-md font-medium transition duration-200">
                                Create Tournament
                            </button>
                        </div>
                    </form>
                @endif
            </main>
        </div>
    </div>

    <!-- Venue Autocomplete Script (same as create.blade.php) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const venueInput = document.getElementById('venue_name');
            const autocompleteDiv = document.getElementById('venue-autocomplete');
            
            if (!venueInput || !autocompleteDiv) return;
            
            let timeout;
            const venues = @json(\App\Models\Tournament::distinct()->pluck('venue_name')->filter()->values()->toArray());
            
            venueInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value.trim().toLowerCase();
                
                if (query.length < 2) {
                    autocompleteDiv.classList.add('hidden');
                    return;
                }
                
                timeout = setTimeout(() => {
                    const matches = venues.filter(v => v.toLowerCase().includes(query)).slice(0, 5);
                    
                    if (matches.length === 0) {
                        autocompleteDiv.classList.add('hidden');
                        return;
                    }
                    
                    autocompleteDiv.innerHTML = matches.map(venue => 
                        `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" onclick="selectVenue('${venue.replace(/'/g, "\\'")}')">${venue}</div>`
                    ).join('');
                    autocompleteDiv.classList.remove('hidden');
                }, 300);
            });
            
            window.selectVenue = function(venue) {
                venueInput.value = venue;
                autocompleteDiv.classList.add('hidden');
            };
            
            document.addEventListener('click', function(e) {
                if (!venueInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
                    autocompleteDiv.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>

