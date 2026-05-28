<div class="min-h-screen bg-background" wire:poll.1000ms.keep-alive>
    @php
        $card      = 'bg-card border border-border rounded-2xl shadow-xl overflow-hidden';
        $head      = 'bg-gradient-to-r from-slate-900 via-slate-800 to-black';
        $hpad      = 'px-6 py-5';
        $tag       = 'w-1.5 bg-white rounded-full';
        $label     = 'block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1.5';
        $input     = 'w-full h-10 px-3.5 rounded-lg border border-input bg-background text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all';
        $btnBlk    = 'inline-flex items-center justify-center gap-1.5 px-3.5 h-8 text-xs font-semibold rounded-lg bg-secondary text-secondary-foreground border border-border hover:bg-secondary/80 transition shadow-sm';
        $btnGrn    = 'inline-flex items-center justify-center gap-1.5 px-3.5 h-8 text-xs font-semibold rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 transition shadow-sm';
        $chip      = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-muted border border-border text-muted-foreground text-xs font-semibold';
        $mono      = 'inline-flex items-center text-[10px] font-mono text-muted-foreground bg-muted px-2.5 py-1 rounded-full border border-border';
        $icoAvatar = 'w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary font-bold text-sm shrink-0 border border-primary/20';
        $icoDot    = 'h-6';
        $sectPad   = 'px-6 py-5';
        $editIn    = 'w-full h-10 bg-background border border-input rounded-lg px-3.5 text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all';
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
        @if (session('saved'))
            <div class="inline-flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs font-semibold shadow-sm w-full">
                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Data dokumen berhasil disimpan.</span>
            </div>
        @endif

        <div class="relative overflow-hidden rounded-2xl bg-[#4A2F24] text-[#CDDEA7] shadow-2xl">
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <div class="absolute top-0 -right-4 w-24 h-24 bg-[#CDDEA7] rounded-full blur-xl"></div>
                <div class="absolute bottom-0 -left-4 w-16 h-16 bg-[#CDDEA7] rounded-full blur-lg"></div>
            </div>
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#CDDEA7]/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-[#CDDEA7]/20">
                        <svg class="w-6 h-6 text-[#CDDEA7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8M4 6h16v12H4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold">Dokumen</h2>
                        <p class="text-xs text-[#CDDEA7]/80">Kelola dokumen masuk & pengiriman hari ini</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <x-heroicon-o-plus-circle class="w-4.5 h-4.5 text-primary" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Tambah Dokumen</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Lengkapi data dokumen</p>
                </div>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Dokumen</label>
                        <input type="text" wire:model.defer="document_name" class="{{ $input }}" placeholder="Contoh: Surat Perintah">
                        @error('document_name') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Type</label>
                        <div class="relative">
                            <select wire:model.defer="type" class="{{ $input }} appearance-none pr-8">
                                <option value="document">Document</option>
                                <option value="invoice">Invoice</option>
                                <option value="etc">Etc</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('type') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Nama Pengirim</label>
                        <input type="text" wire:model.defer="nama_pengirim" class="{{ $input }}" placeholder="Instansi/Orang pengirim">
                    </div>
                    <div>
                        <label class="{{ $label }}">Nama Penerima</label>
                        <input type="text" wire:model.defer="nama_penerima" class="{{ $input }}" placeholder="Penerima internal">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Penyimpanan</label>
                        <div class="relative">
                            <select wire:model.defer="penyimpanan" class="{{ $input }} appearance-none pr-8">
                                <option value="">-</option>
                                <option value="rak1">Rak 1</option>
                                <option value="rak2">Rak 2</option>
                                <option value="rak3">Rak 3</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="{{ $label }}">Tanggal & Jam Pengambilan</label>
                        <div class="flex items-center gap-3">
                            <input type="date" wire:model.defer="pengambilan_date" class="{{ $input }} w-full">
                            <input type="time" wire:model.defer="pengambilan_time" class="{{ $input }} w-full">
                        </div>
                        @error('pengambilan_date') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        @error('pengambilan_time') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="{{ $label }}">Status</label>
                        <div class="relative">
                            <select wire:model.defer="status" class="{{ $input }} appearance-none pr-8">
                                <option value="pending">Pending</option>
                                <option value="taken">Taken</option>
                                <option value="delivered">Delivered</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @error('status') <p class="mt-1.5 text-xs text-destructive font-medium">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-muted-foreground mt-1.5 font-semibold">
                            Jika pilih <b>Delivered</b>, data langsung masuk ke kotak Riwayat (ditandai waktu sekarang).
                        </p>
                    </div>
                </div>

                <div class="pt-4 border-t border-border bg-muted/5 -mx-6 -mb-6 p-6 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if (session('saved'))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs font-semibold">
                                <x-heroicon-o-check class="w-3.5 h-3.5" />
                                <span>Tersimpan!</span>
                            </span>
                        @endif
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/95 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-60 relative overflow-hidden">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="save">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Simpan Data</span>
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="save">
                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4 text-white" />
                            <span>Menyimpan…</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <x-heroicon-o-archive-box-arrow-down class="w-4.5 h-4.5 text-amber-500" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Dokumen Pending</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Menampilkan semua dokumen berstatus pending</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse ($pendingList as $r)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-muted/20 border border-border hover:bg-muted/30 transition-colors"
                        wire:key="pending-{{ $r->document_id }}">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 bg-primary/10 border border-primary/20 rounded-xl flex items-center justify-center text-primary text-sm font-semibold shrink-0">
                                {{ strtoupper(substr($r->document_name, 0, 1)) }}
                            </div>
                            <div class="leading-tight min-w-0">
                                <div class="font-bold text-foreground text-sm truncate">{{ $r->document_name }}</div>
                                <div class="text-[11px] text-muted-foreground font-medium mt-1">
                                    Pengambilan {{ optional($r->pengambilan)->format('H:i') ?? '—' }} • Pengirim
                                    {{ $r->nama_pengirim ?? '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button wire:click="openEdit({{ $r->document_id }})" wire:loading.attr="disabled"
                                wire:target="openEdit({{ $r->document_id }})" class="{{ $btnBlk }}">
                                <span wire:loading.remove wire:target="openEdit({{ $r->document_id }})">Edit</span>
                                <span wire:loading wire:target="openEdit({{ $r->document_id }})">Memuat…</span>
                            </button>

                            <button wire:click="setSudahDikirim({{ $r->document_id }})" wire:loading.attr="disabled"
                                wire:target="setSudahDikirim({{ $r->document_id }})" class="{{ $btnGrn }} relative">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" wire:loading
                                        wire:target="setSudahDikirim({{ $r->document_id }})">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4z" />
                                    </svg>
                                    <span>Sudah dikirim</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-muted-foreground text-sm font-medium">Tidak ada pending.</div>
                @endforelse
            </div>
        </div>

        <div class="{{ $card }}">
            <div class="px-6 py-5 border-b border-border bg-muted/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <x-heroicon-o-check-badge class="w-4.5 h-4.5 text-emerald-500" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Riwayat Dokumen</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Hanya dokumen yang sudah dikirim</p>
                </div>
            </div>

            <div class="px-6 py-5 bg-muted/10 border-b border-border flex flex-col gap-3.5 lg:flex-row lg:items-center">
                <div class="relative w-full lg:w-56">
                    <input type="date" wire:model.live="filter_date" class="{{ $input }} pl-10">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground/60">
                        <x-heroicon-o-calendar class="w-4.5 h-4.5" />
                    </div>
                </div>
                <div class="relative flex-1 w-full">
                    <input type="text" wire:model.live="q"
                        placeholder="Cari nama dokumen / pengirim / penerima / type / penyimpanan / status..."
                        class="{{ $input }} pl-10">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground/60">
                        <x-heroicon-o-magnifying-glass class="w-4.5 h-4.5" />
                    </div>
                </div>
            </div>

            <div class="divide-y divide-border">
                @forelse ($entries as $e)
                    @php
                        $rowNo = ($entries->firstItem() ?? 1) + $loop->index;
                    @endphp
                    <div class="px-6 py-5 hover:bg-muted/10 transition-colors" wire:key="entry-{{ $e->document_id }}">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                <div class="{{ $icoAvatar }}">{{ strtoupper(substr($e->document_name, 0, 1)) }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <h4 class="font-bold text-foreground text-base truncate">{{ $e->document_name }}</h4>
                                        @if ($e->nama_pengirim)
                                            <span class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider bg-muted border border-border px-2 py-0.5 rounded-md">Dari: {{ $e->nama_pengirim }}</span>
                                        @endif
                                        @if ($e->nama_penerima)
                                            <span class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider bg-muted border border-border px-2 py-0.5 rounded-md">Ke: {{ $e->nama_penerima }}</span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-2.5">
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span>{{ ucfirst($e->type) }}</span>
                                        </span>
                                        <span class="{{ $chip }}">
                                            <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>{{ $e->penyimpanan ?? '—' }}</span>
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ optional($e->pengambilan)->format('d M Y H:i') ?? '—' }}</span>
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Dikirim: {{ optional($e->pengiriman)->format('d M Y H:i') }}</span>
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Status: {{ ucfirst($e->status) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0 space-y-2 lg:self-stretch flex lg:flex-col lg:justify-between items-end flex-wrap gap-2 lg:gap-0">
                                <div class="flex flex-col items-end">
                                    <span class="{{ $mono }}">No. {{ $rowNo }}</span>
                                    <span class="text-[10px] text-muted-foreground/60 font-semibold mt-1">{{ $e->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <button wire:click="openEdit({{ $e->document_id }})" wire:loading.attr="disabled"
                                        wire:target="openEdit({{ $e->document_id }})" class="{{ $btnBlk }}">
                                        <span wire:loading.remove wire:target="openEdit({{ $e->document_id }})">Edit</span>
                                        <span wire:loading wire:target="openEdit({{ $e->document_id }})">Memuat…</span>
                                    </button>
                                    <button wire:click="delete({{ $e->document_id }})"
                                        onclick="return confirm('Hapus dokumen ini?')" wire:loading.attr="disabled"
                                        wire:target="delete({{ $e->document_id }})"
                                        class="inline-flex items-center justify-center gap-1.5 px-3.5 h-8 text-xs font-semibold rounded-lg bg-destructive text-destructive-foreground hover:bg-destructive/90 transition shadow-sm">
                                        <span wire:loading.remove wire:target="delete({{ $e->document_id }})">Hapus</span>
                                        <span wire:loading wire:target="delete({{ $e->document_id }})">Menghapus…</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-14 text-center text-muted-foreground text-sm font-medium">Tidak ada riwayat dokumen.</div>
                @endforelse
            </div>

            <div class="px-6 py-4 bg-muted/10 border-t border-border flex justify-end">
                {{ $entries->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

    @if ($showEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" wire:poll.1000ms>
            <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-all duration-300" wire:click="closeEdit"></div>
            
            <div class="relative w-full max-w-lg bg-card rounded-2xl shadow-2xl border border-border overflow-hidden transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-black p-6 text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <div class="absolute top-0 -right-6 w-24 h-24 bg-white rounded-full blur-2xl"></div>
                        <div class="absolute bottom-0 -left-4 w-16 h-16 bg-white rounded-full blur-xl"></div>
                    </div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold tracking-tight">Edit Dokumen</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                                <p class="text-[10px] text-slate-300 font-semibold font-mono tracking-wider">{{ $this->serverClock }}</p>
                            </div>
                        </div>
                        <button class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-all duration-200"
                            wire:click="closeEdit">
                            <x-heroicon-o-x-mark class="w-4 h-4 text-white" />
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="space-y-1.5">
                        <label class="{{ $label }}">Nama Dokumen</label>
                        <input type="text" wire:model="edit.document_name" class="{{ $editIn }}" placeholder="Nama dokumen">
                        @error('edit.document_name') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Type</label>
                            <div class="relative">
                                <select wire:model="edit.type" class="{{ $editIn }} appearance-none pr-8">
                                    <option value="document">Document</option>
                                    <option value="invoice">Invoice</option>
                                    <option value="etc">Etc</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('edit.type') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Penyimpanan</label>
                            <input type="text" wire:model="edit.penyimpanan" class="{{ $editIn }}" placeholder="Rak/Box">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="{{ $label }}">Pengambilan</label>
                        <div class="flex items-center gap-3">
                            <input type="date" wire:model="edit.pengambilan_date" class="{{ $editIn }} w-full">
                            <input type="time" wire:model="edit.pengambilan_time" class="{{ $editIn }} w-full">
                        </div>
                        @error('edit.pengambilan_date') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                        @error('edit.pengambilan_time') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                        <div class="bg-primary/5 rounded-2xl p-4 border border-primary/20 mt-2">
                            <p class="text-[11px] text-muted-foreground leading-relaxed font-medium">
                                💡 <span class="font-bold text-foreground">Tips:</span> Klik <span class="font-bold text-primary">Sudah dikirim</span> di daftar Pending/Taken untuk pakai waktu real-time pengiriman.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Pengiriman</label>
                            <input type="datetime-local" wire:model="edit.pengiriman" class="{{ $editIn }}">
                            @error('edit.pengiriman') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Nama Pengirim</label>
                            <input type="text" wire:model="edit.nama_pengirim" class="{{ $editIn }}" placeholder="Pengirim">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Nama Penerima</label>
                            <input type="text" wire:model="edit.nama_penerima" class="{{ $editIn }}" placeholder="Penerima">
                        </div>
                        <div class="space-y-1.5">
                            <label class="{{ $label }}">Status</label>
                            <div class="relative">
                                <select wire:model="edit.status" class="{{ $editIn }} appearance-none pr-8">
                                    <option value="pending">Pending</option>
                                    <option value="taken">Taken</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted-foreground/60">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('edit.status') <p class="text-[11px] text-destructive font-medium">{{ $message }}</p> @enderror
                            <p class="text-[11px] text-muted-foreground mt-1.5 font-semibold">
                                Akan otomatis menjadi <b>Delivered</b> bila <b>Pengiriman</b> diisi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-muted/10 border-t border-border p-6 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeEdit"
                        class="px-4 h-10 rounded-lg border border-border text-muted-foreground text-xs font-semibold hover:text-foreground hover:bg-muted transition">cancel</button>
                    <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit"
                        class="inline-flex items-center justify-center gap-2 px-5 h-10 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/95 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/20 disabled:opacity-60 relative overflow-hidden">
                        <span wire:loading.remove wire:target="saveEdit" class="flex items-center gap-1.5">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Simpan Perubahan</span>
                        </span>
                        <span wire:loading wire:target="saveEdit" class="flex items-center gap-2">
                            <x-heroicon-o-arrow-path class="animate-spin h-4 w-4 text-white" />
                            <span>Menyimpan…</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>