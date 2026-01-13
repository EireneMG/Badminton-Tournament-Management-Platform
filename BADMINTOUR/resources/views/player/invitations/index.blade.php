<x-dashboard-layout title="Partner Invitations">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Partner Invitations</h1>
            <a href="{{ url()->previous() }}" class="text-sm text-[#2C5F4F] hover:underline">Back to Registration</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold mb-3">Invitations You Sent</h2>
                @forelse($sentInvitations as $invite)
                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $invite->invitee->first_name ?? '' }} {{ $invite->invitee->last_name ?? '' }}</p>
                                <p class="text-sm text-gray-600">{{ $invite->tournament->name ?? '' }} — {{ $invite->category->full_name ?? '' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Status: <span class="font-semibold capitalize">{{ $invite->status }}</span></p>
                            </div>
                            @if($invite->status === 'pending')
                                <form action="{{ route('player.invitations.cancel', $invite->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-sm px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No invitations sent.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="text-lg font-semibold mb-3">Invitations You Received</h2>
                @forelse($receivedInvitations as $invite)
                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $invite->inviter->first_name ?? '' }} {{ $invite->inviter->last_name ?? '' }}</p>
                                <p class="text-sm text-gray-600">{{ $invite->tournament->name ?? '' }} — {{ $invite->category->full_name ?? '' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Status: <span class="font-semibold capitalize">{{ $invite->status }}</span></p>
                            </div>
                            <div class="flex gap-2">
                                @if($invite->status === 'pending')
                                    <form action="{{ route('player.invitations.accept', $invite->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm px-3 py-2 bg-[#2C5F4F] text-white rounded hover:bg-[#244f41]">Accept</button>
                                    </form>
                                    <form action="{{ route('player.invitations.reject', $invite->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if($invite->message)
                            <p class="text-sm text-gray-700 mt-2">Message: {{ $invite->message }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No invitations received.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>

