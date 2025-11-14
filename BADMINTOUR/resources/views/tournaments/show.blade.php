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
                
</x-dashboard-layout>