<flux:sidebar sticky collapsible="mobile" 
    class="fixed inset-y-0 left-0 z-40 bg-zinc-900 border-r border-zinc-800 lg:w-64 w-full max-w-[19rem] overflow-y-auto overflow-x-hidden">
    
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
    <flux:sidebar.nav>
        {{-- ===== CORE ===== --}}
        <flux:sidebar.item icon="home" href="{{ route('superadmin.dashboard') }}"
            :current="request()->routeIs('superadmin.dashboard')">
            Dashboard
        </flux:sidebar.item>

        {{-- ===== USER MANAGEMENT ===== --}}
        <flux:sidebar.group expandable heading="User Management">
            <flux:sidebar.item icon="users" href="{{ route('superadmin.receptionists') }}"
                :current="request()->routeIs('superadmin.receptionists')">
                Receptionists
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ===== ANALYTICS ===== --}}
        <flux:sidebar.group expandable heading="Analytics">
            <flux:sidebar.item icon="chart-bar" href="{{ route('superadmin.room') }}"
                :current="request()->routeIs('superadmin.room')">
                Room Bookings
            </flux:sidebar.item>

            <flux:sidebar.item icon="truck" href="{{ route('superadmin.vehicle') }}"
                :current="request()->routeIs('superadmin.vehicle')">
                Vehicle Bookings
            </flux:sidebar.item>

            <flux:sidebar.item icon="cube" href="{{ route('superadmin.delivery') }}"
                :current="request()->routeIs('superadmin.delivery')">
                Deliveries
            </flux:sidebar.item>

            <flux:sidebar.item icon="book-open" href="{{ route('superadmin.guestbook') }}"
                :current="request()->routeIs('superadmin.guestbook')">
                Guestbook
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ===== AI SYSTEM ===== --}}
        <flux:sidebar.group expandable heading="AI System">
            <flux:sidebar.item icon="shield-check" href="{{ route('superadmin.ai-security') }}"
                :current="request()->routeIs('superadmin.ai-security')">
                Security Reports
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    {{-- LOGOUT --}}
    <flux:sidebar.nav>
        <flux:sidebar.item icon="arrow-right-start-on-rectangle" as="button" type="submit"
            form="logout-form">
            Logout
        </flux:sidebar.item>
    </flux:sidebar.nav>

    {{-- PROFILE --}}
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile avatar="" name="{{ $fullName }}" />
        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <flux:menu.item icon="arrow-right-start-on-rectangle" as="button" type="submit" form="logout-form">
                Logout
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>
