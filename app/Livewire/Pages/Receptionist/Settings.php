<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.receptionist')]
#[Title('Settings')]
class Settings extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    public bool $showPasswordSection = false;
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name  = $user->full_name ?? $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone_number ?? '';
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id() . ',user_id',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->full_name    = $this->name;
        $user->email        = $this->email;
        $user->phone_number = $this->phone;
        $user->save();

        $this->successMessage = 'Profile updated successfully.';
        $this->errorMessage   = null;
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => 'required',
            'newPassword'     => 'required|min:8',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'confirmPassword.same' => 'New password and confirmation do not match.',
        ]);

        $user = Auth::user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->errorMessage   = 'Current password is incorrect.';
            $this->successMessage = null;
            return;
        }

        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->currentPassword = '';
        $this->newPassword     = '';
        $this->confirmPassword = '';
        $this->showPasswordSection = false;

        $this->successMessage = 'Password changed successfully.';
        $this->errorMessage   = null;
    }

    public function render()
    {
        return view('livewire.pages.receptionist.settings');
    }
}
