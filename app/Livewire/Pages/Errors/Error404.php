<?php

namespace App\Livewire\Pages\Errors;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class Error404 extends Component
{
    public function render()
    {
        return view('livewire.pages.errors.error404');
    }
}
