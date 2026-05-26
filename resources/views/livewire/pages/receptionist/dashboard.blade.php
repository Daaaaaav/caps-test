<div class="min-h-screen bg-background" wire:poll.60s>
    <main class="px-4 sm:px-6 py-6 space-y-6">

        {{-- Page header — simple greeting --}}
        <x-page-header
            title="Dashboard"
            subtitle="Overview of the last 7 days across all modules."
        />

        {{-- KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card
                label="Room Bookings"
                :value="$weeklyRoomBookingsCount"
                icon="heroicon-o-calendar-days"
                href="{{ route('receptionist.schedule') }}"
            />
            <x-stat-card
                label="Vehicle Bookings"
                :value="$weeklyVehicleBookingsCount"
                icon="heroicon-o-truck"
                href="{{ route('receptionist.bookingvehicle') }}"
            />
            <x-stat-card
                label="Guest Visits"
                :value="$weeklyGuestsCount"
                icon="heroicon-o-user-group"
                href="{{ route('receptionist.guestbook') }}"
            />
            <x-stat-card
                label="Documents / Packages"
                :value="$weeklyDocsCount"
                icon="heroicon-o-archive-box"
                href="{{ route('receptionist.docpackform') }}"
            />
        </section>

        {{-- Quick Actions + Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Recent Activity — spans 2 cols --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Latest Room Bookings --}}
                <div class="bg-card border border-border rounded-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Recent Room Bookings</h3>
                        <a href="{{ route('receptionist.bookinghistory') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">View all →</a>
                    </div>
                    @if($latestBookingRooms->isEmpty())
                        <x-empty-state icon="heroicon-o-calendar-days" title="No room bookings" description="No bookings found in the last 7 days." />
                    @else
                        <div class="divide-y divide-border">
                            @foreach($latestBookingRooms as $br)
                                <div class="flex items-center justify-between px-4 py-3 hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-md bg-muted flex items-center justify-center shrink-0">
                                            <x-heroicon-o-calendar-days class="w-4 h-4 text-muted-foreground" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-foreground truncate">{{ $br['title'] }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $br['date'] }} · {{ $br['time'] }}</p>
                                        </div>
                                    </div>
                                    <x-status-badge :status="$br['status']" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Latest Guest Entries --}}
                <div class="bg-card border border-border rounded-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Recent Guests</h3>
                        <a href="{{ route('receptionist.guestbookhistory') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">View all →</a>
                    </div>
                    @if($latestGuests->isEmpty())
                        <x-empty-state icon="heroicon-o-user-group" title="No guests" description="No guest entries found recently." />
                    @else
                        <div class="divide-y divide-border">
                            @foreach($latestGuests as $g)
                                <div class="flex items-center justify-between px-4 py-3 hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-semibold shrink-0">
                                            {{ strtoupper(substr($g['name'], 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-foreground truncate">{{ $g['name'] }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $g['purpose'] }} · {{ $g['date'] }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-muted-foreground font-mono">{{ $g['time_in'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Quick Actions + Latest Docs --}}
            <div class="space-y-4">

                {{-- Quick Actions --}}
                <div class="bg-card border border-border rounded-lg">
                    <div class="px-4 py-3 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Quick Actions</h3>
                    </div>
                    <div class="p-3 space-y-1.5">
                        <a href="{{ route('receptionist.guestbook') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-muted transition-colors group">
                            <div class="w-8 h-8 rounded-md bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <x-heroicon-o-user-plus class="w-4 h-4 text-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">New Guest Entry</p>
                                <p class="text-xs text-muted-foreground">Register a visitor</p>
                            </div>
                        </a>
                        <a href="{{ route('receptionist.schedule') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-muted transition-colors group">
                            <div class="w-8 h-8 rounded-md bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Book a Room</p>
                                <p class="text-xs text-muted-foreground">Schedule a meeting room</p>
                            </div>
                        </a>
                        <a href="{{ route('receptionist.docpackform') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-muted transition-colors group">
                            <div class="w-8 h-8 rounded-md bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <x-heroicon-o-document-text class="w-4 h-4 text-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">DocPac Form</p>
                                <p class="text-xs text-muted-foreground">Log a document or package</p>
                            </div>
                        </a>
                        <a href="{{ route('receptionist.bookingvehicle') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-muted transition-colors group">
                            <div class="w-8 h-8 rounded-md bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <x-heroicon-o-truck class="w-4 h-4 text-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-foreground">Book Vehicle</p>
                                <p class="text-xs text-muted-foreground">Reserve a vehicle</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Latest Documents & Packages --}}
                <div class="bg-card border border-border rounded-lg">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                        <h3 class="text-sm font-semibold text-card-foreground">Recent Docs / Packages</h3>
                        <a href="{{ route('receptionist.docpackhistory') }}" class="text-xs text-muted-foreground hover:text-foreground transition-colors">View all →</a>
                    </div>
                    @if($latestDocs->isEmpty())
                        <x-empty-state icon="heroicon-o-archive-box" title="No documents" description="No documents or packages recorded." />
                    @else
                        <div class="divide-y divide-border">
                            @foreach($latestDocs as $d)
                                <div class="px-4 py-3 hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-foreground truncate">{{ $d['item'] }}</p>
                                        <x-status-badge :status="$d['status']" />
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ $d['type'] }} · {{ $d['direction'] }} · {{ $d['created'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </main>
</div>