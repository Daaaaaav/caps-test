<footer class="bg-white">
    <div class="mx-auto max-w-7xl px-6 sm:px-8">
        {{-- Top area --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-10 py-10">

            {{-- Logo --}}
            <div class="flex items-start">
                {{-- Integrated Logo Logic from Navbar --}}
                @php
                $company = auth()->user()?->company;
                $rawLogo = $company?->image;
                // Changed fallback path to match the asset path used in the logo image
                $fallback = asset('https://tiketkebunraya.id/assets/images/kebun-raya.png'); 
                $logoUrl = $fallback;

                if (!empty($rawLogo)) {
                if (preg_match('#^https?://#i', $rawLogo)) {
                $logoUrl = $rawLogo;
                } else {
                if (file_exists(public_path($rawLogo))) {
                $logoUrl = asset($rawLogo);
                } elseif (file_exists(public_path('storage/'.$rawLogo))) {
                $logoUrl = asset('storage/'.$rawLogo);
                } elseif (file_exists(public_path('images/'.$rawLogo))) {
                $logoUrl = asset('images/'.$rawLogo);
                }
                }
                }
                @endphp

                <a href="{{ route('home') }}" class="transition-transform duration-300 hover:scale-105 flex items-center gap-2">
                    <img src="{{ $logoUrl }}" alt="{{ $company?->company_name ?? 'Kebun Raya' }} Logo" class="h-20 w-auto">
                </a>
            </div>

            {{-- Tagline + Socials --}}
            <div class="md:col-span-2 lg:col-span-1">
                <p class="text-sm leading-6 text-slate-500 max-w-[32ch]">
                    Kebun Raya berpegang pada lima pilar: Konservasi (melestarikan tumbuhan), Edukasi (meningkatkan pengetahuan botani), Penelitian, Jasa Lingkungan, dan Wisata Alam yang inspiratif.
                </p>

                <div class="mt-6 flex items-center gap-4">
                    {{-- Facebook (Reverting to original SVG) --}}
                    <a href="#" aria-label="Facebook"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M22 12a10 10 0 1 0-11.6 9.87v-6.99H7.9V12h2.5V9.8c0-2.46 1.47-3.82 3.72-3.82 1.08 0 2.22.19 2.22.19v2.44h-1.25c-1.23 0-1.61.76-1.61 1.54V12h2.74l-.44 2.88h-2.3v6.99A10 10 0 0 0 22 12z" />
                        </svg>
                    </a>
                    {{-- Twitter/X (Reverting to original SVG) --}}
                    <a href="#" aria-label="Twitter"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M17.53 3h3.02l-6.6 7.55L22 21h-6.52l-4.56-5.9L5.7 21H2.67l7.1-8.13L2 3h6.64l4.13 5.5L17.53 3zm-1.14 16h1.67L7.7 4.99H5.96L16.39 19z" />
                        </svg>
                    </a>
                    {{-- LinkedIn (Reverting to original SVG) --}}
                    <a href="#" aria-label="LinkedIn"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M6.94 8.88H4.19V20h2.75V8.88zM5.56 7.55a1.6 1.6 0 1 0 0-3.2 1.6 1.6 0 0 0 0 3.2zM20 20h-2.74v-5.62c0-1.34-.03-3.07-1.87-3.07-1.87 0-2.16 1.46-2.16 2.97V20H10.5V8.88h2.63v1.51h.04c.37-.7 1.29-1.44 2.66-1.44 2.85 0 3.37 1.88 3.37 4.33V20z" />
                        </svg>
                    </a>
                    {{-- Instagram (Reverting to original SVG) --}}
                    <a href="#" aria-label="Instagram"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 7.2A4.8 4.8 0 1 0 12 16.8 4.8 4.8 0 0 0 12 7.2zm0 7.9a3.1 3.1 0 1 1 0-6.2 3.1 3.1 0 0 1 0 6.2z" />
                            <path
                                d="M17.3 2H6.7A4.7 4.7 0 0 0 2 6.7v10.6A4.7 4.7 0 0 0 6.7 22h10.6A4.7 4.7 0 0 0 22 17.3V6.7A4.7 4.7 0 0 0 17.3 2zM20.3 17.3c0 1.66-1.34 3-3 3H6.7c-1.66 0-3-1.34-3-3V6.7c0-1.66 1.34-3 3-3h10.6c1.66 0 3 1.34 3 3v10.6z" />
                            <circle cx="17.7" cy="6.3" r="1.2" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Nav links --}}
            <nav class="lg:pl-6">
                <ul class="space-y-3 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-slate-700 transition-colors">About Us</a></li>
                    <li><a href="#" class="hover:text-slate-700 transition-colors">Services</a></li>
                    <li><a href="#" class="hover:text-slate-700 transition-colors">Portfolio</a></li>
                    <li><a href="#" class="hover:text-slate-700 transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-slate-700 transition-colors">Contact</a></li>
                </ul>
            </nav>

            {{-- Contact (Using Heroicons and Dynamic Data from $company) --}}
            <div class="space-y-3 text-sm text-slate-500">
                {{-- Email --}}
                <div class="flex items-start gap-3">
                    <x-heroicon-o-envelope class="mt-0.5 h-5 w-5" />
                    <span>{{ $company?->company_email ?? 'gatau@mauisiapa.com' }}</span>
                </div>
                {{-- Phone --}}
                <div class="flex items-start gap-3">
                    <x-heroicon-o-phone class="mt-0.5 h-5 w-5" />
                    <span>{{ $company?->company_phone ?? '(0251) 8311362' }}</span>
                </div>
                {{-- Address (Dynamic from DB) --}}
                <div class="flex items-start gap-3">
                    <x-heroicon-o-map-pin class="mt-0.5 h-5 w-5" />
                    <span>{{ $company?->company_address ?? 'Jl. Ir. H. Juanda No.13, Paledang, Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16122' }}</span>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-200"></div>

        {{-- Bottom bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between py-4 text-xs text-slate-400 gap-4">
            <p>© 2024 {{ $company?->company_name ?? 'YourBrand' }}. All rights reserved.</p>
            <ul class="flex items-center gap-8">
                <li><a href="#" class="hover:text-slate-600">Cookie Policy</a></li>
                <li><a href="#" class="hover:text-slate-600">Privacy Policy</a></li>
                <li><a href="#" class="hover:text-slate-600">Terms of Service</a></li>
            </ul>
        </div>
    </div>
</footer>