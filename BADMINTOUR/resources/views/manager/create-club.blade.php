<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Club - Badminton Tournament Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" x-data="{ 
    clubLogo: null,
    clubLogoPreview: null,
    uploadLogo(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
            this.clubLogo = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.clubLogoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
    removeLogo() {
        this.clubLogo = null;
        this.clubLogoPreview = null;
        document.getElementById('logo-upload').value = '';
    }
}">