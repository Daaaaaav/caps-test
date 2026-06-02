<flux:sidebar sticky collapsible="mobile" 
    @mouseenter="sidebarCollapsed = false"
    @mouseleave="sidebarCollapsed = true"
    class="bg-sidebar border-r border-sidebar-border lg:w-[var(--sbw)] overflow-y-auto overflow-x-hidden box-border shadow-2xl shadow-black/30">
    
    <!-- COLLAPSED STATE DOCK (shown when sidebarCollapsed is true, desktop only) -->
    <div x-show="sidebarCollapsed" class="sidebar-collapsed-container max-lg:hidden flex flex-col h-full justify-between items-center py-2 px-1 w-full select-none">
        <!-- Logo Area -->
        <div class="h-10 flex items-center justify-center mb-3 relative group">
            <img src="{{ $brandLogo }}" alt="Brand Logo" class="h-7 w-7 object-contain rounded-lg img-white drop-shadow-[0_0_8px_rgba(205,222,167,0.4)] transition-transform duration-300 hover:rotate-12" style="{{ $invertStyle }}" />
            <div class="sidebar-tooltip">{{ $brandName }}</div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 flex flex-col items-center gap-1.5 w-full">
            {{-- ===== CORE ===== --}}
            @php $dashActive = request()->routeIs('superadmin.dashboard'); @endphp
            <a href="{{ route('superadmin.dashboard') }}" class="sidebar-collapsed-item group {{ $dashActive ? 'active' : '' }}">
                @if($dashActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <div class="sidebar-tooltip">Dashboard</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== USER MANAGEMENT ===== --}}
            @php $recActive = request()->routeIs('superadmin.receptionists'); @endphp
            <a href="{{ route('superadmin.receptionists') }}" class="sidebar-collapsed-item group {{ $recActive ? 'active' : '' }}">
                @if($recActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <div class="sidebar-tooltip">Receptionists</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== ANALYTICS ===== --}}
            @php $roomActive = request()->routeIs('superadmin.room'); @endphp
            <a href="{{ route('superadmin.room') }}" class="sidebar-collapsed-item group {{ $roomActive ? 'active' : '' }}">
                @if($roomActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M10 21V11.5a1.5 1.5 0 0 1 3 0V21"/>
                    <line x1="8" y1="7" x2="16" y2="7"/>
                </svg>
                <div class="sidebar-tooltip">Room Bookings</div>
            </a>

            @php $vehActive = request()->routeIs('superadmin.vehicle'); @endphp
            <a href="{{ route('superadmin.vehicle') }}" class="sidebar-collapsed-item group {{ $vehActive ? 'active' : '' }}">
                @if($vehActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                    <circle cx="7" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                    <circle cx="17" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Vehicle Bookings</div>
            </a>

            @php $delActive = request()->routeIs('superadmin.delivery'); @endphp
            <a href="{{ route('superadmin.delivery') }}" class="sidebar-collapsed-item group {{ $delActive ? 'active' : '' }}">
                @if($delActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z" fill="currentColor" fill-opacity="0.15"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <div class="sidebar-tooltip">Deliveries</div>
            </a>

            @php $guestActive = request()->routeIs('superadmin.guestbook'); @endphp
            <a href="{{ route('superadmin.guestbook') }}" class="sidebar-collapsed-item group {{ $guestActive ? 'active' : '' }}">
                @if($guestActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Guestbook</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== AI & SECURITY SYSTEM ===== --}}
            @php $lstmActive = request()->routeIs('superadmin.lstm-predictions'); @endphp
            <a href="{{ route('superadmin.lstm-predictions') }}" class="sidebar-collapsed-item group {{ $lstmActive ? 'active' : '' }}">
                @if($lstmActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="2" fill="currentColor" fill-opacity="0.15"/>
                    <rect x="9" y="9" width="6" height="6" fill="currentColor" fill-opacity="0.2"/>
                    <line x1="9" y1="1" x2="9" y2="4"/>
                    <line x1="15" y1="1" x2="15" y2="4"/>
                    <line x1="9" y1="20" x2="9" y2="23"/>
                    <line x1="15" y1="20" x2="15" y2="23"/>
                    <line x1="20" y1="9" x2="23" y2="9"/>
                    <line x1="20" y1="15" x2="23" y2="15"/>
                    <line x1="1" y1="9" x2="4" y2="9"/>
                    <line x1="1" y1="15" x2="4" y2="15"/>
                </svg>
                <div class="sidebar-tooltip">Visitor Predictions</div>
            </a>

            @php $occActive = request()->routeIs('superadmin.occupancy'); @endphp
            <a href="{{ route('superadmin.occupancy') }}" class="sidebar-collapsed-item group {{ $occActive ? 'active' : '' }}">
                @if($occActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="m19 9-5 5-4-4-3 3"/>
                </svg>
                <div class="sidebar-tooltip">Occupancy Forecast</div>
            </a>

            @php $secActive = request()->routeIs('superadmin.ai-security'); @endphp
            <a href="{{ route('superadmin.ai-security') }}" class="sidebar-collapsed-item group {{ $secActive ? 'active' : '' }}">
                @if($secActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="m9 11 2 2 4-4"/>
                </svg>
                <div class="sidebar-tooltip">Security Reports</div>
            </a>
        </nav>

        <!-- Bottom Icons -->
        <div class="flex flex-col items-center gap-1.5 w-full mt-auto">
            @php $superSetActive = request()->routeIs('superadmin.settings'); @endphp
            <a href="{{ route('superadmin.settings') }}" class="sidebar-collapsed-item group {{ $superSetActive ? 'active' : '' }}">
                @if($superSetActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <div class="sidebar-tooltip">Settings</div>
            </a>

            @php $superHelpsActive = request()->routeIs('superadmin.help'); @endphp
            <a href="{{ route('superadmin.help') }}" class="sidebar-collapsed-item group {{ $superHelpsActive ? 'active' : '' }}">
                @if($superHelpsActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="sidebar-tooltip">Help</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            <!-- Profile Dropdown (collapsed) -->
            <div x-data="{ open: false }" class="relative w-full flex justify-center">
                <button
                    @click.stop="open = !open"
                    class="sidebar-collapsed-item group hover:bg-white/10 transition-colors focus:outline-none"
                >
                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr($fullName, 0, 1)) }}
                    </div>
                    <div class="sidebar-tooltip">{{ $fullName }}</div>
                </button>

                <!-- Logout popup anchored above the button -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.outside="open = false"
                    class="absolute bottom-full left-0 mb-2 w-48 rounded-xl bg-[#2a1f1a] border border-white/10 shadow-2xl shadow-black/40 z-[9999] overflow-hidden"
                    style="display: none;"
                >
                    <div class="px-3 py-2.5 border-b border-white/10">
                        <p class="text-xs font-semibold text-white truncate">{{ $fullName }}</p>
                        <p class="text-[10px] text-white/40 mt-0.5">Superadmin</p>
                    </div>
                    <button
                        type="submit"
                        form="logout-form"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPANDED STATE SIDEBAR (shown when sidebarCollapsed is false, or always on mobile) -->
    <div x-show="!sidebarCollapsed || isMobile" class="flex flex-col h-full justify-between w-full">
        {{-- HEADER --}}
        <flux:sidebar.header>
            <flux:sidebar.brand 
                href="{{ route('superadmin.dashboard') }}" 
                logo="{{ $brandLogo }}" 
                name="{{ $brandName }}" 
                class="text-white"
                style="{{ $invertStyle }}" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- NAVIGATION --}}
        <div x-data="{ search: '' }" class="px-3 pt-2 pb-1">
            <div class="relative mb-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg bg-white/10 border border-white/10 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-white/20"
                />
            </div>

            <flux:sidebar.nav>
                {{-- ===== CORE ===== --}}
                <flux:sidebar.item icon="home" href="{{ route('superadmin.dashboard') }}"
                    :current="request()->routeIs('superadmin.dashboard')"
                    x-show="!search || 'dashboard'.includes(search.toLowerCase())">
                    {{ __('app.dashboard') }}
                </flux:sidebar.item>

                {{-- ===== USER MANAGEMENT ===== --}}
                <flux:sidebar.group expandable heading="{{ __('app.user_management') }}"
                    x-show="!search || ['user management','receptionists'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item icon="users" href="{{ route('superadmin.receptionists') }}"
                        :current="request()->routeIs('superadmin.receptionists')"
                        x-show="!search || 'receptionists'.includes(search.toLowerCase())">
                        {{ __('app.receptionists') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ===== ANALYTICS ===== --}}
                <flux:sidebar.group expandable heading="{{ __('app.analytics') }}"
                    x-show="!search || ['analytics','room bookings','vehicle bookings','deliveries','guestbook'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item icon="chart-bar" href="{{ route('superadmin.room') }}"
                        :current="request()->routeIs('superadmin.room')"
                        x-show="!search || 'room bookings'.includes(search.toLowerCase())">
                        {{ __('app.room_bookings') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="truck" href="{{ route('superadmin.vehicle') }}"
                        :current="request()->routeIs('superadmin.vehicle')"
                        x-show="!search || 'vehicle bookings'.includes(search.toLowerCase())">
                        {{ __('app.vehicle_bookings') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="cube" href="{{ route('superadmin.delivery') }}"
                        :current="request()->routeIs('superadmin.delivery')"
                        x-show="!search || 'deliveries'.includes(search.toLowerCase())">
                        {{ __('app.deliveries') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="book-open" href="{{ route('superadmin.guestbook') }}"
                        :current="request()->routeIs('superadmin.guestbook')"
                        x-show="!search || 'guestbook'.includes(search.toLowerCase())">
                        {{ __('app.guestbook') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ===== AI & SECURITY SYSTEM ===== --}}
                <flux:sidebar.group expandable heading="{!! __('app.ai_security') !!}"
                    x-show="!search || ['ai','security','visitor predictions','occupancy forecast','security reports'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item icon="cpu-chip" href="{{ route('superadmin.lstm-predictions') }}"
                        :current="request()->routeIs('superadmin.lstm-predictions')"
                        x-show="!search || 'visitor predictions'.includes(search.toLowerCase())">
                        {{ __('app.visitor_predictions') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar-square" href="{{ route('superadmin.occupancy') }}"
                        :current="request()->routeIs('superadmin.occupancy')"
                        x-show="!search || 'occupancy forecast'.includes(search.toLowerCase())">
                        {{ __('app.occupancy_forecast') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="shield-check" href="{{ route('superadmin.ai-security') }}"
                        :current="request()->routeIs('superadmin.ai-security')"
                        x-show="!search || 'security reports'.includes(search.toLowerCase())">
                        {{ __('app.security_reports') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </div>

        <flux:sidebar.spacer />

        {{-- SETTINGS + HELP --}}
        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('superadmin.settings') }}"
                :current="request()->routeIs('superadmin.settings')">
                {{ __('app.settings') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="information-circle" href="{{ route('superadmin.help') }}"
                :current="request()->routeIs('superadmin.help')">
                {{ __('app.help') }}
            </flux:sidebar.item>

            {{-- Logout for MOBILE --}}
            <flux:sidebar.item
                class="lg:hidden"
                icon="arrow-right-start-on-rectangle"
                as="button"
                type="submit"
                form="logout-form">
                {{ __('app.logout') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>

        {{-- PROFILE (expanded, desktop) --}}
        <div x-data="{ open: false }" class="relative max-lg:hidden px-2 pb-2">
            <button
                @click.stop="open = !open"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors group focus:outline-none"
            >
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white shrink-0">
                    {{ strtoupper(substr($fullName, 0, 1)) }}
                </div>
                <div class="flex-1 text-left min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $fullName }}</p>
                    <p class="text-xs text-white/40">Superadmin</p>
                </div>
                <svg class="w-4 h-4 text-white/40 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                @click.outside="open = false"
                class="absolute bottom-full left-2 right-2 mb-1 rounded-xl bg-[#2a1f1a] border border-white/10 shadow-2xl shadow-black/40 z-[9999] overflow-hidden"
                style="display: none;"
            >
                <div class="px-3 py-2.5 border-b border-white/10">
                    <p class="text-xs font-semibold text-white truncate">{{ $fullName }}</p>
                    <p class="text-[10px] text-white/40 mt-0.5">Superadmin</p>
                </div>
                <button
                    type="submit"
                    form="logout-form"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </div>
</flux:sidebar>

{{-- logout-form is defined in the parent layout --}}
