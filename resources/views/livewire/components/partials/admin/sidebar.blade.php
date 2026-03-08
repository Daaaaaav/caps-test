<flux:sidebar
    sticky
    collapsible="mobile"
    class="
        fixed top-0 left-0 h-screen
        bg-zinc-900
        border-r border-zinc-200 dark:border-zinc-700
        lg:w-64 w-[85vw] max-w-sm
        z-40
    "
>
    {{-- Logo + Brand + Collapse (mobile) --}}
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="#"
            logo="{{ $brandLogo }}"
            name="{{ $brandName }}"
            class="text-white"
            style="{{ $invertStyle }}" />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="Search..." />

    <flux:sidebar.nav>
        <flux:sidebar.item
            icon="home"
            href="{{ route('admin.dashboard') }}"
            :current="request()->routeIs('admin.dashboard')"
        >
            Home
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="calendar-days"
            href="{{ route('admin.room.monitoring') }}"
            :current="request()->routeIs('admin.room.monitoring')"
        >
            Booking room
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="calendar-days"
            href="{{ route('admin.information') }}"
            :current="request()->routeIs('admin.information')" 
        >
            Information
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="inbox"
            href="{{ route('admin.ticket') }}"
            :current="request()->routeIs('admin.ticket')"
        >
            Ticket
        </flux:sidebar.item>

        <flux:sidebar.item
            icon="users"
            href="{{ route('admin.usermanagement') }}"
            :current="request()->routeIs('admin.usermanagement')"
        >
            User Management
        </flux:sidebar.item>

        {{-- MENU KHUSUS IT: WiFi Management --}}
        {{-- Hanya tampil jika user memiliki departemen 'IT' --}}
        @if(auth()->user()->department && auth()->user()->department->department_name === 'IT')
            <flux:sidebar.item
                icon="wifi" 
                href="{{ route('admin.wifimanagement') }}"
                :current="request()->routeIs('admin.wifimanagement')"
            >
                WiFi Management
            </flux:sidebar.item>
        @endif

        <flux:sidebar.item
            icon="chart-bar"
            href="{{ route('admin.agentreport') }}"
            :current="request()->routeIs('admin.agentreport')"
        >
            Agent Report
        </flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
        <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>

        <flux:sidebar.item
            class="lg:hidden"
            icon="arrow-right-start-on-rectangle"
            as="button"
            type="submit"
            form="logout-form"
        >
            Logout
        </flux:sidebar.item>
    </flux:sidebar.nav>

    {{-- Profil + menu (desktop) --}}
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile avatar="" name="{{ $fullName }}" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                <flux:sidebar.item
                    icon="user"
                    href="{{ route('user.home') }}"
                    class="cursor-pointer"
                >
                    User Page
                </flux:sidebar.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.item
                icon="arrow-right-start-on-rectangle"
                as="button"
                type="submit"
                form="logout-form"
            >
                Logout
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>

<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>