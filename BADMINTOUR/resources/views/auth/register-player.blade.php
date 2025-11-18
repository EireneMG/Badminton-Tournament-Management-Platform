<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden">
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
                <a href="{{ route('login') }}" class="text-[#2C5F4F] hover:text-[#234A3D] font-semibold">Login</a>
            </div>
        </div>

        <!-- Right Side - Beige Background with Form -->
        <div class="w-7/12 bg-[#E8DCC4] flex items-center justify-center p-8">
            <div class="w-full max-w-2xl px-8">
                <h2 class="text-[#7B1F3C] text-5xl font-bold mb-8 text-center">Register</h2>
                
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

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
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
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
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Region</option>
                                <option value="ncr">NCR</option>
                                <option value="region1">Region I</option>
                                <option value="region2">Region II</option>
                            </select>
                            <select 
                                name="province"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Province</option>
                                <option value="metro_manila">Metro Manila</option>
                            </select>
                            <select 
                                name="city"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">City</option>
                                <option value="manila">Manila</option>
                                <option value="quezon">Quezon City</option>
                            </select>
                        </div>

                        <!-- Gender and Height -->
                        <div class="grid grid-cols-2 gap-4">
                            <select 
                                name="gender"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <select 
                                name="height"
                                class="px-4 py-2.5 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-[#7B1F3C] text-gray-500 text-sm"
                            >
                                <option value="">Height</option>
                                <option value="150">150 cm</option>
                                <option value="160">160 cm</option>
                                <option value="170">170 cm</option>
                                <option value="180">180 cm</option>
                            </select>
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

                        <!-- Username -->
                        <div>
                            <input 
                                type="text" 
                                name="username" 
                                placeholder="Create username"
                                value="{{ old('username') }}"
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

                        <!-- Create Button -->
                        <div class="pt-6 flex justify-center">
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
        function showStep2() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
        }

        function showStep1() {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1').classList.remove('hidden');
        }
    </script>
    </div>
</body>
</html>