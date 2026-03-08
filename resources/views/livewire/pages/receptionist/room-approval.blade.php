@php
    $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
    $btn  = 'px-3 py-2 text-xs font-medium rounded-lg text-white focus:outline-none focus:ring-2 transition';
@endphp

<div class="bg-gray-50" wire:poll.2s="tick">
    <main class="px-4 sm:px-6 py-6 space-y-6">
        {{-- HERO --}}
        <header class="rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-xl px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 11c0 1.104.896 2 2 2h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-semibold">Room Approval</h2>
                <p class="text-sm text-white/80">Setujui permintaan booking & pantau rapat berjalan.</p>
            </div>
        </header>

        {{-- PENDING --}}
        <section class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Pending Approval</h3>
                        <p class="text-sm text-gray-500">Permintaan menunggu persetujuan receptionist.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-3">
                @forelse ($pending as $m)
                    @php $id = $m['id']; @endphp
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200" wire:key="p-{{ $id }}">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($m['meeting_title'] ?? 'M',0,1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $m['meeting_title'] }}</div>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['date'] }}</span>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['time'] }}–{{ $m['time_end'] }}</span>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['room'] }}</span>
                                </div>
                                <div class="mt-1 text-[12px] text-gray-600">{{ $m['participants'] }} peserta</div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button class="{{ $btn }} bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-600/20"
                                            wire:click="approve({{ $id }})" wire:loading.attr="disabled">Approve</button>

                                    <button class="{{ $btn }} bg-rose-600 hover:bg-rose-700 focus:ring-rose-600/20"
                                            wire:click="askReject({{ $id }})" wire:loading.attr="disabled">Reject</button>
                                </div>

                                {{-- Reject box (reason is UI-only) --}}
                                @if($rejectId === $id)
                                    <div class="mt-3">
                                        <textarea class="w-full h-20 border border-gray-300 rounded-md px-3 py-2 text-sm"
                                                  placeholder="Alasan penolakan (opsional, tidak disimpan)" wire:model.defer="reject_reason"></textarea>
                                        <div class="mt-2 flex gap-2">
                                            <button class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-300"
                                                    wire:click="$set('rejectId', null)">Batal</button>
                                            <button class="{{ $btn }} bg-rose-600 hover:bg-rose-700" wire:click="reject">Kirim Penolakan</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">ID {{ $id }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-sm">Tidak ada permintaan menunggu.</div>
                @endforelse
            </div>
        </section>

        {{-- ONGOING --}}
        <section class="{{ $card }}">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-amber-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Ongoing</h3>
                        <p class="text-sm text-gray-500">Rapat sedang berlangsung (sudah disetujui).</p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-3">
                @forelse ($ongoing as $m)
                    @php $id = $m['id']; @endphp
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200" wire:key="o-{{ $id }}">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($m['meeting_title'] ?? 'M',0,1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $m['meeting_title'] }}</div>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['date'] }}</span>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['time'] }}–{{ $m['time_end'] }}</span>
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100">{{ $m['room'] }}</span>
                                </div>
                                <div class="mt-1 text-[12px] text-gray-600">{{ $m['participants'] }} peserta</div>
                            </div>
                            <div class="text-[10px] font-mono text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">ID {{ $id }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 text-sm">Belum ada rapat berjalan.</div>
                @endforelse
            </div>
        </section>
    </main>
</div>
