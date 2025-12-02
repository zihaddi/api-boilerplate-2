<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetBd API Home</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-lg p-10 text-center bg-white rounded-2xl shadow-2xl border border-gray-100">
        <div class="mb-6">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent">
                Petbd API
            </h1>
        </div>

        <div class="space-y-4 mb-8">
            <p class="text-lg text-gray-700 font-medium">Welcome to your API Gateway</p>
            <p class="text-gray-500 leading-relaxed">Your powerful API is ready to serve. Explore the documentation to get started with integration.</p>

            <div class="inline-flex items-center px-4 py-2 bg-green-50 border border-green-200 rounded-full">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                <span class="text-sm font-medium text-green-700">Version 1.0.0 - Active</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="/api-docs" class="block w-full px-8 py-4 text-white font-semibold bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg hover:from-blue-700 hover:to-indigo-800 transform hover:scale-105 transition-all duration-200">
                📚 View Documentation
            </a>

            <div class="flex space-x-2">
                <a href="/health" class="flex-1 px-4 py-3 text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-sm font-medium">
                    ⚡ Health Check
                </a>
                <a href="/status" class="flex-1 px-4 py-3 text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 text-sm font-medium">
                    📊 API Status
                </a>
            </div>
        </div>
    </div>
</body>
</html>
