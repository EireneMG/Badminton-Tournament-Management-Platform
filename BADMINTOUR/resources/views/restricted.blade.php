@extends('components.dashboard-layout')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="max-w-2xl w-full">
        <!-- Restricted Access Card -->
        <div class="bg-white rounded-lg border-2 border-[#C85A54] p-8 text-center">
            <!-- Icon -->
            <div class="mb-6">
                <svg class="mx-auto h-20 w-20 text-[#C85A54]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Access Restricted</h1>

            <!-- Message -->
            <div class="bg-red-50 border-l-4 border-[#C85A54] p-6 rounded mb-6">
                <p class="text-lg text-gray-800 mb-3">
                    <strong>Registration confirmation required.</strong>
                </p>
                <p class="text-gray-700">
                    You need to complete the club verification and registration process before accessing this feature. Please ensure all required steps are completed.
                </p>
            </div>

            <!-- Requirements List -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-bold text-gray-900 mb-4 text-center">Required Steps:</h3>
                <ul class="space-y-3">
                    <li class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-[#C85A54] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">Complete Profile Information</p>
                            <p class="text-sm text-gray-600">Ensure all profile fields are filled out accurately</p>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-[#C85A54] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">Club Verification</p>
                            <p class="text-sm text-gray-600">Join a verified badminton club or wait for club approval</p>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-[#C85A54] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">Registration Confirmation</p>
                            <p class="text-sm text-gray-600">Wait for platform administrator to verify your registration</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-6">
                <p class="text-sm text-gray-800">
                    <strong>Need help?</strong> Contact the platform administrator at <a href="mailto:support@badminton.com" class="text-[#C85A54] hover:underline">support@badminton.com</a> for assistance with registration verification.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="/player/dashboard" class="px-8 py-3 bg-[#C85A54] text-white rounded-lg font-semibold hover:bg-[#B54A44] transition">
                    Go to Dashboard
                </a>
                <a href="/profile" class="px-8 py-3 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Update Profile
                </a>
            </div>
        </div>

        <!-- Additional Help Card -->
        <div class="mt-6 bg-white rounded-lg border-2 border-[#D4A574] p-6">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                <svg class="w-5 h-5 text-[#D4A574] mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                Common Questions
            </h3>
            <div class="space-y-3 text-sm text-gray-700">
                <div>
                    <p class="font-semibold text-gray-900">How long does verification take?</p>
                    <p>Typically 24-48 hours after submitting all required information.</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">What if I don't have a club?</p>
                    <p>You can browse the clubs page and request to join an existing club, or contact an administrator.</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Can I still view tournaments?</p>
                    <p>Yes, you can browse tournaments and rankings, but registration requires verification.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection