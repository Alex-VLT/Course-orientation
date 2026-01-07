<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>@yield('title', "L'Embuscade")</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F4F4E3] text-gray-900">

    <x-header />

    <main class="mx-auto">
        @yield('content')
    </main>

    <x-footer />

    @stack('scripts')
    <style>[x-cloak]{display:none!important;}</style>
</body>
</html>
