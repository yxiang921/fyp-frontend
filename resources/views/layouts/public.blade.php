<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FindSight — Missing Persons Gallery">
    <title>@yield('title', 'FindSight')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-navy text-white">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/15 rounded flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold leading-tight">FindSight</p>
                    <p class="text-[10px] text-gray-400 leading-tight">Missing Persons Gallery</p>
                </div>
            </div>
            <a href="{{ route('login') }}" class="text-xs text-gray-300 hover:text-white transition-colors">
                <i class="fa-solid fa-sign-in-alt mr-1"></i> Sign In
            </a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <nav class="flex flex-wrap gap-4 mb-5 text-sm">
            <a href="{{ route('public.gallery.index') }}">{{ __('Gallery') }}</a>
            <a href="{{ route('public.report-missing') }}">{{ __('Report a missing person') }}</a>
            <a href="{{ route('public.track') }}">{{ __('Track a case') }}</a>
            @foreach(['en' => 'English', 'ms' => 'Bahasa Melayu', 'zh' => '中文'] as $code => $label)
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" lang="{{ $code }}" @if(app()->getLocale() === $code) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 py-6 text-center text-xs text-gray-400">
        <p>&copy; {{ date('Y') }} FindSight. Facial recognition results are not final identification.</p>
    </footer>
    @stack('scripts')
</body>
</html>
