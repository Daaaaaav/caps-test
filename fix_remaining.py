import os

def replace_all(path, replacements):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    count = 0
    for old, new in replacements:
        if old in content:
            content = content.replace(old, new)
            count += 1
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Done: {os.path.basename(path)} ({count} replacements)")

base = r"c:\laragon\www\KRB-System-Caps-main\Capstone-copy\resources\views\livewire\pages\receptionist"

# ── 1. bookingvehicle.blade.php ──────────────────────────────────────────────
replace_all(os.path.join(base, "bookingvehicle.blade.php"), [
    # Hero title
    ('<h2 class="text-lg sm:text-xl font-semibold">Vehicle Booking</h2>',
     "<h2 class=\"text-lg sm:text-xl font-semibold\">{{ __('app.vehicle_booking_title') }}</h2>"),
    # Hero subtitle
    ('Isi form di bawah untuk mengajukan peminjaman kendaraan atas nama user/departemen tertentu.',
     "{{ __('app.vehicle_booking_subtitle') }}"),
    # Toggle: Room Booking
    ('<span>Room Booking</span>',
     "<span>{{ __('app.booking_room') }}</span>"),
    # Toggle: Vehicle Status
    ('<span>Vehicle Status</span>',
     "<span>{{ __('app.vehicle_status_menu') }}</span>"),
    # Submit button
    ('<span>Submit Booking</span>',
     "<span>{{ __('app.submit_booking') }}</span>"),
    # Select User option
    ("<option value=\"\">— Select User —</option>",
     "<option value=\"\">{{ __('app.select_user') }}</option>"),
    # No users found option
    ("<option value=\"\">— No users found —</option>",
     "<option value=\"\">{{ __('app.no_users_found') }}</option>"),
])

# ── 2. vehiclestatus.blade.php ───────────────────────────────────────────────
replace_all(os.path.join(base, "vehiclestatus.blade.php"), [
    # Approve button (card - pending)
    ('>Approve\n                                            </button>',
     ">{{ __('app.approve') }}\n                                            </button>"),
    # Reject button (card - pending)
    ('>Reject\n                                            </button>',
     ">{{ __('app.reject') }}\n                                            </button>"),
    # Approve button (table)
    ('>Approve\n                                                            </button>',
     ">{{ __('app.approve') }}\n                                                            </button>"),
    # Reject button (table)
    ('>Reject\n                                                            </button>',
     ">{{ __('app.reject') }}\n                                                            </button>"),
    # Mark Returned (card)
    ('>Mark Returned\n                                            </button>',
     ">{{ __('app.mark_returned') }}\n                                            </button>"),
    # Mark Done (card)
    ('>Mark Done\n                                                </button>',
     ">{{ __('app.mark_done') }}\n                                                </button>"),
    # Mark Returned (table)
    ('>Mark Returned\n                                                            </button>',
     ">{{ __('app.mark_returned') }}\n                                                            </button>"),
    # Mark Done (table)
    ('>Mark Done\n                                                            </button>',
     ">{{ __('app.mark_done') }}\n                                                            </button>"),
    # Wait for after photos
    ('Wait for after photos',
     "{{ __('app.wait_after_photos') }}"),
])

# ── 3. bookings-approval.blade.php ──────────────────────────────────────────
replace_all(os.path.join(base, "bookings-approval.blade.php"), [
    # Tab: Pending
    ('>Pending\n                                </button>',
     ">{{ __('app.pending') }}\n                                </button>"),
    # Tab: Ongoing
    ('>Ongoing\n                                </button>',
     ">{{ __('app.ongoing') }}\n                                </button>"),
    # Type scope: All
    ('>All\n                            </button>',
     ">{{ __('app.all') }}\n                            </button>"),
    # Type scope: Offline
    ('>Offline\n                            </button>',
     ">{{ __('app.offline') }}\n                            </button>"),
    # Type scope: Online
    ('>Online\n                            </button>',
     ">{{ __('app.online') }}\n                            </button>"),
    # Room filter badge
    ('<span>Room: {{ $activeRoom[\'label\'] ?? \'Unknown\' }}</span>',
     "<span>{{ __('app.room') }}: {{ $activeRoom['label'] ?? __('app.no_data') }}</span>"),
    # No room filter badge
    ('<span>No room filter</span>',
     "<span>{{ __('app.no_room_filter') }}</span>"),
    # Approve button (card)
    ('>Approve\n                                                </button>',
     ">{{ __('app.approve') }}\n                                                </button>"),
    # Reject button (card)
    ('>Reject\n                                                </button>',
     ">{{ __('app.reject') }}\n                                                </button>"),
    # Detail button (card)
    ('>Detail\n                                                </button>',
     ">{{ __('app.detail') }}\n                                                </button>"),
    # Approve button (table)
    ('>Approve\n                                                         </button>',
     ">{{ __('app.approve') }}\n                                                         </button>"),
    # Reject button (table)
    ('>Reject\n                                                         </button>',
     ">{{ __('app.reject') }}\n                                                         </button>"),
    # Detail button (table)
    ('>Detail\n                                                         </button>',
     ">{{ __('app.detail') }}\n                                                         </button>"),
    # Filter by Room sidebar
    ('>Filter by Room</h3>',
     ">{{ __('app.filter_by_vehicle') }}</h3>"),
    # All Rooms
    ('>All Rooms</span>',
     ">{{ __('app.all') }}</span>"),
    # Active badge
    ('>Active</span>',
     ">{{ __('app.active') }}</span>"),
    # Room chip in card
    ("Room: {{ $b->room?->room_name ?? 'Not selected' }}",
     "{{ __('app.room') }}: {{ $b->room?->room_name ?? __('app.not_selected') }}"),
    ("Room: {{ $b->room?->room_name ?? '—' }}",
     "{{ __('app.room') }}: {{ $b->room?->room_name ?? '—' }}"),
    # Created prefix
    ("Created: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}",
     "{{ __('app.created') }}: {{ optional($b->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') }}"),
])

# ── 4. booking-history.blade.php ────────────────────────────────────────────
replace_all(os.path.join(base, "booking-history.blade.php"), [
    # Tab: Done
    ('>Done\n                                </button>',
     ">{{ __('app.done') }}\n                                </button>"),
    # Tab: Rejected
    ('>Rejected\n                                </button>',
     ">{{ __('app.rejected') }}\n                                </button>"),
    # Type scope: All
    ('>All\n                            </button>',
     ">{{ __('app.all') }}\n                            </button>"),
    # Type scope: Offline
    ('>Offline\n                            </button>',
     ">{{ __('app.offline') }}\n                            </button>"),
    # Type scope: Online
    ('>Online\n                            </button>',
     ">{{ __('app.online') }}\n                            </button>"),
    # Status badge: DONE
    ('>DONE\n                                            </span>',
     ">{{ strtoupper(__('app.done')) }}\n                                            </span>"),
    # Status badge: REJECTED
    ('>REJECTED\n                                </span>',
     ">{{ strtoupper(__('app.rejected')) }}\n                                </span>"),
    # Status badge: DELETED
    ('>DELETED\n                                                </span>',
     ">{{ strtoupper(__('app.deleted')) }}\n                                                </span>"),
    # Edit button (card done)
    ('>Edit\n                                                        </button>',
     ">{{ __('app.edit') }}\n                                                        </button>"),
    # Delete button (card done)
    ('>Delete\n                                                        </button>',
     ">{{ __('app.delete') }}\n                                                        </button>"),
    # Restore button (card done)
    ('>Restore\n                                                        </button>',
     ">{{ __('app.restore') }}\n                                                        </button>"),
    # Join link
    ('>Join link\n                                                                    </a>',
     ">{{ __('app.join_link') }}\n                                                                    </a>"),
    # Room chip
    ("Room: {{ optional($row->room)->room_name ?? '—' }}",
     "{{ __('app.room') }}: {{ optional($row->room)->room_name ?? '—' }}"),
    # Requested by
    ("Requested by <span class=\"font-medium text-gray-800\">{{ $row->user?->name",
     "{{ __('app.requested_by') }} <span class=\"font-medium text-gray-800\">{{ $row->user?->name"),
    # No room filter
    ('<span>No room filter</span>',
     "<span>{{ __('app.no_room_filter') }}</span>"),
])

# ── 5. docpackstatus.blade.php ───────────────────────────────────────────────
replace_all(os.path.join(base, "docpackstatus.blade.php"), [
    # Tab: Pending
    ('>Pending\n                            </button>',
     ">{{ __('app.pending') }}\n                            </button>"),
    # Tab: Stored
    ('>Stored\n                            </button>',
     ">{{ __('app.stored') }}\n                            </button>"),
    # Type: All
    ('>All\n                            </button>',
     ">{{ __('app.all') }}\n                            </button>"),
    # Type: Document
    ('>Document\n                            </button>',
     ">{{ __('app.type') }}\n                            </button>"),
    # Type: Package
    ('>Package\n                            </button>',
     ">{{ __('app.type') }}\n                            </button>"),
    # Status badge: Pending
    ('>Pending\n                                                            </span>',
     ">{{ __('app.pending') }}\n                                                            </span>"),
    # Status badge: Stored
    ('>Stored\n                                                            </span>',
     ">{{ __('app.stored') }}\n                                                            </span>"),
    # Edit button (pending card)
    ('>Edit\n                                                </button>',
     ">{{ __('app.edit') }}\n                                                </button>"),
    # Store button (pending card)
    ('>Store\n                                                </button>',
     ">{{ __('app.stored') }}\n                                                </button>"),
    # Edit button (stored card)
    ('>Edit\n                                                </button>',
     ">{{ __('app.edit') }}\n                                                </button>"),
    # Table: Edit
    ('>Edit\n                                                    </button>',
     ">{{ __('app.edit') }}\n                                                    </button>"),
    # Table: Store
    ('>Store\n                                                    </button>',
     ">{{ __('app.stored') }}\n                                                    </button>"),
    # From: label
    ('>From: <span class="font-semibold">',
     ">{{ __('app.sender') }}: <span class=\"font-semibold\">"),
    # To: label
    ('>To: <span class="font-semibold">',
     ">{{ __('app.receiver') }}: <span class=\"font-semibold\">"),
])

print("\nAll done!")
