<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-size=1.0">
    <title>419 - Page Expired</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="mb-4">
            <img src="{{ asset('images/logo.jpg') }}" alt="SENTIENTS A.I" class="mx-auto w-20 h-20 rounded-xl object-cover shadow-lg">
        </div>
        <h1 class="text-6xl font-bold text-yellow-600 mb-4">419</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Page Expired</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Your session has expired. Please refresh the page and try again.
        </p>
        <div class="space-x-4">
            <button onclick="window.location.reload()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                Refresh Page
            </button>
            <a href="{{ url('/') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                Go Home
            </a>
        </div>
        <div class="mt-8 text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} SENTIENTS A.I - Car Rental System</p>
        </div>
    </div>
</body>
</html>