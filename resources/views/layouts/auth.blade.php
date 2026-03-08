<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'App' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/kebun-raya-bogor.png') }}" />
    @vite('resources/css/app.css')
    @livewireStyles
    @fluxAppearance
</head>

<body class="bg-white min-h-screen">

    <main class="">
        {{ $slot }}
    </main>

    @vite('resources/js/app.js')
    @livewireScripts
    @fluxScripts
    
    <script>
        console.log('=== INLINE SCRIPT RUNNING ===');
        console.log('window.Livewire:', typeof window.Livewire);
        console.log('window.Alpine:', typeof window.Alpine);
        
        // Check if Livewire is properly initialized
        document.addEventListener('livewire:init', () => {
            console.log('✅ Livewire:init event fired!');
        });
        
        document.addEventListener('livewire:initialized', () => {
            console.log('✅ Livewire:initialized event fired!');
            console.log('Livewire components:', Livewire.all());
        });
        
        // Try to wait for Livewire to load
        let checkCount = 0;
        const checkLivewire = setInterval(() => {
            checkCount++;
            console.log(`Check #${checkCount}: Livewire =`, typeof window.Livewire);
            
            if (typeof window.Livewire !== 'undefined' || checkCount > 10) {
                clearInterval(checkLivewire);
                if (typeof window.Livewire !== 'undefined') {
                    console.log('✅ Livewire loaded successfully!');
                    console.log('Livewire.all():', Livewire.all());
                } else {
                    console.error('❌ Livewire failed to load after 10 checks');
                }
            }
        }, 500);
    </script>
</body>

</html>