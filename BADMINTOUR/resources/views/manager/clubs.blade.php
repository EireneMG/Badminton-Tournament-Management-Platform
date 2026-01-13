<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Clubs - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.manager-sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-[#2C5F4F]">All Clubs</h1>
                        <p class="text-sm text-gray-500">Browse all clubs in the platform</p>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
                <div class="max-w-7xl mx-auto" x-data="{
                    searchQuery: '',
                    sortColumn: 'name',
                    sortDirection: 'asc',
                    clubs: @js($allClubs->map(function($club) use ($currentManagerClub) {
                        return [
                            'id' => $club->id,
                            'name' => $club->name,
                            'city' => $club->city,
                            'province' => $club->province,
                            'members_count' => $club->approved_players_count,
                            'manager_name' => $club->manager ? $club->manager->first_name . ' ' . $club->manager->last_name : 'N/A',
                            'manager_id' => $club->manager_id,
                            'logo' => $club->logo,
                            'description' => $club->description,
                            'is_my_club' => $currentManagerClub && $club->id === $currentManagerClub->id,
                        ];
                    })->toArray()),
                    get filteredClubs() {
                        let filtered = this.clubs;
                        if (this.searchQuery) {
                            const query = this.searchQuery.toLowerCase();
                            filtered = filtered.filter(club => 
                                club.name.toLowerCase().includes(query) ||
                                club.city.toLowerCase().includes(query) ||
                                club.province.toLowerCase().includes(query) ||
                                club.manager_name.toLowerCase().includes(query)
                            );
                        }
                        return filtered;
                    },
                    sort(column) {
                        if (this.sortColumn === column) {
                            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                        } else {
                            this.sortColumn = column;
                            this.sortDirection = 'asc';
                        }
                        this.clubs.sort((a, b) => {
                            let aVal = a[column];
                            let bVal = b[column];
                            if (aVal === null || aVal === undefined) return 1;
                            if (bVal === null || bVal === undefined) return -1;
                            if (column === 'members_count') {
                                aVal = aVal ?? 0;
                                bVal = bVal ?? 0;
                                return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                            }
                            aVal = String(aVal).toLowerCase();
                            bVal = String(bVal).toLowerCase();
                            if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                            if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                            return 0;
                        });
                    },
                    getSortIcon(column) {
                        if (this.sortColumn !== column) return '↕️';
                        return this.sortDirection === 'asc' ? '↑' : '↓';
                    }
                }">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="searchQuery"
                                placeholder="Search clubs by name, city, province, or manager..."
                                class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-[#2C5F4F] focus:ring-1 focus:ring-[#2C5F4F]"
                            >
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Clubs Table -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                Club Name
                                                <span x-text="getSortIcon('name')"></span>
                                            </div>
                                        </th>
                                        <th @click="sort('city')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                Location
                                                <span x-text="getSortIcon('city')"></span>
                                            </div>
                                        </th>
                                        <th @click="sort('manager_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                Manager
                                                <span x-text="getSortIcon('manager_name')"></span>
                                            </div>
                                        </th>
                                        <th @click="sort('members_count')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                            <div class="flex items-center gap-2">
                                                Members
                                                <span x-text="getSortIcon('members_count')"></span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="club in filteredClubs" :key="club.id">
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <template x-if="club.logo">
                                                        <img :src="`/storage/${club.logo}`" :alt="club.name" class="h-10 w-10 rounded-lg object-cover border-2 border-[#D4A574] mr-3">
                                                    </template>
                                                    <template x-if="!club.logo">
                                                        <div class="h-10 w-10 rounded-lg bg-[#2C5F4F] flex items-center justify-center text-white font-semibold border-2 border-[#D4A574] mr-3" x-text="club.name.substring(0, 2).toUpperCase()"></div>
                                                    </template>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900" x-text="club.name"></div>
                                                        <template x-if="club.description">
                                                            <div class="text-xs text-gray-500 mt-1 line-clamp-1" x-text="club.description"></div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900" x-text="club.city + ', ' + club.province"></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900" x-text="club.manager_name"></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 font-semibold" x-text="club.members_count"></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <template x-if="club.is_my_club">
                                                    <a :href="'/manager/club'" class="bg-[#2C5F4F] hover:bg-[#244D3E] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                                        My Club
                                                    </a>
                                                </template>
                                                <template x-if="!club.is_my_club">
                                                    <a :href="`/manager/clubs/${club.id}`" class="bg-[#D4A574] hover:bg-[#C49564] text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                                        View Details
                                                    </a>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="filteredClubs.length === 0">
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                                <p>No clubs found matching your search.</p>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

