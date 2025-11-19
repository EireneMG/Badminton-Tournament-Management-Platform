<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings Overview - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white" x-data="{ 
    activeCategory: 'mens-singles',
    showPlayerModal: false,
    selectedPlayer: {
        name: 'John Doe',
        club: 'Manila Badminton Club',
        rank: 1,
        points: 2850,
        matchesPlayed: 24,
        wins: 20,
        losses: 4,
        winRate: 83.3
    }
}">
</body>
</html>