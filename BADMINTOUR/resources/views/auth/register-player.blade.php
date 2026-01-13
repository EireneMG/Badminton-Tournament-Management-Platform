<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden" x-data="playerRegistration()" x-init="init()">
    <!-- Back to Home Button -->
    <div class="absolute top-4 left-4 z-10">
        <a href="/" class="inline-flex items-center text-gray-700 hover:text-[#2C5F4F] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Home
        </a>
    </div>
    
    <div class="flex h-full">
        <!-- Left Side - White Background -->
        <div class="w-5/12 bg-white flex flex-col justify-between p-12">
            <div class="flex-1 flex flex-col items-center justify-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Badminton Tournament Management Platform" class="w-48 h-48 mb-6 object-contain">
                <h1 class="text-black text-2xl font-bold text-center leading-tight">
                    Badminton Tournament<br>Management Platform
                </h1>
            </div>
            <div class="text-black text-sm">
                Already have an account? 
                <a href="{{ route('login.select') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center p-8">
            <div class="w-full max-w-2xl px-8">
                <h2 class="text-[#7B1F3C] text-5xl font-bold mb-8 text-center">Register</h2>
                
                <!-- Error Messages (only for Step 1 fields, excluding email/password) -->
                @php
                    $step1Errors = array_filter(
                        $errors->getMessages(),
                        function ($messages, $field) {
                            return !in_array($field, ['email', 'password', 'password_confirmation'], true);
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                @endphp
                @if (!empty($step1Errors))
                    <div class="bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-md mb-4">
                        <p class="font-semibold mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($step1Errors as $fieldErrors)
                                @foreach ($fieldErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="player">

                    <!-- Step 1 -->
                    <div id="step1" class="space-y-4">
                        <!-- First Name -->
                        <div>
                            <input 
                                type="text" 
                                name="first_name" 
                                placeholder="First name"
                                value="{{ old('first_name') }}"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Middle Name and Last Name -->
                        <div class="grid grid-cols-2 gap-4">
                            <input 
                                type="text" 
                                name="middle_name" 
                                placeholder="Middle name"
                                value="{{ old('middle_name') }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            <input 
                                type="text" 
                                name="last_name" 
                                placeholder="Last name"
                                value="{{ old('last_name') }}"
                                required
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <input 
                                type="text" 
                                name="contact_number" 
                                placeholder="Contact number"
                                value="{{ old('contact_number') }}"
                                required
                                pattern="[0-9()+\-\s]+"
                                title="Please enter a valid contact number"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('contact_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birth Date -->
                        <div class="grid grid-cols-3 gap-4">
                            <select 
                                name="birth_month"
                                required
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Birth month</option>
                                <option value="1" {{ old('birth_month') == '1' ? 'selected' : '' }}>January</option>
                                <option value="2" {{ old('birth_month') == '2' ? 'selected' : '' }}>February</option>
                                <option value="3" {{ old('birth_month') == '3' ? 'selected' : '' }}>March</option>
                                <option value="4" {{ old('birth_month') == '4' ? 'selected' : '' }}>April</option>
                                <option value="5" {{ old('birth_month') == '5' ? 'selected' : '' }}>May</option>
                                <option value="6" {{ old('birth_month') == '6' ? 'selected' : '' }}>June</option>
                                <option value="7" {{ old('birth_month') == '7' ? 'selected' : '' }}>July</option>
                                <option value="8" {{ old('birth_month') == '8' ? 'selected' : '' }}>August</option>
                                <option value="9" {{ old('birth_month') == '9' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ old('birth_month') == '10' ? 'selected' : '' }}>October</option>
                                <option value="11" {{ old('birth_month') == '11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{ old('birth_month') == '12' ? 'selected' : '' }}>December</option>
                            </select>
                            <input 
                                type="number" 
                                name="birth_day" 
                                placeholder="Day"
                                value="{{ old('birth_day') }}"
                                required
                                min="1" 
                                max="31"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('birth_day')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <input 
                                type="number" 
                                name="birth_year" 
                                placeholder="Year"
                                value="{{ old('birth_year') }}"
                                required
                                min="1950" 
                                max="{{ now()->year }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('birth_year')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Region, Province, City -->
                        <div class="grid grid-cols-3 gap-4">
                            <select 
                                name="region"
                                x-model="selectedRegion"
                                @change="onRegionChange()"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                                required
                            >
                                <option value="">Region</option>
                                <template x-for="region in regions" :key="region.code">
                                    <option :value="region.code" x-text="region.name"></option>
                                </template>
                            </select>
                            <!-- Hidden field to ensure province is submitted for NCR -->
                            <template x-if="selectedRegion === 'NCR'">
                                <input type="hidden" name="province" value="N/A">
                            </template>
                            <select 
                                name="province"
                                x-model="selectedProvince"
                                @change="onProvinceChange()"
                                :disabled="!selectedRegion || selectedRegion === 'NCR'"
                                :required="selectedRegion !== 'NCR'"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            >
                                <option value="">Province</option>
                                <option x-show="selectedRegion === 'NCR'" value="N/A">N/A</option>
                                <template x-for="province in availableProvinces" :key="province">
                                    <option :value="province" x-text="province"></option>
                                </template>
                            </select>
                            <select 
                                name="city"
                                x-model="selectedCity"
                                :disabled="!selectedProvince && selectedRegion !== 'NCR'"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                                required
                            >
                                <option value="">City</option>
                                <template x-for="city in availableCities" :key="city">
                                    <option :value="city" x-text="city"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Gender and Height -->
                        <div class="grid grid-cols-2 gap-4">
                            <select 
                                name="gender"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                                required
                            >
                                <option value="">Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            <input 
                                type="number" 
                                name="height" 
                                placeholder="Height (cm)"
                                value="{{ old('height') }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                                required
                                min="100"
                                max="250"
                            >
                        </div>

                        <!-- Weight -->
                        <div>
                            <input 
                                type="number" 
                                name="weight" 
                                placeholder="Enter your weight (kg)"
                                value="{{ old('weight') }}"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                                required
                                min="30"
                                max="200"
                                step="0.1"
                            >
                        </div>

                        <!-- Next Button -->
                        <div class="pt-4 flex justify-center">
                            <button 
                                type="button"
                                onclick="showStep2()"
                                class="bg-[#7B1F3C] text-white font-semibold py-2.5 px-16 rounded-md hover:bg-[#5D1730] transition duration-200"
                            >
                                Next
                            </button>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div id="step2" class="space-y-4 hidden">
                        <!-- Email Address -->
                        <div>
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="Email Address"
                                value="{{ old('email') }}"
                                required
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="Create password (minimum 8 characters)"
                                required
                                minlength="8"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                placeholder="Confirm password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            @error('password_confirmation')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 max-h-60 overflow-y-auto">
                            <h3 class="font-semibold text-gray-900 mb-3 text-sm">Terms & Conditions / NDA</h3>
                            <div class="text-xs text-gray-700 space-y-2">
                                <p>By creating an account, you agree to the following terms:</p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li>I understand that participation in badminton tournaments involves physical activity and potential risk of injury.</li>
                                    <li>I agree to follow all tournament rules and regulations set by the organizing club.</li>
                                    <li>I consent to the use of my personal information for tournament management and communication purposes.</li>
                                    <li>I acknowledge that tournament fees are non-refundable once registration is approved.</li>
                                    <li>I agree to maintain sportsmanlike conduct and respect all participants, officials, and organizers.</li>
                                </ul>
                            </div>
                            <div class="mt-4 flex items-start">
                                <input 
                                    type="checkbox" 
                                    id="accept_terms" 
                                    name="accept_terms" 
                                    value="1" 
                                    required
                                    class="mt-1 h-4 w-4 text-[#7B1F3C] border-gray-300 rounded focus:ring-[#7B1F3C]"
                                >
                                <label for="accept_terms" class="ml-2 text-xs text-gray-700">
                                    I have read and accept the Terms & Conditions / NDA
                                </label>
                            </div>
                            @error('accept_terms')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-6 flex justify-center space-x-4">
                            <button 
                                type="button"
                                onclick="showStep1()"
                                class="bg-gray-500 text-white font-semibold py-2.5 px-16 rounded-md hover:bg-gray-600 transition duration-200"
                            >
                                Back
                            </button>
                            <button 
                                type="submit"
                                class="bg-[#7B1F3C] text-white font-semibold py-2.5 px-16 rounded-md hover:bg-[#5D1730] transition duration-200"
                            >
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle required attributes based on active step to avoid hidden-field validation errors
        function setRequiredForStep(step) {
            const step1Fields = [
                'first_name','last_name','contact_number','birth_month','birth_day','birth_year',
                'gender','height','weight','region','province','city'
            ];
            const step2Fields = ['email','password','password_confirmation'];

            step1Fields.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.required = (step === 1);
            });
            step2Fields.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.required = (step === 2);
            });
        }

        // Check which step to show based on validation errors
        document.addEventListener('DOMContentLoaded', function() {
            const step1HasErrors = {{ $errors->has('first_name') || $errors->has('middle_name') || $errors->has('last_name') || $errors->has('contact_number') || $errors->has('birth_month') || $errors->has('birth_day') || $errors->has('birth_year') || $errors->has('gender') || $errors->has('height') || $errors->has('weight') || $errors->has('region') || $errors->has('province') || $errors->has('city') ? 'true' : 'false' }};
            const step2HasErrors = {{ $errors->has('email') || $errors->has('password') || $errors->has('password_confirmation') ? 'true' : 'false' }};

            if (step2HasErrors) {
                showStep2();
                setRequiredForStep(2);
            } else if (step1HasErrors) {
                showStep1();
                setRequiredForStep(1);
            } else {
                setRequiredForStep(1);
            }
        });

        function showStep2() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            setRequiredForStep(2);
        }

        function showStep1() {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
            setRequiredForStep(1);
        }

        function playerRegistration() {
            return {
                selectedRegion: '{{ old("region") }}',
                selectedProvince: '{{ old("province") }}',
                selectedCity: '{{ old("city") }}',
                availableProvinces: [],
                availableCities: [],
                
                regions: [
                    { code: 'NCR', name: 'NCR' },
                    { code: 'CAR', name: 'CAR – Cordillera Administrative Region' },
                    { code: 'R1', name: 'Region I – Ilocos Region' },
                    { code: 'R2', name: 'Region II – Cagayan Valley' },
                    { code: 'R3', name: 'Region III – Central Luzon' },
                    { code: 'R4A', name: 'Region IV-A – CALABARZON' },
                    { code: 'R4B', name: 'Region IV-B – MIMAROPA' },
                    { code: 'R5', name: 'Region V – Bicol Region' }
                ],
                
                regionData: {
                    'NCR': {
                        provinces: [],
                        cities: ['Manila', 'Quezon City', 'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque', 'Pasay', 'Pasig', 'San Juan', 'Taguig', 'Valenzuela']
                    },
                    'CAR': {
                        provinces: ['Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'],
                        cities: {
                            'Abra': [],
                            'Apayao': [],
                            'Benguet': ['Baguio'],
                            'Ifugao': [],
                            'Kalinga': ['Tabuk'],
                            'Mountain Province': []
                        }
                    },
                    'R1': {
                        provinces: ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
                        cities: {
                            'Ilocos Norte': ['Laoag', 'Batac'],
                            'Ilocos Sur': ['Vigan', 'Candon'],
                            'La Union': ['San Fernando'],
                            'Pangasinan': ['Alaminos', 'Dagupan', 'San Carlos', 'Urdaneta']
                        }
                    },
                    'R2': {
                        provinces: ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
                        cities: {
                            'Batanes': [],
                            'Cagayan': ['Tuguegarao'],
                            'Isabela': ['Ilagan', 'Cauayan', 'Santiago'],
                            'Nueva Vizcaya': [],
                            'Quirino': []
                        }
                    },
                    'R3': {
                        provinces: ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'],
                        cities: {
                            'Aurora': [],
                            'Bataan': ['Balanga'],
                            'Bulacan': ['Baliwag', 'Malolos', 'Meycauayan', 'San Jose del Monte'],
                            'Nueva Ecija': ['Cabanatuan', 'Gapan', 'Science City of Muñoz', 'Palayan', 'San Jose (NE)'],
                            'Pampanga': ['Angeles', 'Mabalacat', 'San Fernando (Pampanga)'],
                            'Tarlac': ['Tarlac City'],
                            'Zambales': ['Olongapo']
                        }
                    },
                    'R4A': {
                        provinces: ['Cavite', 'Laguna', 'Batangas', 'Rizal', 'Quezon'],
                        cities: {
                            'Cavite': ['Cavite City', 'Tagaytay', 'Trece Martires', 'Dasmariñas', 'Imus', 'Bacoor', 'General Trias'],
                            'Laguna': ['Biñan', 'Cabuyao', 'Calamba', 'San Pablo', 'Santa Rosa', 'San Pedro'],
                            'Batangas': ['Batangas City', 'Lipa', 'Tanauan', 'Santo Tomas'],
                            'Rizal': ['Antipolo'],
                            'Quezon': ['Lucena', 'Tayabas']
                        }
                    },
                    'R4B': {
                        provinces: ['Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon'],
                        cities: {
                            'Marinduque': [],
                            'Occidental Mindoro': [],
                            'Oriental Mindoro': ['Calapan'],
                            'Palawan': ['Puerto Princesa'],
                            'Romblon': []
                        }
                    },
                    'R5': {
                        provinces: ['Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon'],
                        cities: {
                            'Albay': ['Legazpi', 'Ligao'],
                            'Camarines Norte': [],
                            'Camarines Sur': ['Naga', 'Iriga'],
                            'Catanduanes': [],
                            'Masbate': ['Masbate City'],
                            'Sorsogon': ['Sorsogon City']
                        }
                    }
                },
                
                init() {
                    // Initialize dropdowns with old values if they exist
                    if (this.selectedRegion) {
                        if (this.selectedRegion === 'NCR') {
                            this.availableProvinces = [];
                            this.availableCities = this.regionData['NCR'].cities;
                        } else if (this.regionData[this.selectedRegion]) {
                            this.availableProvinces = this.regionData[this.selectedRegion].provinces;
                            if (this.selectedProvince && this.regionData[this.selectedRegion].cities[this.selectedProvince]) {
                                this.availableCities = this.regionData[this.selectedRegion].cities[this.selectedProvince];
                            }
                        }
                    }
                },
                
                onRegionChange() {
                    this.selectedProvince = '';
                    this.selectedCity = '';
                    this.availableCities = [];
                    
                    if (this.selectedRegion === 'NCR') {
                        this.availableProvinces = [];
                        this.selectedProvince = 'N/A';
                        this.availableCities = this.regionData['NCR'].cities;
                    } else if (this.selectedRegion) {
                        this.availableProvinces = this.regionData[this.selectedRegion].provinces;
                    } else {
                        this.availableProvinces = [];
                    }
                },
                
                onProvinceChange() {
                    this.selectedCity = '';
                    
                    if (this.selectedProvince && this.selectedRegion !== 'NCR') {
                        this.availableCities = this.regionData[this.selectedRegion].cities[this.selectedProvince] || [];
                    } else {
                        this.availableCities = [];
                    }
                }
            }
        }
    </script>
</body>
</html>
