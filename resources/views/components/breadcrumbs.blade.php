@php
$routeName = request()->route() ? request()->route()->getName() : '';
$path = request()->path();

// Predefined routes mapping
$breadcrumbs = [];

// Home / Dashboard base
if (str_starts_with($routeName, 'superadmin.')) {
    $breadcrumbs[] = ['label' => 'Dashboard', 'url' => route('superadmin.dashboard')];
} elseif (str_starts_with($routeName, 'receptionist.')) {
    $breadcrumbs[] = ['label' => 'Home', 'url' => route('receptionist.dashboard')];
} else {
    $breadcrumbs[] = ['label' => 'Home', 'url' => '/home'];
}

// Map sub-pages
$routeMappings = [
    // Receptionist Page Mappings
    'receptionist.schedule' => [
        ['label' => 'Room Management'],
        ['label' => 'Booking Room', 'url' => route('receptionist.schedule')]
    ],
    'receptionist.bookings' => [
        ['label' => 'Room Management'],
        ['label' => 'Booking Approval', 'url' => route('receptionist.bookings')]
    ],
    'receptionist.bookinghistory' => [
        ['label' => 'Room Management'],
        ['label' => 'Booking History', 'url' => route('receptionist.bookinghistory')]
    ],
    'receptionist.bookingvehicle' => [
        ['label' => 'Vehicle Management'],
        ['label' => 'Book Vehicle', 'url' => route('receptionist.bookingvehicle')]
    ],
    'receptionist.vehiclestatus' => [
        ['label' => 'Vehicle Management'],
        ['label' => 'Vehicle Status', 'url' => route('receptionist.vehiclestatus')]
    ],
    'receptionist.vehicleshistory' => [
        ['label' => 'Vehicle Management'],
        ['label' => 'Vehicle History', 'url' => route('receptionist.vehicleshistory')]
    ],
    'receptionist.guestbook' => [
        ['label' => 'Guest Management'],
        ['label' => 'GuestBook', 'url' => route('receptionist.guestbook')]
    ],
    'receptionist.guestbookhistory' => [
        ['label' => 'Guest Management'],
        ['label' => 'GuestBook History', 'url' => route('receptionist.guestbookhistory')]
    ],
    'receptionist.docpackform' => [
        ['label' => 'DocPac Management'],
        ['label' => 'DocPac Form', 'url' => route('receptionist.docpackform')]
    ],
    'receptionist.docpackstatus' => [
        ['label' => 'DocPac Management'],
        ['label' => 'DocPac Status', 'url' => route('receptionist.docpackstatus')]
    ],
    'receptionist.docpackhistory' => [
        ['label' => 'DocPac Management'],
        ['label' => 'DocPac History', 'url' => route('receptionist.docpackhistory')]
    ],
    'receptionist.settings' => [
        ['label' => 'Settings', 'url' => route('receptionist.settings')]
    ],
    'receptionist.help' => [
        ['label' => 'Help', 'url' => route('receptionist.help')]
    ],

    // Superadmin Page Mappings
    'superadmin.receptionists' => [
        ['label' => 'User Management'],
        ['label' => 'Receptionists', 'url' => route('superadmin.receptionists')]
    ],
    'superadmin.room' => [
        ['label' => 'Analytics'],
        ['label' => 'Room Bookings', 'url' => route('superadmin.room')]
    ],
    'superadmin.vehicle' => [
        ['label' => 'Analytics'],
        ['label' => 'Vehicle Bookings', 'url' => route('superadmin.vehicle')]
    ],
    'superadmin.delivery' => [
        ['label' => 'Analytics'],
        ['label' => 'Deliveries', 'url' => route('superadmin.delivery')]
    ],
    'superadmin.guestbook' => [
        ['label' => 'Analytics'],
        ['label' => 'Guestbook', 'url' => route('superadmin.guestbook')]
    ],
    'superadmin.lstm-predictions' => [
        ['label' => 'AI & Security System'],
        ['label' => 'Visitor Predictions', 'url' => route('superadmin.lstm-predictions')]
    ],
    'superadmin.occupancy' => [
        ['label' => 'AI & Security System'],
        ['label' => 'Occupancy Forecast', 'url' => route('superadmin.occupancy')]
    ],
    'superadmin.ai-security' => [
        ['label' => 'AI & Security System'],
        ['label' => 'Security Reports', 'url' => route('superadmin.ai-security')]
    ],
    'superadmin.settings' => [
        ['label' => 'Settings', 'url' => route('superadmin.settings')]
    ],
    'superadmin.help' => [
        ['label' => 'Help', 'url' => route('superadmin.help')]
    ],
];

if (isset($routeMappings[$routeName])) {
    $breadcrumbs = array_merge($breadcrumbs, $routeMappings[$routeName]);
} else {
    // Fallback: parse URL path segments
    $segments = request()->segments();
    foreach ($segments as $index => $segment) {
        if ($index === 0 && ($segment === 'superadmin-dashboard' || $segment === 'receptionist-dashboard')) {
            continue;
        }
        $label = ucwords(str_replace(['-', '_'], ' ', $segment));
        $breadcrumbs[] = [
            'label' => $label,
            'url' => null
        ];
    }
}
@endphp

<nav class="flex text-sm font-medium font-sans select-none" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        @foreach($breadcrumbs as $index => $item)
            <li class="inline-flex items-center">
                @if($index > 0)
                    <svg class="w-3.5 h-3.5 text-muted-foreground/60 mx-1 md:mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                @endif
                
                @if($loop->last)
                    <span class="text-foreground font-semibold tracking-tight">{{ $item['label'] }}</span>
                @elseif(isset($item['url']))
                    <a href="{{ $item['url'] }}" class="text-muted-foreground hover:text-primary transition-colors duration-200">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-muted-foreground/80">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
