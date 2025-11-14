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

</x-dashboard-layout>