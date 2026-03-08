<flux:sidebar
    sticky
    collapsible="mobile"
    class="
        fixed inset-y-0 left-0 z-40
        bg-zinc-900 border-r border-zinc-800
        lg:w-64 w-full max-w-[19rem]
        overflow-y-auto overflow-x-hidden
        box-border
    "
>
    <flux:sidebar.header>
        <flux:sidebar.brand
            href="#"
            logo="{{ $brandLogo }}"
            name="{{ $brandName }}"
            class="text-white"
            style="{{ $invertStyle }}" />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <flux:sidebar.search placeholder="Search modules..." />

    <flux:sidebar.nav>
        {{-- ------- Home ------- --}}
        <flux:sidebar.item
            icon="home"
            href="{{ route('receptionist.dashboard') }}"
            :current="request()->routeIs('receptionist.dashboard')"
        >
            Home
        </flux:sidebar.item>

        {{-- ------- Room Management ------- --}}
        <flux:sidebar.group expandable heading="Room Management" class="grid">
            <flux:sidebar.item
                icon="calendar-days"
                href="{{ route('receptionist.schedule') }}"
                :current="request()->routeIs('receptionist.schedule')"
            >
                Booking Room
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="check-circle"
                href="{{ route('receptionist.bookings') }}"
                :current="request()->routeIs('receptionist.bookings')"
            >
                Booking Approval
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="clock"
                href="{{ route('receptionist.bookinghistory') }}"
                :current="request()->routeIs('receptionist.bookinghistory')"
            >
                Booking History
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- Vehicle Management ------- --}}
        <flux:sidebar.group expandable heading="Vehicle Management" class="grid">
            <flux:sidebar.item
                icon="truck"
                href="{{ route('receptionist.bookingvehicle') }}"
                :current="request()->routeIs('receptionist.bookingvehicle')"
            >
                Book Vehicle
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="truck"
                href="{{ route('receptionist.vehiclestatus') }}"
                :current="request()->routeIs('receptionist.vehiclestatus')"
            >
                Vehicle Status
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="clock"
                href="{{ route('receptionist.vehicleshistory') }}"
                :current="request()->routeIs('receptionist.vehicleshistory')"
            >
                Vehicle History
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- Guest Management ------- --}}
        <flux:sidebar.group expandable heading="Guest Management" class="grid">
            <flux:sidebar.item
                icon="inbox"
                href="{{ route('receptionist.guestbook') }}"
                :current="request()->routeIs('receptionist.guestbook*')"
            >
                GuestBook
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="clock"
                href="{{ route('receptionist.guestbookhistory') }}"
                :current="request()->routeIs('receptionist.guestbookhistory*')"
            >
                GuestBook History
            </flux:sidebar.item>
        </flux:sidebar.group>

        {{-- ------- DocPac Management ------- --}}
        <flux:sidebar.group expandable heading="DocPac Management" class="grid">
            <flux:sidebar.item
                icon="gift"
                href="{{ route('receptionist.docpackform') }}"
                :current="request()->routeIs('receptionist.docpackform')"
            >
                DocPac Form
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="clock"
                href="{{ route('receptionist.docpackstatus') }}"
                :current="request()->routeIs('receptionist.docpackstatus')"
            >
                DocPac Status
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="clock"
                href="{{ route('receptionist.docpackhistory') }}"
                :current="request()->routeIs('receptionist.docpackhistory')"
            >
                DocPac History
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer />

    {{-- SETTINGS + MOBILE LOGOUT (via global logout form) --}}
    <flux:sidebar.nav>
        <flux:sidebar.item icon="cog-6-tooth" href="#">
            Settings
        </flux:sidebar.item>

        <flux:sidebar.item icon="information-circle" href="#">
            Help
        </flux:sidebar.item>

        {{-- Logout for MOBILE uses shared form="logout-form" --}}
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

    {{-- DESKTOP DROPDOWN --}}
    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile avatar="" name="{{ $fullName ?? 'User' }}" />

        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.radio checked>{{ $fullName ?? 'User' }}</flux:menu.radio>

                <flux:sidebar.item
                    icon="user"
                    href="{{ route('user.home') }}"
                    class="cursor-pointer"
                >
                    User Page
                </flux:sidebar.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            {{-- Logout for DESKTOP uses same form --}}
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

{{-- Shared logout form (same pattern as superadmin) --}}
<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>

<style>
    .img-white {
        filter: brightness(0) invert(1);
    }
</style>
