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
</x-dashboard-layout>