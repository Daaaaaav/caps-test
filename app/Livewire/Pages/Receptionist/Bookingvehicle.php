<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\VehicleBooking;
use App\Models\Vehicle;
use App\Models\Department;
use App\Models\User;

#[Layout('layouts.receptionist')]
#[Title('Vehicle Booking')]
class Bookingvehicle extends Component
{
    // form fields
    public ?int $department_id = null;
    public ?int $borrower_user_id = null;
    public string $borrower_name = '';
    public ?int $vehicle_id = null;

    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $start_time = null;
    public ?string $end_time = null;

    public string $purpose = '';
    public ?string $destination = null;
    public string $odd_even_area = 'tidak';      // sesuaikan enum di DB
    public ?string $purpose_type = null;         // jenis keperluan
    public ?string $purpose_type_other = null;   // untuk opsi "lainnya"
    public bool $terms_agreed = false;

    /** @var \Illuminate\Support\Collection */
    public $departments;
    public $users;
    public $vehicles;
    public bool $hasVehicles = false;

    // search box seperti di MeetingSchedule
    public string $departmentSearch = '';
    public string $userSearch = '';

    protected string $tz = 'Asia/Jakarta';

    protected function rules(): array
    {
        return [
            'department_id'        => ['required', 'integer', 'exists:departments,department_id'],
            'borrower_user_id'     => ['nullable', 'integer', 'exists:users,user_id'],
            'borrower_name'        => ['required_without:borrower_user_id', 'string', 'max:255'],
            'vehicle_id'           => ['required', 'integer', 'exists:vehicles,vehicle_id'],

            'date_from'            => ['required', 'date'],
            'date_to'              => ['required', 'date', 'after_or_equal:date_from'],
            'start_time'           => ['required'],
            'end_time'             => ['required'],

            'purpose'              => ['required', 'string', 'max:255'],
            'destination'          => ['nullable', 'string', 'max:255'],
            'odd_even_area'        => ['nullable', 'string', 'max:50'],
            'purpose_type'         => ['nullable', 'string', 'max:50'],
            'purpose_type_other'   => ['required_if:purpose_type,lainnya', 'nullable', 'string', 'max:255'],
            'terms_agreed'         => ['accepted'],
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        // semua departemen di company ini, urut A-Z
        $this->departments = Department::where('company_id', $companyId)
            ->orderBy('department_name', 'asc')
            ->get();

        // user awal kosong, baru diisi ketika departemen dipilih
        $this->users = collect();

        // kendaraan aktif, urut nama A-Z
        $this->vehicles = Vehicle::where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get(['vehicle_id', 'name', 'plate_number']);

        $this->hasVehicles = $this->vehicles->count() > 0;

        // default tanggal = hari ini
        $today = now($this->tz)->toDateString();
        $this->date_from = $today;
        $this->date_to   = $today;
    }

    /**
     * Auto-called saat departemen diganti.
     */
    public function updatedDepartmentId($value): void
    {
        // reset pilihan + search user
        $this->borrower_user_id = null;
        $this->userSearch = '';

        if (!$value) {
            // kalau dikosongkan, list user dikosongkan
            $this->users = collect();
            return;
        }

        $authUser  = Auth::user();
        $companyId = (int) ($authUser?->company_id ?? 0);

        // load user hanya dari departemen terpilih, urut A-Z
        $this->users = User::where('company_id', $companyId)
            ->where('department_id', $value)
            ->orderBy('full_name', 'asc')
            ->get();
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();
        $companyId = (int) ($user?->company_id ?? 0);

        $startAt = Carbon::parse($this->date_from.' '.$this->start_time, $this->tz);
        $endAt   = Carbon::parse($this->date_to.' '.$this->end_time, $this->tz);

        // nama borrower dari user yang dipilih, atau dari input text
        $borrowerName = $this->borrower_name;
        if ($this->borrower_user_id) {
            $u = $this->users->firstWhere('user_id', $this->borrower_user_id);
            if ($u) {
                $borrowerName = $u->full_name;
            }
        }

        // jika keperluan_type = lainnya + ada detail lainnya, boleh digabung ke purpose
        if ($this->purpose_type === 'lainnya' && $this->purpose_type_other) {
            $this->purpose .= ' (Lainnya: '.$this->purpose_type_other.')';
        }

        VehicleBooking::create([
            'vehicle_id'     => $this->vehicle_id,
            'company_id'     => $companyId,
            'department_id'  => $this->department_id,
            'user_id'        => $this->borrower_user_id,
            'borrower_name'  => $borrowerName,
            'start_at'       => $startAt,
            'end_at'         => $endAt,
            'purpose'        => $this->purpose,
            'destination'    => $this->destination,
            'odd_even_area'  => $this->odd_even_area,
            'purpose_type'   => $this->purpose_type,
            'terms_agreed'   => 1,
            'is_approve'     => 0,
            'status'         => 'pending',
            'notes'          => null,
        ]);

        $this->dispatch('$refresh');
        $this->dispatch('toast', type: 'success', title: 'Ditambah', message: 'Terkirim..', duration: 3000);

        // reset form (list departemen/vehicles tetap)
        $this->reset([
            'department_id',
            'borrower_user_id',
            'borrower_name',
            'vehicle_id',
            'date_from',
            'date_to',
            'start_time',
            'end_time',
            'purpose',
            'destination',
            'odd_even_area',
            'purpose_type',
            'purpose_type_other',
            'terms_agreed',
            'departmentSearch',
            'userSearch',
        ]);

        // list user dikosongkan lagi
        $this->users = collect();

        $today = now($this->tz)->toDateString();
        $this->date_from     = $today;
        $this->date_to       = $today;
        $this->odd_even_area = 'tidak';
    }

    public function render()
    {
        // === FILTER + SORT DEPARTEMEN BERDASARKAN SEARCH ===
        $departments = $this->departments;

        if (trim($this->departmentSearch) !== '') {
            $term = mb_strtolower(trim($this->departmentSearch));

            $departments = $departments->filter(function ($d) use ($term) {
                return str_contains(mb_strtolower($d->department_name ?? ''), $term);
            });
        }

        // pastikan tetap urut A-Z setelah filter
        $departments = $departments
            ->sortBy('department_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // === FILTER + SORT USERS (KALAU SUDAH ADA DEPARTEMEN) ===
        $users = $this->users;

        if ($this->department_id) {
            if (trim($this->userSearch) !== '') {
                $term = mb_strtolower(trim($this->userSearch));

                $users = $users->filter(function ($u) use ($term) {
                    return str_contains(mb_strtolower($u->full_name ?? ''), $term)
                        || str_contains(mb_strtolower($u->email ?? ''), $term);
                });
            }

            // pastikan user juga tetap urut A-Z setelah filter
            $users = $users
                ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } else {
            // kalau belum pilih departemen, kosongin saja
            $users = collect();
        }

        return view('livewire.pages.receptionist.bookingvehicle', [
            'departments' => $departments,
            'users'       => $users,
            'vehicles'    => $this->vehicles,
            'hasVehicles' => $this->hasVehicles,
        ]);
    }
}
