<flux:sidebar sticky collapsible="mobile" class="
        fixed inset-y-0 left-0 z-40
        bg-zinc-900 border-r border-zinc-800
        lg:w-64 w-full max-w-[19rem]
        overflow-y-auto overflow-x-hidden
        box-border
    ">
    <flux:sidebar.header>
        <flux:sidebar.brand href="#" logo="{{ $brandLogo }}" name="{{ $brandName }}" class="text-white"
            style="{{ $invertStyle }}" />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="Search modules..." />

    <flux:sidebar.nav>
        {{-- ------- Core ------- --}}
        <flux:sidebar.item icon="home" href="{{ route('superadmin.dashboard') }}"
            :current="request()->routeIs('superadmin.dashboard')">
            Dashboard
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office" href="{{ route('superadmin.department') }}"
            :current="request()->routeIs('superadmin.department')">
            Departments
        </flux:sidebar.item>

        <flux:sidebar.item icon="users" href="{{ route('superadmin.user') }}"
            :current="request()->routeIs('superadmin.user')">
            Users
        </flux:sidebar.item>

        <flux:sidebar.item icon="shield-check" href="{{ route('superadmin.adminmanagement') }}"
            :current="request()->routeIs('superadmin.adminmanagement')">
            Admins
        </flux:sidebar.item>

        <flux:sidebar.item icon="chart-bar" href="{{ route('superadmin.reports') }}"
            :current="request()->routeIs('superadmin.reports')">
            Reports
        </flux:sidebar.item>
        <flux:sidebar.item icon="wifi" href="{{ route('superadmin.wifimanagement') }}"
            :current="request()->routeIs('superadmin.wifimanagement')">
            WiFi Management
        </flux:sidebar.item>

        {{-- ------- Communication ------- --}}
        <flux:sidebar.group expandable heading="Communication" class="grid">
            <flux:sidebar.item icon="megaphone" href="{{ route('superadmin.announcement') }}"
                :current="request()->routeIs('superadmin.announcement')">
                Announcements
            </flux:sidebar.item>
            <flux:sidebar.item icon="document-text" href="{{ route('superadmin.information') }}"
                :current="request()->routeIs('superadmin.information')">
                Info Center
            </flux:sidebar.item>
            <flux:sidebar.item icon="user" href="{{ route('superadmin.guestbookmanagement') }}"
                :current="request()->routeIs('superadmin.guestbookmanagement')">
                Guestbook
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- Vehicles ------- --}}
        <flux:sidebar.group expandable heading="Vehicles" class="grid">
            <flux:sidebar.item icon="truck" href="{{ route('superadmin.vehicle') }}"
                :current="request()->routeIs('superadmin.vehicle')">
                Fleet
            </flux:sidebar.item>
            <flux:sidebar.item icon="calendar" href="{{ route('superadmin.bookingvehicle') }}"
                :current="request()->routeIs('superadmin.bookingvehicle')">
                Bookings
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- Rooms ------- --}}
        <flux:sidebar.group expandable heading="Rooms" class="grid">
            <flux:sidebar.item icon="calendar" href="{{ route('superadmin.bookingroom') }}"
                :current="request()->routeIs('superadmin.bookingroom')">
                Bookings
            </flux:sidebar.item>
            <flux:sidebar.item icon="archive-box" href="{{ route('superadmin.storage') }}"
                :current="request()->routeIs('superadmin.storage')">
                Storage
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-office" href="{{ route('superadmin.manageroom') }}"
                :current="request()->routeIs('superadmin.manageroom')">
                Manage Rooms
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- Ticketing ------- --}}
        <flux:sidebar.group expandable heading="Ticketing" class="grid">
            <flux:sidebar.item icon="inbox" href="{{ route('superadmin.ticketsupport') }}"
                :current="request()->routeIs('superadmin.ticketsupport')">
                Ticket List
            </flux:sidebar.item>
            <flux:sidebar.item icon="check-badge" href="{{ route('superadmin.managerequirements') }}"
                :current="request()->routeIs('superadmin.managerequirements')">
                Requirements
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    <flux:sidebar.nav>
        <flux:sidebar.item class="lg:hidden" icon="arrow-right-start-on-rectangle" as="button" type="submit"
            form="logout-form">
            Logout
        </flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile avatar="" name="{{ $fullName }}" />
        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                <flux:sidebar.item icon="user" href="{{ route('user.home') }}" class="cursor-pointer">
                    User Page
                </flux:sidebar.item>
                <flux:sidebar.item icon="bell" href="{{ route('receptionist.dashboard') }}" class="cursor-pointer">
                    Recepionist Page
                </flux:sidebar.item>
                <flux:sidebar.item icon="shield-check" href="{{ route('admin.dashboard') }}" class="cursor-pointer">
                    Admin Page
                </flux:sidebar.item>
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

<style>
    /* keep images white for dark sidebar */
    .img-white {
        filter: brightness(0) invert(1);
    }
</style>