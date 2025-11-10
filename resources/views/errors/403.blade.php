<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="mb-4">
            <img src="{{ asset('images/logo.jpg') }}" alt="SENTIENTS A.I" class="mx-auto w-20 h-20 rounded-xl object-cover shadow-lg">
        </div>
        <h1 class="text-6xl font-bold text-red-600 mb-4">403</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Access Forbidden</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            You don't have permission to access this resource. Please contact an administrator if you believe this is an error.
        </p>
        <div class="space-x-4">
            <a href="{{ url('/') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                Go Home
            </a>
            <button onclick="history.back()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                Go Back
            </button>
        </div>
        <div class="mt-8 text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} SENTIENTS A.I - Car Rental System</p>
        </div>
    </div>
</body>
</html>