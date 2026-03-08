@php
$initials = function (?string $fullName): string {
$fullName = trim($fullName ?? '');
if ($fullName === '') return 'US';
$parts = preg_split('/\s+/', $fullName);
$first = strtoupper(mb_substr($parts[0] ?? 'U', 0, 1));
$last = strtoupper(mb_substr($parts[count($parts)-1] ?? $parts[0] ?? 'S', 0, 1));
return $first.$last;
};

$isCreate = request()->routeIs('create-ticket') || request()->is('create-ticket');
$isStatus = request()->routeIs('ticketstatus') || request()->is('ticketstatus');

$priority = strtolower($ticket->priority ?? '');
$status = strtolower($ticket->status ?? 'open');

$agents = collect($ticket->assignments ?? [])
->pluck('user.full_name')
->filter()
->unique()
->values();
$hasAgent = $agents->isNotEmpty();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Support Ticket System</h1>

            {{-- Navigation Tabs --}}
            <div class="inline-flex rounded-lg overflow-hidden bg-gray-100 border border-gray-200 self-start md:self-center">
                <a href="{{ route('create-ticket') }}"
                    @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors border-r border-gray-200 inline-flex items-center' , 'bg-gray-900 text-white'=> $isCreate,
                    'text-gray-700 hover:text-gray-900 hover:bg-gray-50' => ! $isCreate,
                    ])>
                    Create Ticket
                </a>
                <a href="{{ route('ticketstatus') }}"
                    @class([ 'px-3 md:px-4 py-2 text-sm font-medium transition-colors inline-flex items-center' , 'bg-gray-900 text-white'=> true, // Active state for this view context
                    ])>
                    Ticket Status
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Ticket Details & Comments (2/3 width) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Ticket Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-mono font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                #{{ $ticket->ticket_id }}
                            </span>

                            @php
                            $isHigh = $priority === 'high';
                            $isMedium = $priority === 'medium';
                            $isLow = $priority === 'low' || $priority === '';
                            @endphp
                            <span @class([ 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border' , 'bg-orange-50 text-orange-800 border-orange-200'=> $isHigh,
                                'bg-yellow-50 text-yellow-800 border-yellow-200' => $isMedium,
                                'bg-gray-50 text-gray-700 border-gray-200' => $isLow,
                                ])>
                                <x-heroicon-o-bolt class="w-3 h-3" />
                                {{ $priority ? ucfirst($priority) : 'Low' }}
                            </span>
                        </div>

                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 break-words leading-tight">
                            {{ $ticket->subject }}
                        </h2>
                    </div>

                    {{-- Status Badge --}}
                    @php
                    $isOpen = $status === 'open';
                    $isAssignedOrProgress = in_array($status, ['assigned','in_progress','process'], true);
                    $isResolved = in_array($status, ['resolved','closed','complete'], true);
                    @endphp
                    <div class="flex flex-col items-end gap-2">
                        <span @class([ 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border shadow-sm' , 'bg-yellow-100 text-yellow-800 border-yellow-200'=> $isOpen,
                            'bg-blue-100 text-blue-800 border-blue-200' => $isAssignedOrProgress,
                            'bg-green-100 text-green-800 border-green-200' => $isResolved,
                            'bg-gray-100 text-gray-800 border-gray-200' => (! $isOpen && ! $isAssignedOrProgress && ! $isResolved),
                            ])>
                            @if($isResolved) <x-heroicon-o-check-circle class="w-4 h-4" />
                            @elseif($isAssignedOrProgress) <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin-slow" />
                            @else <x-heroicon-o-clock class="w-4 h-4" />
                            @endif
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="prose prose-sm max-w-none text-gray-700 bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="whitespace-pre-wrap leading-relaxed">{{ $ticket->description }}</p>
                </div>

                {{-- Attachments --}}
                @if(method_exists($ticket, 'attachments') && $ticket->relationLoaded('attachments') && $ticket->attachments->count())
                <div class="mt-6 pt-4 border-t border-dashed border-gray-200">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                        <x-heroicon-o-paper-clip class="w-4 h-4" />
                        Attachments ({{ $ticket->attachments->count() }})
                    </div>

                    <div class="space-y-3 text-sm">
                        @php
                        // 1. Define allowed image extensions explicitly
                        $okImg = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];

                        // 2. Map and Normalize Data
                        $atts = collect($ticket->attachments ?? [])
                        ->map(function ($a) {
                        $url = (string) ($a->file_url ?? '');
                        $name = (string) ($a->original_filename ?? '');
                        // Force extension check from filename
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                        return (object) [
                        'url' => $url,
                        'name' => $name ?: basename($url) ?: 'attachment',
                        'ext' => $ext,
                        ];
                        })
                        ->filter(fn($x) => $x->url);

                        // 3. Separate Images from Documents
                        $images = $atts->filter(fn($x) => in_array($x->ext, $okImg, true))->values();
                        $others = $atts->reject(fn($x) => in_array($x->ext, $okImg, true))->values();
                        @endphp

                        {{-- A. IMAGES GRID --}}
                        @if ($images->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                            @foreach ($images as $img)
                            <a href="{{ $img->url }}" target="_blank" class="block group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50 hover:border-gray-400 transition">
                                <img src="{{ $img->url }}"
                                    alt="{{ $img->name }}"
                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />

                                {{-- Optional Hover overlay --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                            </a>
                            @endforeach
                        </div>
                        @endif

                        {{-- B. OTHER FILES LIST (PDFs, Docs, Zips) --}}
                        @if ($others->isNotEmpty())
                        <div class="grid grid-cols-1 gap-2">
                            @foreach ($others as $f)
                            <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
                                {{-- File Icon Box --}}
                                <div class="w-10 h-10 rounded-md bg-gray-900 text-white flex items-center justify-center shrink-0 text-[10px] font-bold uppercase">
                                    {{ $f->ext ?: 'FILE' }}
                                </div>

                                {{-- Filename --}}
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-medium text-gray-900" title="{{ $f->name }}">
                                        {{ $f->name }}
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="shrink-0 flex items-center gap-2">
                                    <a href="{{ $f->url }}" target="_blank"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 hover:bg-gray-100 text-gray-700 transition">
                                        Open
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                {{-- Meta Footer --}}
                <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-500">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                            <span>Created {{ optional($ticket->created_at)->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-o-arrow-path class="w-4 h-4 text-gray-400" />
                            <span>Updated {{ optional($ticket->updated_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Discussion Card --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
                    Discussion
                </h3>

                {{-- CONDITIONAL COMMENT FORM START --}}
                @if ($canComment)
                    <form wire:submit.prevent="addComment" class="mb-8">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 hidden sm:block">
                                @php $meInitials = $initials(auth()->user()->full_name ?? auth()->user()->name ?? 'User'); @endphp
                                <span class="inline-flex h-8 w-8 rounded-full bg-black text-white items-center justify-center text-xs font-bold ring-2 ring-white shadow-sm">
                                    {{ $meInitials }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 relative">
                                <textarea
                                    wire:model.defer="newComment"
                                    rows="3"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-black focus:ring-1 focus:ring-black transition-colors resize-none shadow-sm"
                                    placeholder="Type your reply here..."></textarea>

                                @error('newComment')
                                <div class="text-red-600 text-xs mt-1 flex items-center gap-1">
                                    <x-heroicon-o-exclamation-circle class="w-3 h-3" /> {{ $message }}
                                </div>
                                @enderror

                                <div class="mt-2 flex justify-end">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800 transition-all shadow-sm hover:shadow">
                                        <x-heroicon-o-paper-airplane class="w-3.5 h-3.5 -rotate-45 translate-y-[-1px]" />
                                        Post Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    {{-- Display a message if commenting is not allowed --}}
                    @php
                        $isClosed = in_array($status, ['resolved', 'closed', 'complete'], true);
                    @endphp

                    @if ($isClosed)
                        <div class="mb-8 p-4 bg-gray-50 text-center rounded-lg border border-gray-200">
                            <p class="text-sm font-medium text-gray-700">
                                This ticket is {{ str_replace('_', ' ', ucfirst($status)) }} and can no longer receive comments.
                            </p>
                        </div>
                    @elseif($canViewComments)
                        {{-- The ticket is NOT closed, but the user still can't comment (e.g., they are a non-admin user/agent not assigned) --}}
                        <div class="mb-8 p-4 bg-yellow-50 text-center rounded-lg border border-yellow-200">
                            <p class="text-sm font-medium text-yellow-800">
                                You cannot post comments on this ticket. Only the creator and Assigned agents.
                            </p>
                        </div>
                    @endif
                @endif
                {{-- CONDITIONAL COMMENT FORM END --}}


                <div class="space-y-6 relative 
                    @if($canViewComments) 
                        before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:ml-[19px] before:h-full before:w-0.5 before:bg-gray-100 
                    @endif
                ">
                    @if ($canViewComments)
                        @forelse ($ticket->comments as $comment)
                        @php
                        $isMine = $comment->user_id === auth()->id();
                        $name = $comment->user->full_name ?? $comment->user->name ?? 'User';
                        $init = $initials($name);
                        
                        // Check if the current user has read this comment by checking the loaded 'reads' relationship.
                        $isUnread = ! $isMine && $comment->reads->isEmpty(); 
                        @endphp

                        <div class="relative flex gap-3 group">
                            <div class="flex-shrink-0 relative z-10">
                                <span @class([ 'inline-flex h-8 w-8 rounded-full items-center justify-center text-[10px] font-bold ring-4 ring-white shadow-sm' , 'bg-black text-white'=> $isMine,
                                    'bg-white border-2 border-gray-200 text-gray-600' => ! $isMine,
                                    ])>
                                    {{ $init }}
                                </span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">{{ $name }}</span>
                                        @if($isMine)
                                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-medium border border-gray-200">You</span>
                                        @endif
                                        
                                        {{-- UNREAD BADGE --}}
                                        @if($isUnread)
                                        <span class="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-bold border border-red-200 animate-pulse">
                                            <x-heroicon-o-eye-slash class="w-3 h-3 inline-block align-text-top mr-0.5" />
                                            UNREAD
                                        </span>
                                        @endif
                                        {{-- END UNREAD BADGE --}}
                                        
                                    </div>
                                    <span class="text-[10px] text-gray-400" title="{{ $comment->created_at->format('d M Y H:i') }}">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <div @class([ 'rounded-xl px-4 py-3 text-sm shadow-sm border leading-relaxed' , 'bg-gray-50 border-gray-200 text-gray-800 rounded-tl-none'=> ! $isMine,
                                    'bg-white border-black text-gray-900 rounded-tl-none ring-1 ring-black/5' => $isMine,
                                    ])>
                                    <p class="whitespace-pre-wrap">{{ $comment->comment_text }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="relative z-10 bg-white p-6 text-center rounded-xl border-2 border-dashed border-gray-200 mx-4">
                            <div class="mx-auto h-12 w-12 text-gray-300 mb-2">
                                <x-heroicon-o-chat-bubble-oval-left-ellipsis class="w-full h-full" />
                            </div>
                            <p class="text-sm text-gray-500">No comments yet.</p>
                            @if($canComment)
                                <p class="text-xs text-gray-400">Start the conversation by posting a reply above.</p>
                            @endif
                        </div>
                        @endforelse
                    @else
                        {{-- Agent is neither assigned, nor admin, nor the requester --}}
                        <div class="relative z-10 bg-yellow-50 p-6 text-center rounded-xl border-2 border-dashed border-yellow-200 mx-4">
                            <div class="mx-auto h-12 w-12 text-yellow-500 mb-2">
                                <x-heroicon-o-lock-closed class="w-full h-full" />
                            </div>
                            <p class="text-sm font-semibold text-yellow-800">
                                Access Restricted
                            </p>
                            <p class="text-xs text-yellow-700 mt-1">
                                You must be the ticket creator or an Assigned Agent to view the discussion.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Sidebar Info (1/3 width) --}}
        <div class="space-y-6">
            {{-- Info Card --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                    Ticket Info
                </h3>

                <div class="space-y-3 text-sm">

                    {{-- Requester Card --}}
                    <div class="group flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white shadow-sm hover:border-indigo-200 transition-colors">
                        <span class="flex-shrink-0 p-2 rounded-full bg-indigo-50 text-indigo-600">
                            <x-heroicon-o-user class="w-5 h-5" />
                        </span>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-xs text-gray-400 font-medium mb-0.5">Requested By</span>
                            <span class="font-semibold text-gray-900 truncate">{{ $ticket->user->full_name ?? 'Unknown User' }}</span>
                            <span class="text-[10px] text-gray-500 truncate flex items-center gap-1 mt-0.5">
                                {{ $ticket->requesterDepartment->department_name ?? 'No Dept' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        {{-- Department --}}
                        <div class="flex items-center justify-between p-2.5 rounded-md bg-gray-50 border border-gray-100">
                            <span class="text-xs font-medium text-gray-500">Department</span>
                            <span class="text-xs font-semibold text-gray-700">
                                {{ $ticket->department->department_name ?? '-' }}
                            </span>
                        </div>

                        {{-- Agent --}}
                        <div class="flex items-center justify-between p-2.5 rounded-md bg-gray-50 border border-gray-100">
                            <span class="text-xs font-medium text-gray-500">Assigned Agent</span>

                            @if($hasAgent)
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-500" />
                                <span class="text-xs font-semibold text-gray-900">{{ $agents->join(', ') }}</span>
                            </div>
                            @else
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wide">
                                Pending
                            </span>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Status Changer (Only if allowed) --}}
                @if($canEditStatus ?? false)
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <label class="block text-xs font-medium text-gray-500 mb-2">Update Status</label>
                    <form wire:submit.prevent="updateStatus" class="space-y-2">
                        <select
                            wire:model="statusEdit"
                            class="w-full rounded-lg border-gray-300 text-gray-900 text-sm focus:ring-black focus:border-black">
                            @foreach (($allowedStatuses ?? ['OPEN','IN_PROGRESS','RESOLVED','CLOSED']) as $st)
                            <option value="{{ $st }}">{{ str_replace('_',' ', $st) }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="w-full flex justify-center items-center gap-2 px-3 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors">
                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                            Update
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-sm border-2 border-black p-4 md:p-5">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">
                    Actions
                </h3>
                <a href="{{ route('ticketstatus') }}"
                    class="flex items-center justify-center w-full gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors text-sm">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    Goes Back
                </a>
            </div>
        </div>
    </div>
</div>
<script>
// JavaScript for handling Livewire-dispatched events in the browser console.
document.addEventListener('DOMContentLoaded', function () {
    // Listener for the console logging event dispatched from Livewire components
    Livewire.on('consoleLogEvent', ({ message, error, file, line }) => {
        console.error('--- LIVEWIRE SERVER ERROR ---');
        console.error('Message:', message);
        console.error('Error:', error);
        console.error('File:', file);
        console.error('Line:', line);
        console.error('-----------------------------');
        
        // IMPORTANT: The line number above should point you to the source of the crash!
    });
});
</script>