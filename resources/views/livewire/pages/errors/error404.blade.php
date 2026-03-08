{{-- resources/views/errors/404.blade.php --}}
<div class="min-h-screen bg-white flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-8xl md:text-9xl font-black text-black opacity-20 select-none">404</h1>
        <div class="-mt-16 md:-mt-20 mb-8">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-black">
                Oops… Halaman Tidak Ditemukan
            </h2>
            <p class="mt-2 text-gray-600">
                Maaf, halaman yang kamu cari tidak tersedia atau sudah dipindahkan.
            </p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border border-black max-w-xl mx-auto">
            <div class="flex items-center gap-3 mb-3 justify-center">
                <div class="bg-black text-white px-3 py-1 rounded-full text-sm font-medium">Error</div>
                <h2 class="text-xl font-semibold text-black">404 – Not Found</h2>
            </div>

            <p class="text-gray-600 mb-6">
                URL tidak ditemukan. Coba kembali ke beranda atau periksa kembali alamat yang kamu masukkan.
            </p>

            <div class="flex justify-center">
                @if (Route::has('home'))
                <a href="{{ route('home') }}"
                    class="bg-black text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-800 hover:shadow-lg">
                    ← Kembali ke Beranda
                </a>
                @endif
            </div>
        </div>
    </div>
</div>