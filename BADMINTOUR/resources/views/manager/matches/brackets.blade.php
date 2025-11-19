<!-- Brackets Tab -->
<div x-show="activeTab === 'brackets'" x-cloak>
    <!-- Tournament 1: Laguna Invitation 2025 -->
    <div class="mb-6">
        <div class="flex justify-between items-center border-b-2 border-gray-300 pb-3 mb-4">
            <h2 class="text-2xl font-bold text-black">Laguna Invitation 2025</h2>
            <button @click="lagunaBrackets = !lagunaBrackets" class="text-black font-medium underline">
                <span x-text="lagunaBrackets ? 'Collapse' : 'Expand'"></span>
            </button>
        </div>

        <!-- Laguna Bracket View -->
        <div x-show="lagunaBrackets" x-cloak class="bg-white border border-gray-300 rounded-lg p-6 text-center text-gray-500">
            <p>Bracket visualization for Laguna Invitation 2025</p>
        </div>
    </div>

    <!-- Tournament 2: BCBA Tournament 2025 -->
    <div class="mb-6">
        <div class="flex justify-between items-center border-b-2 border-gray-300 pb-3 mb-4">
            <h2 class="text-2xl font-bold text-black">BCBA Tournament 2025</h2>
            <button @click="bcbaBrackets = !bcbaBrackets" class="text-black font-medium underline">
                <span x-text="bcbaBrackets ? 'Collapse' : 'Expand'"></span>
            </button>
        </div>

        <!-- Bracket View -->
        <div x-show="bcbaBrackets" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Round 1 Column -->
            <div>
                <h3 class="text-center font-semibold text-black bg-gray-200 py-2 rounded-t-lg uppercase">Round 1</h3>
                <div class="space-y-4 mt-4">
                    <!-- Match 1 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER A</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER B</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>

                    <!-- Match 2 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER C</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER D</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>

                    <!-- Match 3 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER E</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER F</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>

                    <!-- Match 4 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER G</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER H</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upper Bracket Column -->
            <div>
                <h3 class="text-center font-semibold text-black bg-gray-200 py-2 rounded-t-lg uppercase">Upper Bracket</h3>
                <div class="space-y-4 mt-4">
                    <!-- Semi-Final 1 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER A</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER C</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>

                    <!-- Semi-Final 2 -->
                    <div class="border-2 border-black rounded-lg overflow-hidden">
                        <div class="flex justify-between items-center py-3 px-4 border-b border-gray-300">
                            <span class="font-semibold">PLAYER E</span>
                            <span class="font-bold text-lg">21</span>
                        </div>
                        <div class="flex justify-between items-center py-3 px-4 bg-gray-50">
                            <span class="font-semibold">PLAYER G</span>
                            <span class="font-bold text-lg">16</span>
                        </div>
                    </div>
                </div>

                <!-- Lower Bracket -->
                <h3 class="text-center font-semibold text-black bg-gray-200 py-2 rounded-t-lg uppercase mt-8">Lower Bracket</h3>
                <div class="space-y-4 mt-4">
                    <!-- Empty Match -->
                    <div class="border-2 border-black rounded-lg p-12"></div>
                </div>
            </div>
        </div>
    </div>
</div>
