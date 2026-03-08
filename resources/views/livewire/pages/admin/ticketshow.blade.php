<div class="bg-gray-50">
    @php
        use Carbon\Carbon;
        $card = 'bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden';
        $label = 'block text-sm font-medium text-gray-700 mb-2';
        $input =
            'w-full h-10 px-3 rounded-lg border border-gray-300 text-gray-800 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 bg-white transition';
        $btnBlk =
            'px-3 py-2 text-xs font-medium rounded-lg bg-gray-900 text-white hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900/20 transition';
        $chip = 'inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-gray-100 text-xs';

        // helper initials
        $initials = function (?string $fullName): string {
            $fullName = trim($fullName ?? '');
            if ($fullName === '') {
                return 'US';
            }
            $parts = preg_split('/\s+/', $fullName);
            $first = strtoupper(mb_substr($parts[0] ?? 'U', 0, 1));
            $last = strtoupper(mb_substr($parts[count($parts) - 1] ?? ($parts[0] ?? 'S'), 0, 1));
            return $first . $last;
        };
    @endphp

    <main class="px-4 sm:px-6 py-6 space-y-8">
        {{-- HERO --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 to-black text-white shadow-2xl">
            <div class="relative z-10 p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <x-heroicon-o-calendar-days class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg sm:text-xl font-semibold">Ticket Details</h2>

                        {{-- NEW: owner & department --}}
                        @php
                            $ownerName = $t->user->full_name ?? 'Unknown User';
                            $deptName =
                                $t->department->department_name ??
                                ($t->user->department->department_name ??
                                    null ?? // fallback via user kalau ada
                                    '-');
                            $ownerInit = $initials($ownerName);
                        @endphp
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <span
                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white/10 border border-white/20">
                                <span
                                    class="inline-flex h-6 w-6 rounded-full bg-white/20 text-white items-center justify-center text-[11px] font-bold">
                                    {{ $ownerInit }}
                                </span>
                                <span class="font-medium">{{ $ownerName }}</span>
                            </span>
                            <span
                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-white/10 border border-white/20">
                                <span class="opacity-80">Dept:</span>
                                <span class="font-medium">{{ $deptName }}</span>
                            </span>
                        </div>
                        {{-- /NEW --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Main info --}}
            <section class="lg:col-span-2 {{ $card }}">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-base font-semibold text-gray-900">Ticket Info</h2>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Subject (readonly) --}}
                    <div>
                        <label class="{{ $label }}">Subject</label>
                        <div
                            class="w-full h-10 flex items-center px-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-800">
                            {{ $t->subject }}
                        </div>
                    </div>

                    {{-- Description (readonly) --}}
                    <div>
                        <label class="{{ $label }}">Description</label>
                        <div class="p-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-800 whitespace-pre-line">
                            {{ $t->description }}
                        </div>
                    </div>

                    {{-- Priority (readonly) --}}
                    <div>
                        <label class="{{ $label }}">Priority</label>
                        <div class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-gray-800 capitalize">
                            {{ $t->priority }}
                        </div>
                    </div>

                    {{-- Editable fields --}}
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $label }}">Status</label>
                            <select wire:model.defer="status" class="{{ $input }}">
                                <option value="open">Open</option>
                                <option value="in_progress" @disabled(!$agent_id)>In Progress</option>
                                <option value="resolved" @disabled(!$agent_id)>Resolved</option>
                                <option value="closed" @disabled(!$agent_id)>Closed</option>
                            </select>
                            @error('status')
                                <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                            @enderror

                            @if (!$agent_id)
                                <p class="mt-1 text-[11px] text-gray-500">
                                    Assign ticket ke agent terlebih dahulu untuk mengubah status ke In Progress, Resolved,
                                    atau Closed.
                                </p>
                            @endif
                        </div>

                        <div>
                            <label class="{{ $label }}">Assigned Agent</label>
                            <select wire:model.defer="agent_id" class="{{ $input }}">
                                <option value="">— Unassigned —</option>
                                @foreach ($agents as $a)
                                    <option value="{{ $a->user_id }}">{{ $a->full_name }}</option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <div class="text-xs text-rose-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2">
                        <button wire:click="save" class="{{ $btnBlk }}" wire:loading.attr="disabled">
                            Save Changes
                        </button>
                    </div>
                </div>
            </section>

            {{-- Attachments --}}
            <aside class="{{ $card }}">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Attachments</h3>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    @php
                        $okImg = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
                        $atts = collect($t->attachments ?? [])
                            ->map(function ($a) {
                                $url = (string) ($a->file_url ?? '');
                                $name = (string) ($a->original_filename ?? '');
                                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                return (object) [
                                    'url' => $url,
                                    'name' => $name ?: basename($url) ?: 'attachment',
                                    'bytes' => $a->bytes ?? null,
                                    'ext' => $ext,
                                ];
                            })
                            ->filter(fn($x) => $x->url);
                    @endphp

                    @if ($atts->isEmpty())
                        <div class="text-gray-500">No attachments.</div>
                    @else
                        {{-- images --}}
                        @php $images = $atts->filter(fn($x)=>in_array($x->ext,$okImg,true))->values(); @endphp
                        @if ($images->isNotEmpty())
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                @foreach ($images as $img)
                                    <button wire:click="openPreview('{{ $img->url }}')"
                                        class="block focus:outline-none">
                                        <div
                                            class="relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-white hover:scale-105 transition">
                                            <img src="{{ $img->url }}" alt="{{ $img->name }}"
                                                class="absolute inset-0 w-full h-full object-cover" />
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- others --}}
                        @php $others = $atts->reject(fn($x)=>in_array($x->ext,$okImg,true))->values(); @endphp
                        @if ($others->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($others as $f)
                                    <div
                                        class="flex items-center gap-3 p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
                                        <div
                                            class="w-8 h-8 rounded-md bg-gray-900 text-white flex items-center justify-center shrink-0 text-[10px] font-bold">
                                            {{ strtoupper($f->ext ?: 'FILE') }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-medium text-gray-900"
                                                title="{{ $f->name }}">{{ $f->name }}</div>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2">
                                            <a href="{{ $f->url }}" target="_blank"
                                                class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-300 hover:bg-gray-100 transition">Open</a>
                                            <a href="{{ $f->url }}" download
                                                class="px-2.5 py-1.5 text-xs rounded-lg bg-gray-900 text-white hover:bg-black transition">Download</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </aside>
        </div>

        {{-- NEW: Discussion / Comments (Admin side) --}}
        <section class="{{ $card }}">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Discussion 💬</h3>
            </div>

            <div class="p-5 space-y-6">
                {{-- Composer --}}
                <form wire:submit.prevent="addComment" class="mb-2">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            @php $meInitials = $initials(auth()->user()->full_name ?? auth()->user()->name ?? 'User'); @endphp
                            <span
                                class="inline-flex h-10 w-10 rounded-full bg-gray-900 text-white items-center justify-center text-xs font-bold">
                                {{ $meInitials }}
                            </span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <textarea wire:model.defer="newComment" rows="3"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 transition"
                                placeholder="Tulis komentar..."></textarea>
                            @error('newComment')
                                <div class="text-rose-600 text-xs mt-1">{{ $message }}</div>
                            @enderror

                            <div class="mt-3 flex items-center justify-end">
                                <button type="submit" class="{{ $btnBlk }}">Post Comment</button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Chat list --}}
                <div class="space-y-5">
                    @forelse ($t->comments as $comment)
                        @php
                            $isMine = $comment->user_id === auth()->id();
                            $name = $comment->user->full_name ?? ($comment->user->name ?? 'User');
                            $init = $initials($name);
                        @endphp

                        <div class="flex {{ $isMine ? 'flex-row' : 'flex-row-reverse' }} items-start gap-3">
                            {{-- Avatar --}}
                            <div class="flex-shrink-0">
                                <span
                                    class="inline-flex h-9 w-9 rounded-full
                                        {{ $isMine ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-800' }}
                                        items-center justify-center text-[11px] font-bold">
                                    {{ $init }}
                                </span>
                            </div>

                            {{-- Bubble --}}
                            <div class="max-w-[80%]">
                                <div
                                    class="flex items-center {{ $isMine ? 'justify-between' : 'flex-row-reverse justify-between' }} gap-3">
                                    <p class="text-xs font-semibold text-gray-700 truncate">{{ $name }}</p>
                                    <p class="text-[11px] text-gray-500"
                                        title="{{ $comment->created_at->format('Y-m-d H:i') }}">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div
                                    class="mt-1 rounded-xl
                                            {{ $isMine ? 'bg-gray-900 text-white border border-gray-900' : 'bg-gray-50 text-gray-900 border border-gray-200' }}
                                            px-4 py-3 shadow-sm">
                                    <p class="text-sm whitespace-pre-wrap leading-relaxed">
                                        {{ $comment->comment_text }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-600">
                            Belum ada komentar.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Modal for image preview --}}
        @if ($previewUrl)
            <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
                <div class="relative">
                    <img src="{{ $previewUrl }}" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-lg" />
                    <button wire:click="closePreview"
                        class="absolute top-2 right-2 bg-white/80 hover:bg-white text-gray-900 px-2 py-1 rounded-full">✕</button>
                </div>
            </div>
        @endif
    </main>
</div>