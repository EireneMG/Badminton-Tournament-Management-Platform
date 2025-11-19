<!-- Ongoing Tab -->
<div x-show="activeTab === 'ongoing'" x-cloak>
    <!-- Tournament 1: Laguna Invitation 2025 -->
    <div class="mb-6">
        <div class="flex justify-between items-center border-b-2 border-gray-300 pb-3 mb-4">
            <h2 class="text-2xl font-bold text-black">Laguna Invitation 2025</h2>
            <button @click="lagunaOngoing = !lagunaOngoing" class="text-black font-medium underline">
                <span x-text="lagunaOngoing ? 'Collapse' : 'Expand'"></span>
            </button>
        </div>

        <!-- Laguna Matches Table -->
        <div x-show="lagunaOngoing" x-cloak class="bg-white border border-gray-300 rounded-lg p-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-black">Match</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Court</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Players</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Current Score</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Update Score</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Match 1 -->
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-4">#1</td>
                        <td class="py-4 px-4">Court 1</td>
                        <td class="py-4 px-4">Player E vs<br>Player F</td>
                        <td class="py-4 px-4">12 - 15</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="E" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <input type="text" placeholder="F" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <button class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    Update
                                </button>
                                <button class="bg-[#C85A54] hover:bg-[#B54A44] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    End
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Match 2 -->
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-4">#2</td>
                        <td class="py-4 px-4">Court 2</td>
                        <td class="py-4 px-4">Player G vs<br>Player H</td>
                        <td class="py-4 px-4">18 - 14</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="G" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <input type="text" placeholder="H" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <button class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    Update
                                </button>
                                <button class="bg-[#C85A54] hover:bg-[#B54A44] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    End
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tournament 2: BCBA Tournament 2025 -->
    <div class="mb-6">
        <div class="flex justify-between items-center border-b-2 border-gray-300 pb-3 mb-4">
            <h2 class="text-2xl font-bold text-black">BCBA Tournament 2025</h2>
            <button @click="bcbaOngoing = !bcbaOngoing" class="text-black font-medium underline">
                <span x-text="bcbaOngoing ? 'Collapse' : 'Expand'"></span>
            </button>
        </div>

        <!-- BCBA Matches Table -->
        <div x-show="bcbaOngoing" x-cloak class="bg-white border border-gray-300 rounded-lg p-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-black">Match</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Court</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Players</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Current Score</th>
                        <th class="text-left py-3 px-4 font-semibold text-black">Update Score</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Match 1 -->
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-4">#1</td>
                        <td class="py-4 px-4">Court 1</td>
                        <td class="py-4 px-4">Player A vs<br>Player B</td>
                        <td class="py-4 px-4">8 - 10</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="A" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <input type="text" placeholder="B" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <button class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    Update
                                </button>
                                <button class="bg-[#C85A54] hover:bg-[#B54A44] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    End
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Match 2 -->
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-4">#2</td>
                        <td class="py-4 px-4">Court 2</td>
                        <td class="py-4 px-4">Player C vs<br>Player D</td>
                        <td class="py-4 px-4">8 - 10</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="C" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <input type="text" placeholder="D" class="w-16 px-2 py-1 border border-gray-300 rounded text-center">
                                <button class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    Update
                                </button>
                                <button class="bg-[#C85A54] hover:bg-[#B54A44] text-white px-4 py-1 rounded text-sm font-medium transition duration-200">
                                    End
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
