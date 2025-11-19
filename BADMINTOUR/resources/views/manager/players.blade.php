<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players Management - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    showAddPlayerModal: false,
    showViewPlayerModal: false,
    selectedPlayer: {
        id: 'PL001',
        name: 'John Doe',
        email: 'john.doe@email.com',
        club: 'Manila Badminton Club',
        skillLevel: 'A',
        status: 'Active',
        matchesPlayed: 24,
        wins: 20,
        losses: 4,
        joinDate: 'January 15, 2025'
    }
}">