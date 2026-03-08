<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Import Models
use App\Models\Ticket;
use App\Models\BookingRoom;
use App\Models\Information;
use App\Models\TicketAssignment;
use App\Models\User;
use App\Models\Department; 

#[Layout('layouts.admin')]
#[Title('Admin - Dashboard')]
class Dashboard extends Component
{

    public string $company_name    = '-';
    public string $department_name = '-';
    // Mengatur Timezone secara eksplisit
    protected string $tz = 'Asia/Jakarta';

    /**
     * Cek apakah user memiliki role 'superadmin'.
     */
    private function isSuperAdmin(User $user): bool
    {
        // Asumsi kolom role adalah 'role_name' pada tabel user atau relasi
        // Jika menggunakan relasi, sesuaikan dengan logic otentikasi role yang benar
        // Contoh: return $user->hasRole('superadmin');
        return $user->role->name === 'Superadmin'; // Sesuaikan dengan properti/relasi role yang Anda gunakan
    }

    /**
     * Helper untuk mendapatkan semua ID Departemen (Primer + Sekunder) dari user.
     * Untuk Superadmin, fungsi ini harus mengembalikan array kosong agar tidak ada filter departemen yang diterapkan.
     */
    private function getAllDepartmentIds(User $user): array
    {
        // **PERUBAHAN UTAMA UNTUK SUPERADMIN**
        if ($this->isSuperAdmin($user)) {
            // Mengembalikan array kosong. Semua query akan melewatkan when(!empty($departmentIds), ...)
            return []; 
        }

        // Logic standar untuk non-Superadmin
        $primaryId = $user->department_id;
        
        $secondaryIds = DB::table('user_departments')
            ->where('user_id', $user->user_id)
            ->pluck('department_id')
            ->toArray();

        $departmentIds = array_merge([$primaryId], $secondaryIds);
        
        return array_map('intval', array_filter(array_unique($departmentIds)));
    }


    /**
     * Helper untuk mengubah nilai ke Carbon instance
     */
    private function asCarbon(null|Carbon|\DateTimeInterface|string $v): ?Carbon
    {
        if ($v === null) return null;
        if ($v instanceof Carbon) return $v->timezone($this->tz);
        if ($v instanceof \DateTimeInterface) return Carbon::instance($v)->timezone($this->tz);
        try {
            return Carbon::parse($v)->timezone($this->tz);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Mengambil data aktivitas harian untuk 7 hari terakhir
     */
    private function getWeeklyActivityData($companyId, array $departmentIds): array
    {
        $now = Carbon::now($this->tz);
        $startOfRange = $now->copy()->subDays(6)->startOfDay();
        $endOfRange = $now->copy()->endOfDay();

        $days = [];
        $dayLabels = [];

        // 1. Inisialisasi array untuk 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dayLabels[] = $date->format('D');
            $days[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'ticket' => 0,
                'room' => 0,
                'information' => 0,
            ];
        }

        // --- 2. Tickets Data (Filter by Company AND Department IDs) ---
        $ticketsData = Ticket::query()
            ->select(DB::raw('DATE(created_at) as date_key'), DB::raw('count(*) as count'))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            // JIKA $departmentIds KOSONG (SUPERADMIN), KLAUSA INI DILOMPATI
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($ticketsData as $data) {
            $dateKey = $data->date_key;
            if (isset($days[$dateKey])) {
                $days[$dateKey]['ticket'] = (int)$data->count;
            }
        }

        // --- 3. Room Bookings Data (Filter by Company AND Department IDs) ---
        $roomData = BookingRoom::query()
            ->select(DB::raw('DATE(created_at) as date_key'), DB::raw('count(*) as count'))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            // JIKA $departmentIds KOSONG (SUPERADMIN), KLAUSA INI DILOMPATI
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($roomData as $data) {
            $dateKey = $data->date_key;
            if (isset($days[$dateKey])) {
                $days[$dateKey]['room'] = (int)$data->count;
            }
        }

        // --- 4. Information Data ---
        // Informasi tidak memiliki company_id atau department_id, jadi tidak perlu filter tambahan
        $infoData = Information::query()
            ->select(DB::raw('DATE(created_at) as date_key'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($infoData as $data) {
            $dateKey = $data->date_key;
            if (isset($days[$dateKey])) {
                $days[$dateKey]['information'] = (int)$data->count;
            }
        }

        return [
            'labels' => $dayLabels,
            'ticket' => array_column(array_values($days), 'ticket'),
            'room' => array_column(array_values($days), 'room'),
            'information' => array_column(array_values($days), 'information'),
        ];
    }


    public function render()
    {
        $user = Auth::user();
        $companyId = optional($user)->company_id;
        
        // Ambil semua ID departemen (Primer + Sekunder). Akan kosong jika Superadmin.
        $departmentIds = $this->getAllDepartmentIds($user);
        
        // Tentukan label departemen yang ditampilkan
        if ($this->isSuperAdmin($user)) {
            $departmentList = 'SEMUA DEPARTEMEN';
        } else {
            // Ambil nama-nama Departemen berdasarkan ID yang terkumpul
            $departments = Department::whereIn('department_id', $departmentIds)
                ->pluck('department_name') // Asumsi kolom nama departemen adalah 'department_name'
                ->toArray();
            
            // Ubah array nama menjadi string yang dipisahkan koma
            $departmentList = count($departments) > 0 
                ? implode(', ', $departments) 
                : 'Semua Departemen'; // Fallback jika departemen tidak ditemukan
        }
            
        // ... (Range waktu) ...
        $startOfRange = Carbon::now($this->tz)->subDays(6)->startOfDay();
        $endOfRange = Carbon::now($this->tz)->endOfDay();
        $startOfMonth = Carbon::now($this->tz)->startOfMonth();
        $endOfMonth = Carbon::now($this->tz)->endOfMonth();

        // --- Kueri Statistik (semua menggunakan whereIn $departmentIds) ---
        
        $weeklyTicketsCount = Ticket::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->count();

        $weeklyRoomBookingsCount = BookingRoom::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->count();

        $weeklyInformationCount = Information::query()
            // Information tidak difilter berdasarkan company/department karena bersifat umum
            ->whereBetween('created_at', [$startOfRange, $endOfRange])
            ->count();
        
        $totalTicketsThisMonth = Ticket::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $ticketPriorities = Ticket::query()
            ->select('priority', DB::raw('count(*) as count'))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('department_id', $departmentIds))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('priority')
            ->get()
            ->keyBy('priority');

        $highCount = $ticketPriorities['high']->count ?? 0;
        $mediumCount = $ticketPriorities['medium']->count ?? 0;
        $lowCount = $ticketPriorities['low']->count ?? 0;

        $highPercent = $totalTicketsThisMonth > 0 ? round(($highCount / $totalTicketsThisMonth) * 100) : 0;
        $mediumPercent = $totalTicketsThisMonth > 0 ? round(($mediumCount / $totalTicketsThisMonth) * 100) : 0;
        // Hitung Low sebagai sisa agar total menjadi 100%
        $lowPercent = 100 - $highPercent - $mediumPercent;
        if ($lowPercent < 0) $lowPercent = 0;

        $topAgent = TicketAssignment::query()
            ->join('tickets', 'ticket_assignments.ticket_id', '=', 'tickets.ticket_id')
            ->join('users', 'ticket_assignments.user_id', '=', 'users.user_id')
            ->select('users.full_name', DB::raw('count(tickets.ticket_id) as solved_count'))
            ->where('tickets.status', 'closed')
            ->when($companyId, fn($q) => $q->where('tickets.company_id', $companyId))
            ->when(!empty($departmentIds), fn($q) => $q->whereIn('tickets.department_id', $departmentIds))
            ->groupBy('users.full_name')
            ->orderByDesc('solved_count')
            ->first();

        $weeklyActivityData = $this->getWeeklyActivityData($companyId, $departmentIds);

        return view('livewire.pages.admin.dashboard', [
            'weeklyTicketsCount' => $weeklyTicketsCount,
            'weeklyRoomBookingsCount' => $weeklyRoomBookingsCount,
            'weeklyInformationCount' => $weeklyInformationCount,
            'topAgent' => $topAgent,
            'totalTicketsThisMonth' => $totalTicketsThisMonth,
            'highPercent' => $highPercent,
            'mediumPercent' => $mediumPercent,
            'lowPercent' => $lowPercent,
            'weeklyActivityData' => $weeklyActivityData,
            'departmentList' => $departmentList, 
        ]);
    }
}