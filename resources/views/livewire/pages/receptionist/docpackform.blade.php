<div class="min-h-screen bg-background" wire:poll.1000ms.keep-alive>
    @php
        $card   = 'bg-card border border-border rounded-2xl shadow-xl overflow-hidden';
        $head   = 'bg-[#4A2F24]';
        $hpad   = 'px-6 py-5';
        $label  = 'block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5';
        $input  = 'w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all';
        $btnBlk = 'inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/95 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-60';
    @endphp

    <style>
      :root { color-scheme: light; }
      select, option {
        color: var(--foreground) !important;
        background: var(--background) !important;
      }
      option:checked { background: var(--muted) !important; color: var(--foreground) !important; }
    </style>

    <div class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl {{ $head }} text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                        <x-heroicon-o-archive-box class="w-6 h-6 text-[#CDDEA7]" />
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">{{ __('app.docpac_form_title') }}</h2>
                        <p class="text-sm text-[#CDDEA7]/80">{{ __('app.docpac_form_sub') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-border bg-muted/10">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 bg-primary rounded-full animate-pulse"></div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">{{ __('app.add_data') }}</h3>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ __('app.add_data_sub') }}</p>
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-6">
                {{-- Row: Direction & Type & Storage --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="{{ $label }}">Arah</label>
                        <div class="relative">
                            <select class="{{ $input }} appearance-none pr-8" wire:model.live="direction" wire:key="direction-select">
                                <option value="taken">Masuk untuk internal (Taken)</option>
                                <option value="deliver">Titip untuk dikirim (Deliver later)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('direction') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Tipe</label>
                        <div class="relative">
                            <select class="{{ $input }} appearance-none pr-8" wire:model.live="itemType" wire:key="type-select">
                                <option value="package">Package</option>
                                <option value="document">Document</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('itemType') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Tempat Penyimpanan</label>
                        <div class="relative">
                            <select class="{{ $input }} appearance-none pr-8" wire:model.defer="storageId" wire:key="storage-select">
                                <option value="">Pilih penyimpanan…</option>
                                @foreach($storages as $s)
                                    <option wire:key="storage-{{ $s['id'] }}" value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('storageId') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Item name --}}
                <div>
                    <label class="{{ $label }}">Nama Paket/Dokumen</label>
                    <input type="text" class="{{ $input }}" wire:model.defer="itemName" placeholder="Contoh: Dokumen Kontrak PT ABC">
                    @error('itemName') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-5">
                        <div>
                            <label class="{{ $label }}">
                                {{ $direction === 'taken' ? 'Departemen Penerima' : 'Departemen Pengirim' }}
                            </label>
                            <div class="relative">
                                <select class="{{ $input }} appearance-none pr-8" wire:model.live="departmentId" wire:key="dept-select">
                                    <option value="">Pilih departemen…</option>
                                    @foreach($departments as $d)
                                        <option wire:key="dept-{{ $d['id'] }}" value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('departmentId') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">
                                {{ $direction === 'taken' ? 'Nama Penerima (User)' : 'Nama Pengirim (User)' }}
                            </label>
                            <div class="relative">
                                <select
                                    class="{{ $input }} appearance-none pr-8 disabled:bg-muted disabled:text-muted-foreground"
                                    wire:model.live="userId"
                                    wire:key="user-select-{{ $departmentId ?? 'none' }}"
                                    @disabled(!$departmentId || empty($users))
                                >
                                    <option value="" selected disabled>
                                        {{ !$departmentId ? 'Pilih departemen dulu…' : (empty($users) ? 'Tidak ada user pada departemen ini' : 'Pilih user…') }}
                                    </option>
                                    @if($departmentId && !empty($users))
                                        @foreach($users as $id => $name)
                                            <option wire:key="user-{{ $id }}" value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('userId') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-5">
                        @if ($direction === 'taken')
                            <div>
                                <label class="{{ $label }}">Nama Pengirim (Free Text)</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="senderText" placeholder="Kurir / Ekspedisi / Pengirim">
                                @error('senderText') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div>
                                <label class="{{ $label }}">Nama Penerima (Free Text)</label>
                                <input type="text" class="{{ $input }}" wire:model.defer="receiverText" placeholder="Nama penerima">
                                @error('receiverText') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                {{-- FOTO BUKTI --}}
                <div>
                    <label class="{{ $label }}">Bukti Foto <span class="text-muted-foreground/60 font-normal">(opsional)</span></label>

                    <div class="space-y-3.5">
                        {{-- upload biasa (galeri / file explorer) --}}
                        <input
                            id="photo-input"
                            type="file"
                            class="{{ $input }} !h-auto py-2 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                            wire:model="photo"
                            accept="image/*"
                            capture="environment"
                        >
                        @error('photo') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror

                        {{-- tombol khusus kamera (laptop / PC / HP) --}}
                        <button
                            type="button"
                            id="open-camera-btn"
                            class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border border-border bg-secondary text-secondary-foreground text-xs font-semibold hover:bg-secondary/80 transition">
                            <x-heroicon-o-camera class="w-4 h-4" />
                            <span>Ambil dari kamera</span>
                        </button>

                        {{-- preview --}}
                        @if ($photo)
                            <div class="mt-3">
                                <p class="text-xs text-muted-foreground font-semibold mb-1.5">Preview bukti:</p>
                                <img src="{{ $photo->temporaryUrl() }}" class="w-40 h-40 object-cover rounded-2xl border border-border shadow-sm">
                            </div>
                        @endif

                        <p class="text-[11px] text-muted-foreground">
                            Di HP: bisa pakai kamera atau galeri. Di laptop/PC: klik "Ambil dari kamera", beri izin akses kamera.
                        </p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-4 flex items-center border-t border-border bg-muted/5 -mx-6 -mb-6 p-6">
                    <button type="submit" class="{{ $btnBlk }}" wire:loading.attr="disabled" wire:target="save,photo">
                        <span wire:loading.remove wire:target="save,photo" class="inline-flex items-center gap-2">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Simpan</span>
                        </span>
                        <span wire:loading wire:target="save,photo" class="inline-flex items-center gap-2">
                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4 text-white" />
                            <span>Menyimpan…</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KAMERA (untuk laptop/PC & HP) --}}
    <div id="camera-modal"
         wire:ignore   {{-- penting: Livewire jangan utak-atik modal ini --}}
         class="fixed inset-0 bg-black/60 backdrop-blur-md z-50 items-center justify-center p-4 hidden">
        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-lg w-full overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-semibold text-foreground">Kamera Aktif</span>
                </div>
                <button id="close-camera-btn" class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition">
                    <x-heroicon-o-x-mark class="w-4.5 h-4.5" />
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-black/5 rounded-2xl border border-border overflow-hidden flex items-center justify-center">
                    <video id="camera-video" autoplay playsinline class="max-h-[60vh] rounded-xl"></video>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-xs text-muted-foreground">
                        Pastikan browser mengizinkan akses kamera (HTTPS / localhost).
                    </span>
                    <button id="capture-btn" class="inline-flex items-center gap-1.5 px-4 h-9 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/95 transition shadow-sm shrink-0">
                        <x-heroicon-o-camera class="w-4 h-4" />
                        <span>Ambil Foto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- JS kamera + DEBUG LOGS --}}
    <script>
        (function () {
            console.log('[DocPackForm] <script> tag evaluated');

            function initCameraScript() {
                console.log('[DocPackForm] initCameraScript called');

                let stream = null;

                const openBtn   = document.getElementById('open-camera-btn');
                const modal     = document.getElementById('camera-modal');
                const closeBtn  = document.getElementById('close-camera-btn');
                const video     = document.getElementById('camera-video');
                const captureBtn= document.getElementById('capture-btn');
                const fileInput = document.getElementById('photo-input');

                console.log('[DocPackForm] DOM lookup:', {
                    openBtn: !!openBtn,
                    modal: !!modal,
                    closeBtn: !!closeBtn,
                    video: !!video,
                    captureBtn: !!captureBtn,
                    fileInput: !!fileInput,
                });

                if (!openBtn || !modal || !video || !captureBtn || !fileInput || !closeBtn) {
                    console.warn('[DocPackForm] Some elements not found, aborting initCameraScript');
                    return;
                }

                async function openCamera() {
                    console.log('[DocPackForm] openCamera() called');

                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        console.error('[DocPackForm] navigator.mediaDevices.getUserMedia NOT available');
                        alert('Browser tidak mendukung kamera (getUserMedia tidak tersedia).');
                        return;
                    }

                    try {
                        console.log('[DocPackForm] Requesting getUserMedia...');
                        stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        console.log('[DocPackForm] getUserMedia SUCCESS, stream tracks:', stream.getTracks().length);
                        video.srcObject = stream;
                        video.play().then(() => {
                            console.log('[DocPackForm] video.play() resolved');
                        }).catch((e) => {
                            console.error('[DocPackForm] video.play() error:', e);
                        });
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        console.log('[DocPackForm] camera modal shown');
                    } catch (e) {
                        console.error('[DocPackForm] getUserMedia ERROR:', e);
                        alert('Gagal mengakses kamera. Cek izin browser & HTTPS / localhost.');
                    }
                }

                function closeCamera() {
                    console.log('[DocPackForm] closeCamera() called');
                    if (stream) {
                        console.log('[DocPackForm] stopping stream tracks');
                        stream.getTracks().forEach(t => t.stop());
                        stream = null;
                    }
                    video.srcObject = null;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                // Attach listeners
                openBtn.addEventListener('click', () => {
                    console.log('[DocPackForm] open-camera-btn CLICK');
                    openCamera();
                });

                closeBtn.addEventListener('click', () => {
                    console.log('[DocPackForm] close-camera-btn CLICK');
                    closeCamera();
                });

                captureBtn.addEventListener('click', () => {
                    console.log('[DocPackForm] capture-btn CLICK');
                    if (!stream) {
                        console.warn('[DocPackForm] No stream when capture clicked');
                        return;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width  = video.videoWidth  || 640;
                    canvas.height = video.videoHeight || 480;
                    console.log('[DocPackForm] capture canvas size:', canvas.width, canvas.height);

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            console.error('[DocPackForm] canvas.toBlob returned null blob');
                            return;
                        }

                        console.log('[DocPackForm] canvas.toBlob OK, size:', blob.size);

                        const file = new File([blob], 'camera-photo.png', { type: 'image/png' });
                        const dt = new DataTransfer();
                        dt.items.add(file);

                        fileInput.files = dt.files;
                        console.log('[DocPackForm] fileInput.files set from camera, dispatching change event');
                        fileInput.dispatchEvent(new Event('change', { bubbles: true }));

                        closeCamera();
                    }, 'image/png');
                });

                // Optional: log setiap Livewire re-render
                if (window.Livewire) {
                    Livewire.hook('message.processed', () => {
                        console.log('[DocPackForm] Livewire message.processed (poll re-render)');
                    });
                }
            }

            // Try to init on both events, for safety
            document.addEventListener('DOMContentLoaded', () => {
                console.log('[DocPackForm] DOMContentLoaded');
                initCameraScript();
            });

            document.addEventListener('livewire:load', () => {
                console.log('[DocPackForm] livewire:load');
                initCameraScript();
            });
        })();
    </script>
</div>
