<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logoEmbuscade.png') }}">
    
    <title>@yield('title', "L'Embuscade")</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>[x-cloak]{display:none!important;}</style>
</head>

<body class="bg-[#F4F4E3] text-gray-900 min-h-screen flex flex-col">

    <x-header />

    <main class="mx-auto w-full flex-1">
        @yield('content')
    </main>

    <div class="mt-auto">
        <x-footer />
    </div>

    @stack('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
