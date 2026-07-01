<?php

$layoutPath = __DIR__ . '/resources/views/layouts/admin.blade.php';
$sidebarPath = __DIR__ . '/resources/views/livewire/components/partials/admin/sidebar.blade.php';

// 1. Update Layout
if (file_exists($layoutPath)) {
    $layout = file_get_contents($layoutPath);
    // Add sidebarEnter and sidebarLeave if they are missing
    if (strpos($layout, 'sidebarEnter()') === false) {
        $layout = str_replace(
            "},",
            "},
            sidebarEnter() {
                if (!this.sidebarLocked && !this.isMobile) {
                    this.sidebarCollapsed = false;
                }
            },
            sidebarLeave() {
                if (!this.sidebarLocked && !this.isMobile) {
                    this.sidebarCollapsed = true;
                }
            },",
            $layout
        );
    }

    // Update padding
    $layout = str_replace(
        ":style=\"isMobile ? 'padding-left: 0;' : (sidebarLocked ? 'padding-left: 344px;' : 'padding-left: 64px;')\"",
        ":style=\"isMobile ? 'padding-left: 0;' : (sidebarLocked ? 'padding-left: 280px;' : 'padding-left: 64px;')\"",
        $layout
    );
    file_put_contents($layoutPath, $layout);
}

// 2. Generate new Sidebar HTML
$newSidebar = <<<'HTML'
<div class="sidebar-root">
    {{-- Mobile Backdrop --}}
    <div x-show="mobileMenuOpen" x-transition.opacity class="sidebar-backdrop lg:hidden" @click="mobileMenuOpen = false" x-cloak></div>

    <aside class="sidebar-unified"
           :class="isMobile ? (mobileMenuOpen ? 'mobile-open' : 'mobile-closed') : (sidebarLocked ? 'desktop-locked' : (sidebarCollapsed ? 'desktop-collapsed' : 'desktop-hovered'))"
           @mouseenter="sidebarEnter()" @mouseleave="sidebarLeave()"
           x-cloak>
           
        <div class="sidebar-unified-inner">
            {{-- Header --}}
            <div class="sidebar-unified-header">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-unified-logo" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    <div class="logo-icon-wrapper">
                        <img src="{{ $brandLogo }}" alt="Logo" class="sidebar-logo-img" />
                    </div>
                    <span class="logo-text" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'opacity-100' : 'opacity-0 hidden'">{{ $brandName }}</span>
                </a>
                
                <div class="sidebar-unified-actions" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'opacity-100' : 'opacity-0 hidden'">
                    <button @click.stop="sidebarLocked = !sidebarLocked" class="sidebar-pin-btn max-lg:hidden" :class="sidebarLocked ? 'pinned' : ''" title="Toggle Pin">
                        <svg class="w-4 h-4 transition-transform duration-300" :class="sidebarLocked ? 'rotate-0' : '-rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 17v5"/>
                            <path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/>
                        </svg>
                    </button>
                    <button @click="mobileMenuOpen = false" class="sidebar-close-btn lg:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            {{-- Nav Items --}}
            <nav class="sidebar-unified-nav">
                @php $homeActive = request()->routeIs('admin.dashboard'); @endphp
                <a href="{{ route('admin.dashboard') }}" class="sidebar-unified-item {{ $homeActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($homeActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                    <span class="item-label">Home</span>
                    <div class="tooltip">Home</div>
                </a>

                @php $roomActive = request()->routeIs('admin.room.monitoring'); @endphp
                <a href="{{ route('admin.room.monitoring') }}" class="sidebar-unified-item {{ $roomActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($roomActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                    <span class="item-label">Booking room</span>
                    <div class="tooltip">Booking room</div>
                </a>

                @php $infoActive = request()->routeIs('admin.information'); @endphp
                <a href="{{ route('admin.information') }}" class="sidebar-unified-item {{ $infoActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($infoActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8z"/><path d="M22 6l-4 4v4l4 4V6z"/></svg></div>
                    <span class="item-label">Information</span>
                    <div class="tooltip">Information</div>
                </a>

                @php $ticketActive = request()->routeIs('admin.ticket'); @endphp
                <a href="{{ route('admin.ticket') }}" class="sidebar-unified-item {{ $ticketActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($ticketActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></div>
                    <span class="item-label">Ticket</span>
                    <div class="tooltip">Ticket</div>
                </a>

                @php $userActive = request()->routeIs('admin.usermanagement'); @endphp
                <a href="{{ route('admin.usermanagement') }}" class="sidebar-unified-item {{ $userActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($userActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                    <span class="item-label">User Management</span>
                    <div class="tooltip">User Management</div>
                </a>

                @if(auth()->user()->department && auth()->user()->department->department_name === 'IT')
                    @php $wifiActive = request()->routeIs('admin.wifimanagement'); @endphp
                    <a href="{{ route('admin.wifimanagement') }}" class="sidebar-unified-item {{ $wifiActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                        @if($wifiActive)<div class="active-pip"></div>@endif
                        <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20" stroke-width="3"/></svg></div>
                        <span class="item-label">WiFi Management</span>
                        <div class="tooltip">WiFi Management</div>
                    </a>
                @endif

                @php $reportActive = request()->routeIs('admin.agentreport'); @endphp
                <a href="{{ route('admin.agentreport') }}" class="sidebar-unified-item {{ $reportActive ? 'active' : '' }}" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    @if($reportActive)<div class="active-pip"></div>@endif
                    <div class="item-icon"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg></div>
                    <span class="item-label">Agent Report</span>
                    <div class="tooltip">Agent Report</div>
                </a>
            </nav>

            <div class="sidebar-unified-footer">
                <a href="#" class="sidebar-unified-item" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    <div class="item-icon">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <span class="item-label">Settings</span>
                    <div class="tooltip">Settings</div>
                </a>

                <a href="#" class="sidebar-unified-item" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                    <div class="item-icon">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <span class="item-label">Help</span>
                    <div class="tooltip">Help</div>
                </a>
                
                <div class="sidebar-unified-user border-t border-white/10 mt-1 pt-2">
                    <div x-data="{ open: false }" class="relative w-full">
                        <button @click.stop="open = !open" class="sidebar-unified-user-card" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''">
                            <div class="user-avatar">
                                {{ strtoupper(substr($fullName ?? 'U', 0, 1)) }}
                            </div>
                            <div class="user-info">
                                <p class="user-name">{{ $fullName ?? 'User' }}</p>
                                <p class="user-role">Admin</p>
                            </div>
                            <svg class="user-chevron w-4 h-4 text-white/40 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                            <div class="tooltip">{{ $fullName ?? 'User' }}</div>
                        </button>
                        
                        <div x-show="open" @click.outside="open = false" class="sidebar-profile-popover" :class="(!sidebarCollapsed || sidebarLocked || isMobile) ? 'expanded' : ''" style="display:none;" x-cloak>
                            <button type="submit" form="logout-form" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                {{ __('app.logout') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>
HTML;
file_put_contents($sidebarPath, $newSidebar);

echo "Done\n";
