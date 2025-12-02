<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Failed</h2>
        <p class="text-gray-600 mb-8">We're sorry, but your payment could not be processed. Please try again or contact support if the problem persists.</p>
        <div class="space-y-4">
            <a href="{{ config('services.frontend_url') }}/payment" class="inline-block bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
                Try Again
            </a>
            <div>
                <a href="{{ config('services.frontend_url') }}/support" class="inline-block text-gray-500 hover:text-gray-700 mt-4">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>
