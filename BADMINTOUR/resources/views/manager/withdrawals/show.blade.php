<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Withdrawal Request - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white" x-data="{ 
    showApproveModal: false,
    showRejectModal: false,
    refundStatus: 'none',
    managerResponse: ''
}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold text-black">REVIEW WITHDRAWAL REQUEST</h1>
                    <a href="{{ route('manager.withdrawals.index') }}" class="text-gray-600 hover:text-gray-800">
                        ← Back to Withdrawal Requests
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                @php
                    $player = $registration->player;
                    $partner = $registration->partner;
                    $category = $registration->category;
                @endphp

                <div class="max-w-4xl mx-auto">
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

                    <!-- Withdrawal Request Details -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">Withdrawal Request Details</h2>
                        </div>

                        <div class="px-6 py-4 space-y-6">
                            <!-- Player Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Player Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $player->first_name }} {{ $player->last_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $player->email }}</p>
                                    </div>
                                    @if($partner)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Partner</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $partner->first_name }} {{ $partner->last_name }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Tournament Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Tournament Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tournament</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $tournament->name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Category</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $category->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Requested</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $withdrawalRequest->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Withdrawal Reason -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Reason for Withdrawal</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $withdrawalRequest->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 justify-end">
                        <button 
                            @click="showRejectModal = true"
                            class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition duration-200">
                            Reject Request
                        </button>
                        <button 
                            @click="showApproveModal = true"
                            class="px-6 py-3 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-lg font-semibold transition duration-200">
                            Approve Request
                        </button>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div x-show="showApproveModal" 
                     x-cloak
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                     @click.self="showApproveModal = false">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Approve Withdrawal Request</h3>
                        
                        <form method="POST" action="{{ route('manager.withdrawals.approve', $withdrawalRequest->id) }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Refund Status</label>
                                <select name="refund_status" x-model="refundStatus" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]">
                                    <option value="none">No Refund</option>
                                    <option value="partial">Partial Refund</option>
                                    <option value="full">Full Refund</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Manager Response (Optional)</label>
                                <textarea 
                                    name="manager_response" 
                                    x-model="managerResponse"
                                    rows="3" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]"
                                    placeholder="Add any notes or comments..."></textarea>
                            </div>

                            <div class="flex gap-4 justify-end">
                                <button 
                                    type="button"
                                    @click="showApproveModal = false"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition duration-200">
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    class="px-4 py-2 bg-[#2C5F4F] hover:bg-[#244D3E] text-white rounded-lg font-medium transition duration-200">
                                    Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div x-show="showRejectModal" 
                     x-cloak
                     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                     @click.self="showRejectModal = false">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Reject Withdrawal Request</h3>
                        
                        <form method="POST" action="{{ route('manager.withdrawals.reject', $withdrawalRequest->id) }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                                <textarea 
                                    name="manager_response" 
                                    x-model="managerResponse"
                                    rows="3" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#2C5F4F]"
                                    placeholder="Please provide a reason for rejecting this withdrawal request..."></textarea>
                                <p class="mt-1 text-sm text-gray-500">This message will be sent to the player.</p>
                            </div>

                            <div class="flex gap-4 justify-end">
                                <button 
                                    type="button"
                                    @click="showRejectModal = false"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition duration-200">
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition duration-200">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

