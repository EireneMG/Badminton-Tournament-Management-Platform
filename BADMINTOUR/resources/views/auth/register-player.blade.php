<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden" x-data="playerRegistration()" x-init="init()">
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
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-400 text-red-800 px-4 py-3 rounded-md mb-4">
                        <p class="font-semibold mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
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
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <input 
                                type="text" 
                                name="contact_number" 
                                placeholder="Contact number"
                                value="{{ old('contact_number') }}"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Birth Date -->
                        <div class="grid grid-cols-3 gap-4">
                            <select 
                                name="birth_month"
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
                                type="text" 
                                name="birth_day" 
                                placeholder="Day"
                                value="{{ old('birth_day') }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                            <input 
                                type="text" 
                                name="birth_year" 
                                placeholder="Year"
                                value="{{ old('birth_year') }}"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
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
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password" 
                                placeholder="Create password"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                placeholder="Confirm password"
                                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-700 placeholder-gray-400 text-sm"
                            >
                        </div>

                        <!-- Back and Create Buttons -->
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
        // Check which step to show based on validation errors
        document.addEventListener('DOMContentLoaded', function() {
            // Step 1 fields: first_name, middle_name, last_name, contact_number, birth_month, birth_day, birth_year, gender, height, weight, region, province, city
            const step1HasErrors = {{ $errors->has('first_name') || $errors->has('middle_name') || $errors->has('last_name') || $errors->has('contact_number') || $errors->has('birth_month') || $errors->has('birth_day') || $errors->has('birth_year') || $errors->has('gender') || $errors->has('height') || $errors->has('weight') || $errors->has('region') || $errors->has('province') || $errors->has('city') ? 'true' : 'false' }};
            
            // Step 2 fields: email, password, password_confirmation
            const step2HasErrors = {{ $errors->has('email') || $errors->has('password') || $errors->has('password_confirmation') ? 'true' : 'false' }};
            
            // Priority: Show Step 1 if it has ANY errors, otherwise show Step 2 if it has errors
            if (step1HasErrors) {
                // Stay on Step 1 to fix Step 1 errors first
                showStep1();
            } else if (step2HasErrors) {
                // Only Step 2 has errors, show Step 2
                showStep2();
            }
            // Otherwise stay on Step 1 (default)
        });

        function showStep2() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
        }

        function showStep1() {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
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
