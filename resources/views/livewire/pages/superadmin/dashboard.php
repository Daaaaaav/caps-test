<div wire:poll.10s="loadStats">

<h1 class="text-2xl font-bold mb-6">
Superadmin Dashboard
</h1>

<div class="grid grid-cols-4 gap-4">

<div class="bg-white p-4 rounded shadow">
Visitors
<h2>{{ $visitors }}</h2>
</div>

<div class="bg-white p-4 rounded shadow">
Room Bookings
<h2>{{ $roomBookings }}</h2>
</div>

<div class="bg-white p-4 rounded shadow">
Vehicle Bookings
<h2>{{ $vehicleBookings }}</h2>
</div>

<div class="bg-white p-4 rounded shadow">
Packages
<h2>{{ $packages }}</h2>
</div>

</div>

</div>