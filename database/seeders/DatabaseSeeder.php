<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\{
    Company,
    Department,
    Role,
    User,
    Room,
    Requirement,
    Storage,
    Vehicle,
    VehicleBooking,
    VehicleBookingPhoto,
    Delivery,
    Announcement,
    Information,
    Guestbook,
    BookingRoom,
    Ticket,
    TicketAssignment,
    TicketAttachment,
    TicketComment,
    // TicketHistory
};

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            $companies = [
                ['Kebun Raya Bogor', 'krbogor.id', 'https://tiketkebunraya.id/assets/images/kebun-raya-bogor.png'],
                ['Kebun Raya Bali', 'krbali.id', 'https://tiketkebunraya.id/assets/images/kebun-raya-bali.png'],
                ['Kebun Raya Cibodas', 'krcibodas.id', 'https://tiketkebunraya.id/assets/images/kebun-raya-cibodas.png'],
                ['Kebun Raya Purwodadi', 'krpurwodadi.id', 'https://tiketkebunraya.id/assets/images/kebun-raya-purwodadi.png'],
            ];

            // Default Company
            Company::firstOrCreate(
                ['company_id' => 1],
                ['company_name' => 'Default Company']
            );

            foreach ($companies as [$companyName, $domain, $imageUrl]) {
                echo "\n🌿 Seeding {$companyName}...\n";

                // === COMPANY CREATION ===
                $company = Company::firstOrCreate(
                    ['company_name' => $companyName],
                    [
                        'company_address' => 'Jl. Raya ' . $companyName,
                        'company_email' => "info@{$domain}",
                        'image' => $imageUrl,
                    ]
                );

                $companyId = $company->company_id;

                // === ROLES ===
                $roles = [];
                foreach (['Superadmin', 'Admin', 'User', 'Receptionist'] as $r) {
                    $roles[$r] = Role::firstOrCreate(['name' => $r]);
                }

                // === DEPARTMENTS ===
                $deptNames = [
                    'IT','Finance','HRD','Marketing','Operations',
                    'General Affairs','Executive',
                    'Customer Support','Legal','Maintenance','Administration'
                ];
                $depts = [];
                foreach ($deptNames as $d) {
                    $depts[$d] = Department::firstOrCreate([
                        'company_id' => $companyId,
                        'department_name' => $d,
                    ]);
                }

                // === CORE USERS ===
                $superadmin = User::firstOrCreate(
                    ['email' => "superadmin@{$domain}"],
                    [
                        'company_id' => $companyId,
                        'department_id' => $depts['Executive']->department_id,
                        'role_id' => $roles['Superadmin']->role_id,
                        'full_name' => "Superadmin {$companyName}",
                        'phone_number' => '08000000000',
                        'password' => Hash::make('superpassword'),
                        'is_agent' => 'no',
                    ]
                );

                $receptionist = User::firstOrCreate(
                    ['email' => "receptionist@{$domain}"],
                    [
                        'company_id' => $companyId,
                        'department_id' => $depts['Administration']->department_id,
                        'role_id' => $roles['Receptionist']->role_id,
                        'full_name' => "Receptionist {$companyName}",
                        'phone_number' => '087812345678',
                        'password' => Hash::make('receppassword'),
                        'is_agent' => 'no',
                    ]
                );

                // === ADMINS ===
                $admins = collect();
                foreach ($depts as $name => $dept) {
                    $slug = Str::slug($name);
                    $admin = User::firstOrCreate(
                        ['email' => "admin-{$slug}@{$domain}"],
                        [
                            'company_id' => $companyId,
                            'department_id' => $dept->department_id,
                            'role_id' => $roles['Admin']->role_id,
                            'full_name' => "Admin {$name} ({$companyName})",
                            'phone_number' => '081' . random_int(100000000, 999999999),
                            'password' => Hash::make('password'),
                            'is_agent' => 'no',
                        ]
                    );
                    $admins->push($admin);
                }

                // === GENERAL USERS & AGENTS ===
                $users = collect([$superadmin, $receptionist]);
                $agents = collect();

                $firstNames = ['Agus','Bambang','Cici','Dedi','Endang','Fajar','Gita','Hadi','Indah','Joko','Kartika','Lina','Mega','Nina','Oscar','Putra','Qori','Rian','Sari','Tono','Umar','Vina','Wati','Yoga','Zul'];
                $lastNames = ['Susanto','Wijaya','Permata','Nugroho','Pratama','Wibowo','Hidayat','Kusuma','Lestari','Setiawan','Saputra','Santoso'];

                $globalCounter = 1;

                foreach ($depts as $deptName => $deptObj) {
                    // 10 Agents per Department
                    for ($k = 1; $k <= 10; $k++) {
                        $name = Arr::random($firstNames) . ' ' . Arr::random($lastNames);
                        $slug = Str::slug($name) . "-agent-" . $globalCounter;

                        $newAgent = User::create([
                            'email' => "{$slug}@{$domain}",
                            'company_id' => $companyId,
                            'department_id' => $deptObj->department_id,
                            'role_id' => $roles['User']->role_id,
                            'full_name' => $name,
                            'phone_number' => '089' . random_int(100000000, 999999999),
                            'password' => Hash::make('password'),
                            'is_agent' => 'yes',
                        ]);

                        $users->push($newAgent);
                        $agents->push($newAgent);
                        $globalCounter++;
                    }

                    // 5 Normal Users per Department
                    for ($j = 1; $j <= 5; $j++) {
                        $name = Arr::random($firstNames) . ' ' . Arr::random($lastNames);
                        $slug = Str::slug($name) . "-user-" . $globalCounter;

                        $newUser = User::create([
                            'email' => "{$slug}@{$domain}",
                            'company_id' => $companyId,
                            'department_id' => $deptObj->department_id,
                            'role_id' => $roles['User']->role_id,
                            'full_name' => $name,
                            'phone_number' => '085' . random_int(100000000, 999999999),
                            'password' => Hash::make('password'),
                            'is_agent' => 'no',
                        ]);

                        $users->push($newUser);
                        $globalCounter++;
                    }
                }

                // === PIVOT user_departments ===
                $allDeptIds = collect($depts)->pluck('department_id');
                foreach ($admins as $user) {
                    if (rand(1, 100) <= 50) continue;

                    $primaryDeptId = $user->department_id;
                    $secondaryDeptIds = $allDeptIds
                        ->reject(fn ($id) => $id === $primaryDeptId)
                        ->shuffle()
                        ->take(rand(1, 2));

                    foreach ($secondaryDeptIds as $deptId) {
                        DB::table('user_departments')->insertOrIgnore([
                            'user_id'       => $user->user_id,
                            'department_id' => $deptId,
                        ]);
                    }
                }

                $this->seedAssetsAndActivities($companyId, $companyName, $depts, $roles, $admins, $users, $agents, $receptionist, $now);
            }
        });
    }

    protected function seedAssetsAndActivities($companyId, $companyName, $depts, $roles, $admins, $users, $agents, $receptionist, $now)
    {
        mt_srand($companyId * 999);
        $daysBack = 1825; // 5 Years

        $demoImages = [
            'https://res.cloudinary.com/demo/image/upload/sample.jpg',
            'https://res.cloudinary.com/demo/image/upload/dog.jpg',
            'https://res.cloudinary.com/demo/image/upload/cat.jpg',
            'https://res.cloudinary.com/demo/image/upload/girl.jpg',
            'https://res.cloudinary.com/demo/image/upload/car.jpg',
        ];

        // ===== ROOMS & REQUIREMENTS =====
        $rooms = collect(['Garuda','Merak','Cendrawasih','Aula','Elang'])
            ->map(fn($r) => Room::firstOrCreate(['company_id'=>$companyId,'room_name'=>"Ruang {$r}"]));

        $requirementsList = collect();
        foreach (['Projector & Screen','Whiteboard','Coffee Break','Lunch Set','Sound System'] as $req) {
            $requirementsList->push(Requirement::firstOrCreate(['company_id'=>$companyId,'name'=>$req]));
        }

        // ===== STORAGES & VEHICLES =====
        foreach ([['S-01','Rak Dokumen'],['S-02','Loker Paket'],['S-03','Gudang ATK']] as [$code,$name]) {
            Storage::firstOrCreate(['company_id'=>$companyId,'code'=>$code],['name'=>$name]);
        }

        $vehicles = collect();
        foreach ([
            ['Avanza','car',2022],['Innova','car',2021],['Honda Vario','motorcycle',2023],['Carry PickUp','pickup',2019]
        ] as [$name,$type,$year]) {
            $plate = 'B ' . rand(1000,9999) . ' ' . Str::upper(Str::random(3));
            $vehicles->push(Vehicle::firstOrCreate(
                ['plate_number'=>$plate],
                ['company_id'=>$companyId,'name'=>$name,'category'=>$type,'year'=>$year]
            ));
        }

        // ===== DELIVERIES =====
        for ($i=1; $i<=50; $i++) {
            Delivery::create([
                'company_id'=>$companyId,
                'receptionist_id'=>$receptionist->user_id,
                'item_name'=>"Paket {$companyName} #{$i}",
                'type'=>Arr::random(['package','document','invoice','etc']),
                'nama_pengirim'=>Arr::random(['JNE','TIKI','SiCepat','Pos Indonesia']),
                'nama_penerima'=>$users->random()->full_name,
                'status'=>Arr::random(['pending','stored','taken','delivered']),
                'direction' => Arr::random(['taken', 'deliver']),
                'pengiriman'=>$now->copy()->subDays(rand(0, $daysBack)),
            ]);
        }

        // ===== ANNOUNCEMENTS/GUESTBOOK =====
        for ($i=1; $i<=30; $i++) {
            $randomDate = $now->copy()->subDays(rand(0, $daysBack));

            Announcement::create([
                'company_id'=>$companyId,
                'description'=>"📢 Pengumuman {$companyName} #{$i}",
                'event_at'=>$randomDate->copy()->addDays(rand(2,10)),
                'created_at'=>$randomDate,
            ]);

            Information::create([
                'company_id'=>$companyId,
                'department_id'=>Arr::random($depts)->department_id,
                'description'=>"📰 Info khusus {$companyName} #{$i}",
                'event_at'=>$randomDate->copy()->addDays(rand(1,5)),
                'created_at'=>$randomDate,
            ]);

            Guestbook::create([
                'company_id'=>$companyId,
                'department_id'=>Arr::random($depts)->department_id,
                'date'=>$randomDate->toDateString(),
                'jam_in'=>sprintf("%02d:%02d:00", rand(8,10), rand(0,59)),
                'jam_out'=>sprintf("%02d:%02d:00", rand(14,17), rand(0,59)),
                'name'=>"Tamu #{$i}",
                'instansi'=>"Instansi {$i}",
                'keperluan'=>"Meeting",
                'petugas_penjaga'=>$receptionist->full_name,
                'created_at'=>$randomDate,
            ]);
        }

        // ===== ROOM BOOKINGS (Offline & Online) =====
        foreach (range(1, 80) as $i) {
            $booker = $users->random();
            $room = $rooms->random();
            $startDate = $now->copy()->subDays(rand(0, $daysBack));
            $endDate = $startDate->copy()->addHours(rand(1,3));

            // DECIDE TYPE: Meeting or Online Meeting
            $bookingType = Arr::random(['meeting', 'online_meeting']);
            $onlineProvider = null;
            $onlineUrl = null;
            $onlineCode = null;
            $onlinePass = null;

            if ($bookingType === 'online_meeting') {
                $onlineProvider = Arr::random(['zoom', 'google_meet']);
                $onlineUrl = 'https://' . $onlineProvider . '.us/j/' . Str::random(10);
                $onlineCode = Str::random(10);
                $onlinePass = Str::random(8);
            }

            $bookingRoom = BookingRoom::create([
                'room_id'=>$room->room_id,
                'company_id'=>$companyId,
                'user_id'=>$booker->user_id,
                'department_id'=>$booker->department_id,
                'meeting_title'=>"Rapat {$companyName} #{$i}",
                'date'=>$startDate->toDateString(),
                'number_of_attendees'=>rand(3,30),
                'start_time'=>$startDate,
                'end_time'=>$endDate,
                'is_approve'=>1,

                // TYPE AND ONLINE DETAILS
                'booking_type' => $bookingType,
                'online_provider' => $onlineProvider,
                'online_meeting_url' => $onlineUrl,
                'online_meeting_code' => $onlineCode,
                'online_meeting_password' => $onlinePass,

                'status'=>'approved',
                'approved_by' => $receptionist->user_id,

                // requestinformation applies to both types
                'requestinformation' => Arr::random(['request', null]),

                'created_at' => $startDate,
                'updated_at' => $startDate,
            ]);

            $randomRequirements = $requirementsList->random(rand(0, 3));
            foreach ($randomRequirements as $req) {
                DB::table('booking_requirements')->insert([
                    'bookingroom_id' => $bookingRoom->bookingroom_id,
                    'requirement_id' => $req->requirement_id,
                    'created_at' => $startDate,
                    'updated_at' => $startDate,
                ]);
            }
        }

        // ===== VEHICLE BOOKINGS =====
        if ($vehicles->isNotEmpty()) {
            foreach (range(1, 80) as $i) {
                $user = $users->random();
                $vehicle = $vehicles->random();
                $start = $now->copy()->subDays(rand(0, $daysBack))->hour(rand(8,14));
                $end = $start->copy()->addHours(rand(2,6));

                $purposeType = Arr::random(['dinas', 'operasional', 'antar_jemput', 'lainnya']);
                $status = Arr::random(['pending', 'approved', 'on_progress', 'returned', 'completed', 'rejected', 'cancelled']);

                $booking = VehicleBooking::create([
                    'vehicle_id' => $vehicle->vehicle_id,
                    'company_id' => $companyId,
                    'department_id' => $user->department_id,
                    'user_id' => $user->user_id,
                    'borrower_name' => $user->full_name,
                    'start_at' => $start,
                    'end_at' => $end,
                    'purpose' => "Keperluan " . ucfirst($purposeType) . " #{$i}",
                    'purpose_type' => $purposeType,
                    'destination' => Arr::random(['Bogor','Jakarta','Bali','Purwodadi']),
                    'odd_even_area' => Arr::random(['tidak', 'ganjil', 'genap']),
                    'status' => $status,
                    'terms_agreed' => 1,
                    'created_at' => $start,
                    'updated_at' => $start,
                ]);

                if (in_array($status, ['on_progress', 'returned', 'completed'])) {
                    VehicleBookingPhoto::create([
                        'vehiclebooking_id' => $booking->vehiclebooking_id,
                        'user_id' => $user->user_id,
                        'photo_type' => 'before',
                        'photo_path' => 'vehicle_photos/demo_sample_before_' . $i . '.jpg',
                        'created_at' => $start,
                    ]);
                }
                if ($status == 'completed') {
                    VehicleBookingPhoto::create([
                        'vehiclebooking_id' => $booking->vehiclebooking_id,
                        'user_id' => $user->user_id,
                        'photo_type' => 'after',
                        'photo_path' => 'vehicle_photos/demo_sample_after_' . $i . '.jpg',
                        'created_at' => $end,
                    ]);
                }
            }
        }

        // ===== TICKETING =====
        $this->seedTicketSystem($companyId, $companyName, $depts, $users, $agents, $now, $demoImages, $daysBack);
    }

    protected function seedTicketSystem($companyId, $companyName, $depts, $users, $agents, $now, $demoImages, $daysBack)
    {
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];

        $slaLimits = ['low' => 72, 'medium' => 48, 'high' => 24];

        if ($agents->isEmpty()) {
            echo "⚠️ No agents found. Skipping ticket seeding.\n";
            return;
        }

        $counter = 1;

        foreach ($agents as $agent) {
            for ($k = 1; $k <= 10; $k++) {
                $creator = $users->random();
                $requestDept = Arr::random($depts);
                $priority = Arr::random($priorities);
                $status = Arr::random($statuses);

                $created = $now->copy()->subDays(rand(0, $daysBack));

                $limitHours = $slaLimits[$priority];
                $violatesSla = (rand(1, 100) <= 30);

                if ($violatesSla) {
                    $hoursTaken = rand($limitHours + 1, $limitHours + 120);
                } else {
                    $hoursTaken = rand(1, $limitHours - 1);
                }

                if (in_array($status, ['RESOLVED', 'CLOSED'])) {
                    $updated = $created->copy()->addHours($hoursTaken);
                } else {
                    $updated = $created->copy()->addHours(rand(1, 24));
                }

                if ($updated->gt($now)) {
                    $updated = $now;
                }

                $ticket = Ticket::create([
                    'company_id' => $companyId,
                    'department_id' => $agent->department_id,
                    'requestdept_id' => $requestDept->department_id,
                    'user_id' => $creator->user_id,
                    'subject' => "Ticket #{$counter} - Priority {$priority}",
                    'description' => "Issue detected. SLA Target: {$limitHours}h. Created: {$created->toDateString()}",
                    'priority' => $priority,
                    'status' => $status,
                    'created_at' => $created,
                    'updated_at' => $updated,
                ]);

                TicketAssignment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'user_id' => $agent->user_id,
                    'created_at' => $created,
                ]);

                $numAttachments = rand(1, 2);
                for ($att = 1; $att <= $numAttachments; $att++) {
                    TicketAttachment::create([
                        'ticket_id' => $ticket->ticket_id,
                        'file_url' => Arr::random($demoImages),
                        'file_type' => 'image/jpeg',
                        'uploaded_by' => $creator->user_id,
                        'cloudinary_public_id' => 'demo_ticket_' . $counter . '_' . $att,
                        'bytes' => random_int(15000, 60000),
                        'original_filename' => "evidence_{$counter}_{$att}.jpg",
                        'created_at' => $created,
                    ]);
                }

                TicketComment::create([
                    'ticket_id' => $ticket->ticket_id,
                    'user_id' => $creator->user_id,
                    'comment_text' => "Follow up for ticket #{$counter}",
                    'created_at' => $created->copy()->addHours(1),
                ]);

                // TicketHistory::create([
                //     'ticket_id' => $ticket->ticket_id,
                //     'status' => $status,
                //     'changed_by' => $creator->user_id,
                //     'created_at' => $updated,
                // ]);

                $counter++;
            }
        }

        echo "  ✅ Created " . ($counter - 1) . " tickets with realistic SLA data.\n";
    }
}
