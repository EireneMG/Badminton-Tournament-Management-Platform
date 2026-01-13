<x-dashboard-layout title="Invitation Details">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('player.invitations.index') }}" class="inline-flex items-center text-[#2C5F4F] hover:text-[#244f41]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Invitations
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Partner Invitation</h1>
            <p class="text-sm text-gray-600 mb-6">Status: <span class="font-semibold capitalize">{{ $invitation->status }}</span></p>

            <div class="space-y-3 text-sm text-gray-800">
                <div><span class="font-semibold">Tournament:</span> {{ $invitation->tournament->name ?? '' }}</div>
                <div><span class="font-semibold">Category:</span> {{ $invitation->category->name ?? '' }}</div>
                <div><span class="font-semibold">From:</span> {{ $invitation->inviter->first_name ?? '' }} {{ $invitation->inviter->last_name ?? '' }}</div>
                <div><span class="font-semibold">To:</span> {{ $invitation->invitee->first_name ?? '' }} {{ $invitation->invitee->last_name ?? '' }}</div>
                @if($invitation->message)
                    <div class="pt-2">
                        <span class="font-semibold">Message:</span>
                        <p class="mt-1 text-gray-700">{{ $invitation->message }}</p>
                    </div>
                @endif
            </div>

            @if($invitation->status === 'pending' && auth()->id() === $invitation->invitee_id)
                <div class="mt-6 flex gap-3">
                    <form action="{{ route('player.invitations.accept', $invitation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#244f41]">Accept</button>
                    </form>
                    <form action="{{ route('player.invitations.reject', $invitation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                    </form>
                </div>
            @elseif($invitation->status === 'pending' && auth()->id() === $invitation->inviter_id)
                <div class="mt-6">
                    <form action="{{ route('player.invitations.cancel', $invitation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel Invitation</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>

