<x-dashboard-layout title="Complete Your Profile">
    <div class="max-w-3xl mx-auto" x-data="biodataForm()" x-init="init()">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-[#2C5F4F]">Complete Your Profile</h1>
                <p class="text-gray-600 mt-2">Please fill out your profile information. This helps club managers learn more about you when you join a club or register for tournaments.</p>
            </div>

            @if(!$player->biodata_completed)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Profile Completion Required</p>
                            <p class="text-sm mt-1">You must complete your profile before accessing other parts of the system. Please fill out all required fields below.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                    {{ session('warning') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Photo</h2>
                    <div class="flex items-center gap-6">
                        <div class="flex-shrink-0">
                            @if($player->profile_photo)
                                <img src="{{ Storage::url($player->profile_photo) }}" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover border-2 border-[#2C5F4F]">
                            @else
                                <div class="w-24 h-24 rounded-full bg-[#2C5F4F] flex items-center justify-center text-white text-3xl font-bold">
                                    {{ strtoupper(substr($player->first_name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Photo</label>
                            <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/jpg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#2C5F4F] file:text-white hover:file:bg-[#234b3f]">
                            <p class="text-xs text-gray-500 mt-1">JPG, JPEG or PNG. Max 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $player->first_name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $player->middle_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $player->last_name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $player->contact_number) }}" placeholder="+63 9XX XXX XXXX" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender (locked)</label>
                            @php $genderValue = strtolower($player->gender); @endphp
                            <div class="w-full px-4 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
                                @if($genderValue === 'male')
                                    Male
                                @elseif($genderValue === 'female')
                                    Female
                                @elseif($genderValue === 'other')
                                    Other
                                @else
                                    Not set
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <div class="grid grid-cols-3 gap-4">
                            <select name="birth_month" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Month</option>
                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                    <option value="{{ $index + 1 }}" {{ old('birth_month', $player->birth_month) == ($index + 1) ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                            <select name="birth_day" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Day</option>
                                @for($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ old('birth_day', $player->birth_day) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            <select name="birth_year" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Year</option>
                                @for($i = date('Y'); $i >= 1950; $i--)
                                    <option value="{{ $i }}" {{ old('birth_year', $player->birth_year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                            <input type="number" step="0.1" name="height" id="height" value="{{ old('height', $player->height) }}" placeholder="170" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" id="weight" value="{{ old('weight', $player->weight) }}" placeholder="65" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Location</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                            <select 
                                name="region"
                                id="region"
                                x-model="selectedRegion"
                                @change="onRegionChange()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]"
                            >
                                <option value="">Select Region</option>
                                <template x-for="region in regions" :key="region.code">
                                    <option :value="region.code" x-text="region.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <template x-if="selectedRegion === 'NCR'">
                                <input type="hidden" name="province" value="N/A">
                            </template>
                            <select 
                                name="province"
                                id="province"
                                x-model="selectedProvince"
                                @change="onProvinceChange()"
                                :disabled="!selectedRegion || selectedRegion === 'NCR'"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F] disabled:bg-gray-100 disabled:cursor-not-allowed"
                            >
                                <option value="">Select Province</option>
                                <option x-show="selectedRegion === 'NCR'" value="N/A">N/A</option>
                                <template x-for="province in availableProvinces" :key="province">
                                    <option :value="province" x-text="province"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City/Municipality</label>
                            <!-- Hidden field to preserve city value when disabled -->
                            <template x-if="(!selectedProvince && selectedRegion !== 'NCR') || !selectedRegion">
                                <input type="hidden" name="city" :value="selectedCity || '{{ old("city", $player->city) }}'">
                            </template>
                            <select 
                                name="city"
                                id="city"
                                x-model="selectedCity"
                                :disabled="(!selectedProvince && selectedRegion !== 'NCR') || !selectedRegion"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F] disabled:bg-gray-100 disabled:cursor-not-allowed"
                            >
                                <option value="">Select City</option>
                                <template x-for="city in availableCities" :key="city">
                                    <option :value="city" x-text="city"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Education Background</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="school_status" class="block text-sm font-medium text-gray-700 mb-1">Education Status</label>
                            <select name="school_status" id="school_status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Status</option>
                                <option value="junior_high" {{ old('school_status', $player->school_status) == 'junior_high' ? 'selected' : '' }}>Junior High School Student</option>
                                <option value="senior_high" {{ old('school_status', $player->school_status) == 'senior_high' ? 'selected' : '' }}>Senior High School Student</option>
                                <option value="college_student" {{ old('school_status', $player->school_status) == 'college_student' ? 'selected' : '' }}>College Student</option>
                                <option value="college_graduate" {{ old('school_status', $player->school_status) == 'college_graduate' ? 'selected' : '' }}>College Graduate</option>
                                <option value="post_graduate" {{ old('school_status', $player->school_status) == 'post_graduate' ? 'selected' : '' }}>Post-Graduate (Master's/Doctorate)</option>
                                <option value="not_applicable" {{ old('school_status', $player->school_status) == 'not_applicable' ? 'selected' : '' }}>N/A (Did not attend school)</option>
                            </select>
                        </div>
                        <div>
                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                            <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $player->school_name) }}" placeholder="University of the Philippines" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                            <p class="text-xs text-gray-500 mt-1">Leave blank if N/A</p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Badminton Experience</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="years_of_experience" class="block text-sm font-medium text-gray-700 mb-1">Years of Playing Experience</label>
                            <select name="years_of_experience" id="years_of_experience" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Years</option>
                                <option value="less_than_1" {{ old('years_of_experience', $player->years_of_experience) == 'less_than_1' ? 'selected' : '' }}>Less than 1 year</option>
                                <option value="1_2" {{ old('years_of_experience', $player->years_of_experience) == '1_2' ? 'selected' : '' }}>1–2 years</option>
                                <option value="3_5" {{ old('years_of_experience', $player->years_of_experience) == '3_5' ? 'selected' : '' }}>3–5 years</option>
                                <option value="6_10" {{ old('years_of_experience', $player->years_of_experience) == '6_10' ? 'selected' : '' }}>6–10 years</option>
                                <option value="more_than_10" {{ old('years_of_experience', $player->years_of_experience) == 'more_than_10' ? 'selected' : '' }}>More than 10 years</option>
                            </select>
                        </div>
                        <div>
                            <label for="experience_level" class="block text-sm font-medium text-gray-700 mb-1">Experience Level</label>
                            <select name="experience_level" id="experience_level" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Level</option>
                                <option value="beginner" {{ old('experience_level', $player->experience_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="lower_intermediate" {{ old('experience_level', $player->experience_level) == 'lower_intermediate' ? 'selected' : '' }}>Lower Intermediate</option>
                                <option value="intermediate" {{ old('experience_level', $player->experience_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="upper_intermediate" {{ old('experience_level', $player->experience_level) == 'upper_intermediate' ? 'selected' : '' }}>Upper Intermediate</option>
                                <option value="advanced" {{ old('experience_level', $player->experience_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label for="competitive_background" class="block text-sm font-medium text-gray-700 mb-1">Competitive Background</label>
                            <select name="competitive_background" id="competitive_background" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Background</option>
                                <option value="school_competitions" {{ old('competitive_background', $player->competitive_background) == 'school_competitions' ? 'selected' : '' }}>School competitions</option>
                                <option value="local_tournaments" {{ old('competitive_background', $player->competitive_background) == 'local_tournaments' ? 'selected' : '' }}>Local tournaments</option>
                                <option value="regional_tournaments" {{ old('competitive_background', $player->competitive_background) == 'regional_tournaments' ? 'selected' : '' }}>Regional tournaments</option>
                                <option value="national_tournaments" {{ old('competitive_background', $player->competitive_background) == 'national_tournaments' ? 'selected' : '' }}>National tournaments</option>
                                <option value="none" {{ old('competitive_background', $player->competitive_background) == 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">Select all that apply to your badminton background:</p>
                    @php
                        $historyOptions = [
                            'tournament' => 'Joined a tournament',
                            'school_event' => 'Participated in a school event/sportsfest',
                            'community_event' => 'Participated in a community event',
                            'club_member' => 'Member of a badminton club',
                            'recreational' => 'Recreational player',
                            'coached' => 'Received formal coaching',
                            'varsity' => 'Varsity/school team member',
                        ];
                        $currentHistory = old('badminton_history', $player->badminton_history) ?? [];
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($historyOptions as $value => $label)
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ in_array($value, $currentHistory) ? 'border-[#2C5F4F] bg-green-50' : 'border-gray-200' }}">
                                <input type="checkbox" name="badminton_history[]" value="{{ $value }}" {{ in_array($value, $currentHistory) ? 'checked' : '' }} class="h-4 w-4 text-[#2C5F4F] focus:ring-[#2C5F4F] border-gray-300 rounded">
                                <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">ID Document</h2>
                    <p class="text-sm text-gray-600 mb-4">Upload a valid ID for verification purposes (optional but recommended for tournament registration).</p>
                    <div class="space-y-4">
                        <div>
                            <label for="id_type" class="block text-sm font-medium text-gray-700 mb-1">ID Type</label>
                            <select name="id_type" id="id_type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select ID Type</option>
                                <option value="drivers_license" {{ old('id_type', $player->id_type) == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                                <option value="student_id" {{ old('id_type', $player->id_type) == 'student_id' ? 'selected' : '' }}>Student ID</option>
                                <option value="passport" {{ old('id_type', $player->id_type) == 'passport' ? 'selected' : '' }}>Passport</option>
                                <option value="national_id" {{ old('id_type', $player->id_type) == 'national_id' ? 'selected' : '' }}>National ID</option>
                                <option value="birth_certificate" {{ old('id_type', $player->id_type) == 'birth_certificate' ? 'selected' : '' }}>Birth Certificate</option>
                                <option value="prc_id" {{ old('id_type', $player->id_type) == 'prc_id' ? 'selected' : '' }}>PRC ID</option>
                                <option value="postal_id" {{ old('id_type', $player->id_type) == 'postal_id' ? 'selected' : '' }}>Postal ID</option>
                                <option value="senior_citizen_id" {{ old('id_type', $player->id_type) == 'senior_citizen_id' ? 'selected' : '' }}>Senior Citizen ID</option>
                                <option value="others" {{ old('id_type', $player->id_type) == 'others' ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4">
                            @if($player->player_id_document)
                                <div class="flex items-center gap-2 text-green-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm">ID document uploaded</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <input type="file" name="player_id_document" accept="image/jpeg,image/png,image/jpg,application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" @if(!$player->player_id_document) required @endif>
                                <p class="text-xs text-gray-500 mt-1">JPG, JPEG, PNG or PDF. Max 5MB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('profile.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-[#2C5F4F] text-white rounded-md hover:bg-[#234b3f] transition font-medium">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function biodataForm() {
            return {
                selectedRegion: '{{ old("region", $player->region) }}',
                selectedProvince: '{{ old("province", $player->province) }}',
                selectedCity: '{{ old("city", $player->city) }}',
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
                    const oldRegion = '{{ old("region", $player->region) }}';
                    const oldProvince = '{{ old("province", $player->province) }}';
                    const oldCity = '{{ old("city", $player->city) }}';
                    
                    // Helper: ensure we keep existing stored values even if they aren't in the predefined list
                    const ensureOption = (list, value) => {
                        if (value && !list.includes(value)) {
                            list.push(value);
                        }
                    };
                    
                    if (oldRegion) {
                        this.selectedRegion = oldRegion;
                        if (oldRegion === 'NCR') {
                            this.availableProvinces = [];
                            this.selectedProvince = 'N/A';
                            this.availableCities = this.regionData['NCR'].cities.slice();
                            ensureOption(this.availableCities, oldCity);
                            if (oldCity) {
                                this.selectedCity = oldCity;
                            }
                        } else if (this.regionData[oldRegion]) {
                            this.availableProvinces = this.regionData[oldRegion].provinces.slice();
                            ensureOption(this.availableProvinces, oldProvince);
                            
                            if (oldProvince) {
                                const cities = this.regionData[oldRegion].cities[oldProvince] || [];
                                this.availableCities = cities.slice();
                                ensureOption(this.availableCities, oldCity);
                                this.selectedProvince = oldProvince;
                                if (oldCity) {
                                    this.selectedCity = oldCity;
                                }
                            } else {
                                // No province selected yet; keep any old city as a selectable option
                                this.availableCities = [];
                                ensureOption(this.availableCities, oldCity);
                                if (oldCity) {
                                    this.selectedCity = oldCity;
                                }
                            }
                        } else {
                            // Unknown region: still keep the value selectable
                            this.availableProvinces = [];
                            ensureOption(this.availableProvinces, oldProvince);
                            this.availableCities = [];
                            ensureOption(this.availableCities, oldCity);
                            this.selectedProvince = oldProvince || '';
                            this.selectedCity = oldCity || '';
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
</x-dashboard-layout>
