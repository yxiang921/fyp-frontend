<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FRMS') — Facial Recognition Monitoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-navy rounded flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-halved text-white text-xl"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900">FRMS</h1>
            <p class="text-sm text-gray-500 mt-1">Real-Time Facial Recognition-Based<br>Monitoring and Alert System</p>
        </div>

        <div class="bg-white border border-gray-200 rounded shadow-sm p-6">
            @yield('auth-content')
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} FRMS. This is a prototype system.<br>
            Facial recognition results are simulated.
        </p>
    </div>
</body>
</html>
