<?php

namespace App\Livewire\Pages\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Announcement;
use App\Models\Information;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\Wifi;

#[Layout('layouts.app')]
#[Title('HomePage')]
class Home extends Component
{
    public string $activeTab = 'upcoming';
    protected string $tz = 'Asia/Jakarta';

    // Data Umum
    public $announcements = [];
    public $informations = [];

    // Status User
    public bool $isAgent = false;

    // --- DATA UNTUK USER (REQUESTER) ---
    public int $totalTickets = 0;
    public int $ticketsOpen = 0;
    public int $ticketsProgress = 0;
    public int $ticketsResolved = 0;
    public int $ticketsClosed = 0;

    // --- DATA UNTUK AGENT (WORKER) ---
    public int $agentQueueCount = 0; // Jumlah antrian departemen
    public int $agentClaimCount = 0; // Jumlah yang sedang dikerjakan agent ini
    public $agentQueuePreview = [];  // List preview antrian

    // --- SYSTEM HEALTH ---
    public string $dbStatus = 'Checking...';
    public string $serverStatus = 'Checking...';
    public string $networkLatency = '...';
    public string $networkColor = 'text-gray-500 bg-gray-100';
    public $wifis = [];

    public function mount(): void
    {
        $user = Auth::user();
        // Cek apakah user adalah agent
        $this->isAgent = ($user->is_agent === 'yes');

        $this->loadAnnouncementsAndInformations();
        $this->loadWifis(); // <--- Load Wifi
        $this->checkSystemHealth();
    }

    public function getUserName(): string
    {
        return Auth::user()?->full_name ?? 'Team Member';
    }

    public function checkSystemHealth()
    {
        // 1. Refresh Data Tiket (Sesuai Role)
        $this->loadTicketOverview();

        // 2. Cek Database
        try {
            DB::connection()->getPdo();
            $this->dbStatus = 'OK';
        } catch (\Exception $e) {
            $this->dbStatus = 'DOWN';
        }

        // 3. Cek Server
        $freeSpace = @disk_free_space('.');
        $totalSpace = @disk_total_space('.');
        if ($freeSpace !== false && $totalSpace !== false && $totalSpace > 0) {
            $percentageFree = ($freeSpace / $totalSpace) * 100;
            $this->serverStatus = $percentageFree > 10 ? 'OK' : 'FULL';
        } else {
            $this->serverStatus = 'Unknown';
        }

        // 4. Cek Network
        $startTime = microtime(true);
        try {
            $fsock = @fsockopen('www.google.com', 80, $errno, $errstr, 1);
            if ($fsock) {
                $endTime = microtime(true);
                $latency = round(($endTime - $startTime) * 1000);
                $this->networkLatency = $latency . 'ms';
                fclose($fsock);

                if ($latency < 150) {
                    $this->networkColor = 'text-green-700 bg-green-100';
                } elseif ($latency < 400) {
                    $this->networkColor = 'text-yellow-700 bg-yellow-100';
                } else {
                    $this->networkColor = 'text-red-700 bg-red-100';
                }
            } else {
                $this->networkLatency = 'Timeout';
                $this->networkColor = 'text-red-700 bg-red-100';
            }
        } catch (\Exception $e) {
            $this->networkLatency = 'Offline';
            $this->networkColor = 'text-red-700 bg-red-100';
        }
    }

    protected function loadTicketOverview(): void
    {
        if ($this->isAgent) {
            $this->loadAgentData();
        } else {
            $this->loadRequesterData();
        }
    }

    // --- LOGIC AGENT (Mirip Ticketqueue.php) ---
    private function loadAgentData()
    {
        $user = Auth::user();

        // 1. Hitung Queue (Antrian Departemen Agent)
        // Syarat: Dept sama, bukan user itu sendiri, status bukan resolved/closed, belum ada assignment aktif
        $queueQuery = Ticket::query()
            ->where('company_id', $user->company_id)
            ->where('department_id', $user->department_id) // Hanya tiket untuk departemen agent ini
            ->where('user_id', '!=', $user->user_id)       // Bukan tiket buatan sendiri
            ->whereNotIn('status', ['RESOLVED', 'CLOSED']) // Tiket yang masih butuh pengerjaan
            ->whereDoesntHave('assignments', function ($q) {
                $q->whereNull('deleted_at'); // Belum diambil orang lain
            });

        $this->agentQueueCount = $queueQuery->count();

        // Ambil 5 tiket terbaru untuk preview di dashboard
        $this->agentQueuePreview = $queueQuery
            ->with(['user']) // Mengambil nama pembuat tiket
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 2. Hitung My Claims (Yang sedang dikerjakan agent ini)
        $this->agentClaimCount = TicketAssignment::query()
            ->where('user_id', $user->user_id)
            ->whereNull('deleted_at')
            ->whereRelation('ticket', 'status', '!=', 'CLOSED')
            ->count();
    }

    // --- LOGIC USER BIASA ---
    private function loadRequesterData()
    {
        $user = Auth::user();

        $stats = Ticket::where('user_id', $user->user_id)
            ->selectRaw("count(*) as total")
            ->selectRaw("count(case when status = 'OPEN' then 1 end) as open")
            ->selectRaw("count(case when status = 'IN_PROGRESS' then 1 end) as progress")
            ->selectRaw("count(case when status = 'RESOLVED' then 1 end) as resolved")
            ->selectRaw("count(case when status = 'CLOSED' then 1 end) as closed")
            ->first();

        $this->totalTickets = $stats->total ?? 0;
        $this->ticketsOpen = $stats->open ?? 0;
        $this->ticketsProgress = $stats->progress ?? 0;
        $this->ticketsResolved = $stats->resolved ?? 0;
        $this->ticketsClosed = $stats->closed ?? 0;
    }

    protected function loadWifis(): void
    {
        $user = Auth::user();
        $companyId = (int) ($user->company_id ?? 0);

        $this->wifis = Wifi::where('company_id', $companyId)
            ->where('is_active', 1)
            ->get()
            ->toArray(); // Ubah ke array agar mudah dibaca AlpineJS
    }
    protected function loadAnnouncementsAndInformations(): void
    {
        // ... (Kode Announcements & Information tetap sama seperti sebelumnya) ...
        $user = Auth::user();
        $companyId = (int) ($user->company_id ?? 0);
        $deptId = $user->department_id ? (int) $user->department_id : null;

        $annBase = Announcement::query();
        if (method_exists(Announcement::class, 'scopeForCompany')) {
            $annBase = $annBase->forCompany($companyId);
        } else {
            $annBase = $annBase->where('company_id', $companyId);
        }

        $this->announcements = $annBase
            ->orderBy('event_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->informations = Information::forCompany($companyId)
            ->forDepartment($deptId)
            ->orderBy('event_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($this->informations->isEmpty()) {
            $this->informations = Information::forCompany($companyId)
                ->orderBy('event_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.pages.user.home');
    }
}