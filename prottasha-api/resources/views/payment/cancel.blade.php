<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Cancelled</h2>
        <p class="text-gray-600 mb-8">Your payment process has been cancelled. No charges have been made to your account.</p>
        <div class="space-y-4">
            <a href="{{ config('services.frontend_url') }}/payment" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
                Try Again
            </a>
            <div>
                <a href="{{ config('services.frontend_url') }}" class="inline-block text-gray-500 hover:text-gray-700 mt-4">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
