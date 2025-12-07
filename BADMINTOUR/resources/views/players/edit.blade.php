<x-dashboard-layout title="Complete Your Profile">
    <div class="max-w-3xl mx-auto">
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
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender" id="gender" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $player->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $player->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $player->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
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
                            <input type="text" name="region" id="region" value="{{ old('region', $player->region) }}" placeholder="NCR" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <input type="text" name="province" id="province" value="{{ old('province', $player->province) }}" placeholder="Metro Manila" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City/Municipality</label>
                            <input type="text" name="city" id="city" value="{{ old('city', $player->city) }}" placeholder="Quezon City" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Education Background</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="school_status" class="block text-sm font-medium text-gray-700 mb-1">School Status</label>
                            <select name="school_status" id="school_status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                                <option value="">Select Status</option>
                                <option value="student" {{ old('school_status', $player->school_status) == 'student' ? 'selected' : '' }}>Currently a Student</option>
                                <option value="graduated" {{ old('school_status', $player->school_status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                <option value="not_applicable" {{ old('school_status', $player->school_status) == 'not_applicable' ? 'selected' : '' }}>N/A</option>
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
                            <input type="file" name="player_id_document" accept="image/jpeg,image/png,image/jpg,application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                            <p class="text-xs text-gray-500 mt-1">JPG, JPEG, PNG or PDF. Max 5MB.</p>
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
</x-dashboard-layout>
