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


</div>