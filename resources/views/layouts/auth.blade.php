<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'App' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/kebun-raya-bogor.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-background text-foreground min-h-screen">

    <main class="">
        {{ $slot }}
    </main>

    @vite('resources/js/app.js')
    @livewireScripts
</body>

</html>