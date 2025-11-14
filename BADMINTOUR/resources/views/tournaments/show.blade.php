<x-dashboard-layout title="Tournament Details">
    <div class="max-w-7xl mx-auto" x-data="{
        showWithdrawModal: false,
        categoryToWithdraw: null,
        
        categories: {
            mens_singles: {
                registered: true,
                withdrawalStatus: null,
                deadlinePassed: false
            },
            womens_singles: {
                registered: false,
                withdrawalStatus: null,
                deadlinePassed: false
            },
            mens_doubles: {
                registered: true,
                withdrawalStatus: 'denied',
                deadlinePassed: false
            },
            womens_doubles: {
                registered: false,
                withdrawalStatus: null,
                deadlinePassed: false
            },
            mixed_doubles: {
                registered: true,
                withdrawalStatus: null,
                deadlinePassed: true
            }
        },
        
        openWithdrawModal(category) {
            this.categoryToWithdraw = category;
            this.showWithdrawModal = true;
        },
        
        submitWithdrawal() {
            this.categories[this.categoryToWithdraw].withdrawalStatus = 'pending';
            this.showWithdrawModal = false;
            this.categoryToWithdraw = null;
        },
        
        cancelWithdrawal(category) {
            this.categories[category].withdrawalStatus = null;
        },
        
        simulateApproval(category) {
            this.categories[category].withdrawalStatus = 'approved';
        },
        
        simulateDenial(category) {
            this.categories[category].withdrawalStatus = 'denied';
        }
    }">
        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2">
                <!-- Back Button and Header -->
                <div class="mb-6">
                    <a href="{{ route('tournaments.index') }}" class="inline-flex items-center text-[#D4A574] hover:text-[#C4956

4] mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="font-semibold text-lg">UPCOMING TOURNAMENTS</span>
                    </a>
                </div>

                <!-- Tournament Title and Details -->
                <div class="bg-white rounded-lg p-6 mb-6">
                    <h1 class="text-3xl font-bold mb-4">MYSB Tournament 2025</h1>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>October 1 - 3, 2025</span>
                        </div>
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Neopolitan Brittany Badminton Court, QC</span>
                        </div>
                        
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Organizer: Coach Luisa Gregorio</span>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b-2 border-[#D4A574] mb-6">
                        <div class="flex space-x-6">
                            <button class="px-2 py-3 border-b-2 border-[#C85A54] font-semibold text-gray-900 text-sm">Overview</button>
                            <button class="px-2 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54] text-sm">Registration Details</button>
                            <button class="px-2 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54] text-sm">Match Fixtures</button>
                            <button class="px-2 py-3 border-b-2 border-transparent font-semibold text-gray-600 hover:text-[#C85A54] text-sm">Participants</button>
                        </div>
                    </div>

                    <!-- Overview Content -->
                    <div class="text-gray-700 leading-relaxed">
                        <p>The MYSB Club Tournament 2025 is the club's annual event, bringing together players of all skill levels for a competitive yet community-centered event. The tournament is currently in progress, with players competing across categories. Live updates of matches, results, and standings are available throughout the event.</p>
                    </div>
                </div>
            </div>

            <!-- Categories Sidebar -->
            <div class="col-span-1">
                <div class="bg-white rounded-lg p-6 sticky top-6">
                    <h3 class="text-xl font-bold mb-4">Categories</h3>
                    
                    <div class="space-y-3">
                        <!-- Men's Singles -->
                        <div class="border-2 border-[#D4A574] rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">Men's Singles</span>
                                <span class="text-sm text-[#C85A54] font-semibold">Open</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">8/10 Registered</p>

                            <!-- STATE: Withdrawal Approved -->
                            <div x-show="categories.mens_singles.withdrawalStatus === 'approved'" class="mb-3">
                                <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-green-800">Withdrawn</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- STATE: Withdrawal Pending -->
                            <div x-show="categories.mens_singles.withdrawalStatus === 'pending'" class="mb-3">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs font-semibold text-yellow-800">Withdrawal Request Pending</span>
                                            </div>
                                            <p class="text-xs text-yellow-700 ml-6">Waiting for manager approval</p>
                                        </div>
                                    </div>
                                    <button 
                                        @click="cancelWithdrawal('mens_singles')"
                                        class="text-xs text-[#C85A54] hover:text-[#B54A44] font-semibold mt-2 ml-6">
                                        Cancel Withdrawal Request
                                    </button>
                                </div>
                            </div>
                            
                            <!-- STATE: Withdrawal Denied -->
                            <div x-show="categories.mens_singles.withdrawalStatus === 'denied'" class="mb-3">
                                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-red-800">Withdrawal Request Denied</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- STATE: Deadline Passed -->
                            <div x-show="categories.mens_singles.registered && categories.mens_singles.deadlinePassed && !categories.mens_singles.withdrawalStatus" class="mb-3">
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">Withdrawal period has ended</span>
                                </div>
                            </div>

                            <!-- ACTION BUTTONS -->
                            <div x-show="categories.mens_singles.withdrawalStatus !== 'approved'">
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- Withdraw Button: Show if registered, deadline not passed, and status is null or denied -->
                                    <button 
                                        x-show="categories.mens_singles.registered && !categories.mens_singles.deadlinePassed && (categories.mens_singles.withdrawalStatus === null || categories.mens_singles.withdrawalStatus === 'denied')"
                                        @click="openWithdrawModal('mens_singles')"
                                        class="bg-gray-600 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700 transition">
                                        Withdraw
                                    </button>
                                    
                                    <!-- Register Button: Show if not registered -->
                                    <button 
                                        x-show="!categories.mens_singles.registered"
                                        class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">
                                        Register
                                    </button>
                                    
                                    <!-- View Button: Always show unless approved -->
                                    <button class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">View</button>
                                </div>
                            </div>
                        </div>

                        <!-- Women's Singles -->
                        <div class="border-2 border-[#D4A574] rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">Women's Singles</span>
                                <span class="text-sm text-[#C85A54] font-semibold">Open</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">6/10 Registered</p>
                            
                            <div x-show="categories.womens_singles.withdrawalStatus === 'approved'" class="mb-3">
                                <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-green-800">Withdrawn</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div x-show="categories.womens_singles.withdrawalStatus === 'pending'" class="mb-3">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs font-semibold text-yellow-800">Withdrawal Request Pending</span>
                                            </div>
                                            <p class="text-xs text-yellow-700 ml-6">Waiting for manager approval</p>
                                        </div>
                                    </div>
                                    <button 
                                        @click="cancelWithdrawal('womens_singles')"
                                        class="text-xs text-[#C85A54] hover:text-[#B54A44] font-semibold mt-2 ml-6">
                                        Cancel Withdrawal Request
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="categories.womens_singles.withdrawalStatus === 'denied'" class="mb-3">
                                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-red-800">Withdrawal Request Denied</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div x-show="categories.womens_singles.registered && categories.womens_singles.deadlinePassed && !categories.womens_singles.withdrawalStatus" class="mb-3">
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">Withdrawal period has ended</span>
                                </div>
                            </div>
                            
                            <div x-show="categories.womens_singles.withdrawalStatus !== 'approved'">
                                <div class="grid grid-cols-2 gap-2">
                                    <button 
                                        x-show="categories.womens_singles.registered && !categories.womens_singles.deadlinePassed && (categories.womens_singles.withdrawalStatus === null || categories.womens_singles.withdrawalStatus === 'denied')"
                                        @click="openWithdrawModal('womens_singles')"
                                        class="bg-gray-600 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700 transition">
                                        Withdraw
                                    </button>
                                    <button 
                                        x-show="!categories.womens_singles.registered"
                                        class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">
                                        Register
                                    </button>
                                    <button class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">View</button>
                                </div>
                            </div>
                        </div>

                         <!-- Men's Doubles (Demonstrates Denied State) -->
                        <div class="border-2 border-[#D4A574] rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold">Men's Doubles</span>
                                <span class="text-sm text-[#C85A54] font-semibold">Full</span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">10/10 Registered</p>
                            
                            <div x-show="categories.mens_doubles.withdrawalStatus === 'approved'" class="mb-3">
                                <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-green-800">Withdrawn</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div x-show="categories.mens_doubles.withdrawalStatus === 'pending'" class="mb-3">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-xs font-semibold text-yellow-800">Withdrawal Request Pending</span>
                                            </div>
                                            <p class="text-xs text-yellow-700 ml-6">Waiting for manager approval</p>
                                        </div>
                                    </div>
                                    <button 
                                        @click="cancelWithdrawal('mens_doubles')"
                                        class="text-xs text-[#C85A54] hover:text-[#B54A44] font-semibold mt-2 ml-6">
                                        Cancel Withdrawal Request
                                    </button>
                                </div>
                            </div>
                            
                            <div x-show="categories.mens_doubles.withdrawalStatus === 'denied'" class="mb-3">
                                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-red-800">Withdrawal Request Denied</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div x-show="categories.mens_doubles.registered && categories.mens_doubles.deadlinePassed && !categories.mens_doubles.withdrawalStatus" class="mb-3">
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs font-semibold">Withdrawal period has ended</span>
                                </div>
                            </div>
                            
                            <div x-show="categories.mens_doubles.withdrawalStatus !== 'approved'">
                                <div class="grid grid-cols-2 gap-2">
                                    <button 
                                        x-show="categories.mens_doubles.registered && !categories.mens_doubles.deadlinePassed && (categories.mens_doubles.withdrawalStatus === null || categories.mens_doubles.withdrawalStatus === 'denied')"
                                        @click="openWithdrawModal('mens_doubles')"
                                        class="bg-gray-600 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-700 transition">
                                        Withdraw
                                    </button>
                                    <button 
                                        x-show="!categories.mens_doubles.registered"
                                        class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">
                                        Register
                                    </button>
                                    <button class="bg-[#C85A54] text-white px-3 py-1.5 rounded text-sm hover:bg-[#B54A44] transition">View</button>
                                </div>
                            </div>
                        </div>

</x-dashboard-layout>