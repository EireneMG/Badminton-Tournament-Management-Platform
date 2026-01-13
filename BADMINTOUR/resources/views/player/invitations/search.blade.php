<x-dashboard-layout title="Invite Partner">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('player.tournaments.register.show', [$tournament->id, $category->id]) }}" class="inline-flex items-center text-[#D4A574] hover:text-[#C4956A]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold text-lg">Back to Registration</span>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500">Tournament</p>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $tournament->name }}</h1>
                    <p class="text-sm text-gray-600 mt-1">Category: <span class="font-semibold">{{ $category->full_name }}</span></p>
                </div>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-[#2C5F4F] focus:border-[#2C5F4F]">
                </div>
                <div class="md:col-span-1 flex items-end justify-end gap-3">
                    <a href="{{ route('player.invitations.search', [$tournament->id, $category->id]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-2 bg-[#2C5F4F] text-white rounded-lg hover:bg-[#244f41]">
                        Filter
                    </button>
                </div>
            </form>

            <div class="mb-4 text-sm text-gray-700">
                Showing players within ±{{ $eloGap }} ELO of you (auto-filtered for fairness). Your ELO: {{ $userElo }}.
            </div>

            @if($players->count() === 0)
                <p class="text-sm text-gray-500">No eligible partners found. Try adjusting filters.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Player Name</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Gender</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Club</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ELO (Δ)</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($players as $player)
                                @php
                                    $elo = $player->elo_value ?? 0;
                                    $delta = $elo - $userElo;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-900 font-semibold">{{ $player->first_name }} {{ $player->last_name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700 capitalize">{{ $player->gender ?? 'n/a' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        {{ $player->clubMemberships->first()->club->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">
                                        {{ $elo }} ({{ $delta >= 0 ? '+' : '' }}{{ $delta }})
                                    </td>
                                    <td class="px-4 py-2 text-sm text-green-700">Available</td>
                                    <td class="px-4 py-2 text-sm">
                                        <form action="{{ route('player.invitations.send') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                                            <input type="hidden" name="invitee_id" value="{{ $player->id }}">
                                            <button type="submit" class="px-3 py-1.5 bg-[#C85A54] text-white rounded hover:bg-[#B54A44] text-xs">
                                                Invite
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $players->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>

