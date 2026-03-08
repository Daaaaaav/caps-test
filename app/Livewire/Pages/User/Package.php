<?php

namespace App\Livewire\Pages\User;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('PackagePage')]
class Package extends Component
{
    public function render()
    {
        return view('livewire.pages.user.package');
    }
}
