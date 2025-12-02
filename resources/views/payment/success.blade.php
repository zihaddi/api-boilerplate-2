<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8 text-center">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Successful!</h2>
        <p class="text-gray-600 mb-8">Thank you for your payment. Your transaction has been completed successfully.</p>
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-gray-500">Transaction ID:</p>
            <p class="font-mono text-gray-700">{{ $transaction_id ?? 'N/A' }}</p>
        </div>
        <a href="{{ config('services.frontend_url') }}/dashboard" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors duration-200">
            Return to Dashboard
        </a>
    </div>
</body>
</html>
