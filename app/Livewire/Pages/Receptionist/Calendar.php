<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;


#[Layout('layouts.receptionist')]
#[Title('Calendar')]
class Calendar extends Component
{
    public function render()
    {
        return view('livewire.pages.receptionist.calendar');
    }
}
