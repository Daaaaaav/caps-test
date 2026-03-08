<?php

namespace App\Livewire\Components\Ui;

use Livewire\Component;
use Livewire\Attributes\On; // Import the Livewire v3 attribute

class ChatModal extends Component
{
    // The state property, entangled with Alpine's 'show'
    public $isOpen = false;

    // Listen for the global event dispatched from the floating button
    #[On('openChatModal')]
    public function openModal()
    {
        // Debugging: Confirmation the PHP component received the event.
        $this->js('console.log("LIVEWIRE COMPONENT (ChatModal.php): Event \'openChatModal\' successfully received. Setting \$isOpen = true.");');
        
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.components.ui.chat-modal');
    }
}