import os

def replace_all(path, replacements):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    count = 0
    for old, new in replacements:
        if old in content:
            content = content.replace(old, new)
            count += 1
        else:
            print(f"  NOT FOUND: {repr(old[:60])}")
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Done: {os.path.basename(path)} ({count} replacements)")

base = r"c:\laragon\www\KRB-System-Caps-main\Capstone-copy\resources\views\livewire\pages\receptionist"

# ── vehiclestatus.blade.php ──────────────────────────────────────────────────
# The status tab labels come from a PHP array in the blade — translate those
replace_all(os.path.join(base, "vehiclestatus.blade.php"), [
    # Status tab array labels
    ("@foreach(['pending'=>'Pending','approved'=>'Approved','on_progress'=>'On Progress','returned'=>'Returned'] as $key=>$lbl)",
     "@foreach(['pending'=>__('app.pending'),'approved'=>__('app.approved'),'on_progress'=>__('app.on_progress'),'returned'=>__('app.returned')] as $key=>$lbl)"),
    # Status color labels in PHP block
    ("'pending'      => ['bg'=>'bg-amber-100','text'=>'text-amber-800','label'=>'Pending'],",
     "'pending'      => ['bg'=>'bg-amber-100','text'=>'text-amber-800','label'=>__('app.pending')],"),
    ("'approved'     => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'Approved'],",
     "'approved'     => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>__('app.approved')],"),
    ("'on_progress'  => ['bg'=>'bg-blue-100','text'=>'text-blue-800','label'=>'On Progress'],",
     "'on_progress'  => ['bg'=>'bg-blue-100','text'=>'text-blue-800','label'=>__('app.on_progress')],"),
    ("'returned'     => ['bg'=>'bg-indigo-100','text'=>'text-indigo-800','label'=>'Returned'],",
     "'returned'     => ['bg'=>'bg-indigo-100','text'=>'text-indigo-800','label'=>__('app.returned')],"),
    ("'rejected'     => ['bg'=>'bg-rose-100','text'=>'text-rose-800','label'=>'Rejected'],",
     "'rejected'     => ['bg'=>'bg-rose-100','text'=>'text-rose-800','label'=>__('app.rejected')],"),
    ("'completed'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>'Completed'],",
     "'completed'    => ['bg'=>'bg-emerald-100','text'=>'text-emerald-800','label'=>__('app.completed')],"),
    # Card: Reject button
    ("                                                Reject\n                                            </button>",
     "                                                {{ __('app.reject') }}\n                                            </button>"),
    # Card: Approve button
    ("                                                Approve\n                                            </button>",
     "                                                {{ __('app.approve') }}\n                                            </button>"),
    # Card: Mark Returned
    ("                                                Mark Returned\n                                            </button>",
     "                                                {{ __('app.mark_returned') }}\n                                            </button>"),
    # Card: Mark Done
    ("                                                    Mark Done\n                                                </button>",
     "                                                    {{ __('app.mark_done') }}\n                                                </button>"),
    # Table: Reject
    ("                                                                Reject\n                                                            </button>",
     "                                                                {{ __('app.reject') }}\n                                                            </button>"),
    # Table: Approve
    ("                                                                Approve\n                                                            </button>",
     "                                                                {{ __('app.approve') }}\n                                                            </button>"),
    # Table: Mark Returned
    ("                                                                Mark Returned\n                                                            </button>",
     "                                                                {{ __('app.mark_returned') }}\n                                                            </button>"),
    # Table: Mark Done
    ("                                                                Mark Done\n                                                            </button>",
     "                                                                {{ __('app.mark_done') }}\n                                                            </button>"),
    # Wait for after photos
    ("Wait for after photos",
     "{{ __('app.wait_after_photos') }}"),
])

# ── booking-history.blade.php ────────────────────────────────────────────────
replace_all(os.path.join(base, "booking-history.blade.php"), [
    # Tab: Done
    ("                                        Done\n                                        </button>",
     "                                        {{ __('app.done') }}\n                                        </button>"),
    # Tab: Rejected
    ("                                        Rejected\n                                        </button>",
     "                                        {{ __('app.rejected') }}\n                                        </button>"),
    # Type scope: All
    ("                                All\n                            </button>",
     "                                {{ __('app.all') }}\n                            </button>"),
    # Type scope: Offline
    ("                                Offline\n                            </button>",
     "                                {{ __('app.offline') }}\n                            </button>"),
    # Type scope: Online
    ("                                Online\n                            </button>",
     "                                {{ __('app.online') }}\n                            </button>"),
    # Status badge DONE
    ("                                                            DONE\n                                                            </span>",
     "                                                            {{ strtoupper(__('app.done')) }}\n                                                            </span>"),
    # Status badge REJECTED
    ("                                                REJECTED\n                                                </span>",
     "                                                {{ strtoupper(__('app.rejected')) }}\n                                                </span>"),
    # Status badge DELETED (done tab)
    ("                                                                DELETED\n                                                                </span>",
     "                                                                {{ strtoupper(__('app.deleted')) }}\n                                                                </span>"),
    # Edit button (done card)
    ("                                                    Edit\n                                                </button>",
     "                                                    {{ __('app.edit') }}\n                                                </button>"),
    # Delete button (done card)
    ("                                                    Delete\n                                                    </button>",
     "                                                    {{ __('app.delete') }}\n                                                    </button>"),
    # Restore button (done card)
    ("                                                    Restore\n                                                    </button>",
     "                                                    {{ __('app.restore') }}\n                                                    </button>"),
    # Join link
    ("                                                                        Join link\n                                                                    </a>",
     "                                                                        {{ __('app.join_link') }}\n                                                                    </a>"),
    # Room chip
    ("Room: {{ optional($row->room)->room_name ?? '—' }}",
     "{{ __('app.room') }}: {{ optional($row->room)->room_name ?? '—' }}"),
    # Requested by
    ("Requested by <span class=\"font-medium text-gray-800\">{{ $row->user?->name",
     "{{ __('app.requested_by') }} <span class=\"font-medium text-gray-800\">{{ $row->user?->name"),
])

# ── bookings-approval.blade.php ──────────────────────────────────────────────
replace_all(os.path.join(base, "bookings-approval.blade.php"), [
    # Tab: Pending
    ("                                    Pending\n                                </button>",
     "                                    {{ __('app.pending') }}\n                                </button>"),
    # Tab: Ongoing
    ("                                    Ongoing\n                                </button>",
     "                                    {{ __('app.ongoing') }}\n                                </button>"),
    # Type scope: All
    ("                                All\n                            </button>",
     "                                {{ __('app.all') }}\n                            </button>"),
    # Type scope: Offline
    ("                                Offline\n                            </button>",
     "                                {{ __('app.offline') }}\n                            </button>"),
    # Type scope: Online
    ("                                Online\n                            </button>",
     "                                {{ __('app.online') }}\n                            </button>"),
    # Room filter badge
    ("<span>Room: {{ $activeRoom['label'] ?? 'Unknown' }}</span>",
     "<span>{{ __('app.room') }}: {{ $activeRoom['label'] ?? __('app.no_data') }}</span>"),
    # No room filter
    ("<span>No room filter</span>",
     "<span>{{ __('app.no_room_filter') }}</span>"),
    # Approve (card)
    ("                                                    Approve\n                                                </button>",
     "                                                    {{ __('app.approve') }}\n                                                </button>"),
    # Reject (card)
    ("                                                    Reject\n                                                </button>",
     "                                                    {{ __('app.reject') }}\n                                                </button>"),
    # Detail (card)
    ("                                                    Detail\n                                                </button>",
     "                                                    {{ __('app.detail') }}\n                                                </button>"),
    # Approve (table)
    ("                                                         Approve\n                                                         </button>",
     "                                                         {{ __('app.approve') }}\n                                                         </button>"),
    # Reject (table)
    ("                                                         Reject\n                                                         </button>",
     "                                                         {{ __('app.reject') }}\n                                                         </button>"),
    # Detail (table)
    ("                                                         Detail\n                                                         </button>",
     "                                                         {{ __('app.detail') }}\n                                                         </button>"),
    # Room chip in card
    ("Room: {{ $b->room?->room_name ?? 'Not selected' }}",
     "{{ __('app.room') }}: {{ $b->room?->room_name ?? __('app.not_selected') }}"),
    ("Room: {{ $b->room?->room_name ?? '—' }}",
     "{{ __('app.room') }}: {{ $b->room?->room_name ?? '—' }}"),
    # Created prefix
    ("Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}",
     "{{ __('app.created') }}: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}"),
    # Filter by Room sidebar
    (">Filter by Room</h3>",
     ">{{ __('app.filter_by_vehicle') }}</h3>"),
    # All Rooms
    (">All Rooms</span>",
     ">{{ __('app.all') }}</span>"),
    # Active badge
    (">Active</span>",
     ">{{ __('app.active') }}</span>"),
])

# ── docpackstatus.blade.php ───────────────────────────────────────────────────
replace_all(os.path.join(base, "docpackstatus.blade.php"), [
    # Tab: Pending
    ("                            Pending\n                            </button>",
     "                            {{ __('app.pending') }}\n                            </button>"),
    # Tab: Stored
    ("                            Stored\n                            </button>",
     "                            {{ __('app.stored') }}\n                            </button>"),
    # Type: All
    ("                            All\n                            </button>",
     "                            {{ __('app.all') }}\n                            </button>"),
    # Type: Document
    ("                            Document\n                            </button>",
     "                            Document\n                            </button>"),  # keep, no key needed
    # Type: Package
    ("                            Package\n                            </button>",
     "                            Package\n                            </button>"),  # keep, no key needed
    # Status badge: Pending (card)
    ("                                                            Pending\n                                                            </span>",
     "                                                            {{ __('app.pending') }}\n                                                            </span>"),
    # Status badge: Stored (card)
    ("                                                            Stored\n                                                            </span>",
     "                                                            {{ __('app.stored') }}\n                                                            </span>"),
    # Edit button (pending card)
    ("                                                Edit\n                                                </button>",
     "                                                {{ __('app.edit') }}\n                                                </button>"),
    # Store button (pending card)
    ("                                                Store\n                                                </button>",
     "                                                {{ __('app.stored') }}\n                                                </button>"),
    # Table: Edit
    ("                                                    Edit\n                                                    </button>",
     "                                                    {{ __('app.edit') }}\n                                                    </button>"),
    # Table: Store
    ("                                                    Store\n                                                    </button>",
     "                                                    {{ __('app.stored') }}\n                                                    </button>"),
    # From: label
    (">From: <span class=\"font-semibold\">",
     ">{{ __('app.sender') }}: <span class=\"font-semibold\">"),
    # To: label
    (">To: <span class=\"font-semibold\">",
     ">{{ __('app.receiver') }}: <span class=\"font-semibold\">"),
])

print("\nAll done!")
