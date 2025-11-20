<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-800 mb-4">500</h1>
            <h2 class="text-2xl font-semibold text-gray-600 mb-4">Server Error</h2>
            <p class="text-gray-500 mb-8">Something went wrong on our end. Please try again later.</p>
            <a href="<?= BASE_URL ?>" class="bg-brand text-white px-6 py-3 rounded-lg hover:bg-brand-dark transition">
                Go Home
            </a>
        </div>
    </div>
</body>
</html>

