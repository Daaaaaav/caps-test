<?php

namespace App\Livewire\Pages\User;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Throwable;

#[Layout('layouts.app')]
#[Title('Profile')]
class Profile extends Component
{
    public array $profile = [];
    public array $stats = [];

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user()->loadMissing(['department', 'company', 'role']);

        $this->profile = [
            'fullName'     => $user->name,
            'email'        => $user->email,
            'phone_number' => $user->phone_number ?? null,
            'employeeId'   => $user->employee_id ?? null,
            'department'   => optional($user->department)->department_name ?? '-',
            'company'      => optional($user->company)->company_name ?? '-',
            'joinDate'     => optional($user->joined_at ?? $user->created_at)?->format('M Y'),
            'role'         => optional($user->role)->name ?? (is_string($user->role) ? $user->role : '-'),
        ];

        $this->stats = [
            'totalTickets'     => method_exists($user, 'tickets')  ? $user->tickets()->count() : 0,
            'totalBookRooms'   => method_exists($user, 'rooms') ? $user->rooms()->count() : 0,
            'totalBookVehicle' => method_exists($user, 'vehicles') ? $user->vehicles()->count() : 0,
            'memberSince'      => optional($user->created_at)?->format('M Y'),
        ];
    }

    public function updatePassword(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        try {
            $validated = $this->validate([
                'current_password'            => ['required', 'string'],
                'new_password'                => ['required', 'string', 'confirmed', PasswordRule::min(8)],
            ]);

            $user = auth()->user();

            // Check current password
            if (! Hash::check($this->current_password, $user->password)) {
                $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Kata sandi saat ini salah.', duration: 3000);
                return;
            }

            // Ensure new password is different
            if (Hash::check($this->new_password, $user->password)) {
                $this->dispatch('toast', type: 'warning', title: 'Tidak Valid', message: 'Kata sandi baru harus berbeda dari yang lama.', duration: 3000);
                return;
            }

            // Save new password
            $user->password = Hash::make($this->new_password);
            $saved = $user->save();

            if (! $saved) {
                $this->dispatch('toast', type: 'error', title: 'Gagal', message: 'Gagal memperbarui kata sandi. Silakan coba lagi.', duration: 3000);
                return;
            }

            // Reset fields
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

            // Success toast
            $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Kata sandi berhasil diperbarui.', duration: 3000);
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', title: 'Validasi Gagal', message: 'Periksa kembali input Anda.', duration: 3000);
            throw $e;
        } catch (Throwable $e) {
            $this->dispatch('toast', type: 'error', title: 'Kesalahan', message: 'Terjadi kesalahan tak terduga.', duration: 3000);
        }
    }

    public function render()
    {
        return view('livewire.pages.user.profile');
    }
}
