<?php

namespace App\Livewire\Pages\Receptionist;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.receptionist')]
#[Title('Help')]
class Help extends Component
{
    public string $search = '';

    public array $faqs = [
        [
            'question' => 'How do I book a meeting room?',
            'answer'   => 'Go to Room Management → Booking Room. Fill in the meeting title, select a room, choose your date and time, then submit. Your booking will be pending approval.',
            'category' => 'Room Booking',
        ],
        [
            'question' => 'How do I approve or reject a room booking?',
            'answer'   => 'Navigate to Room Management → Booking Approval. You will see all pending requests. Click Approve or Reject on each entry.',
            'category' => 'Room Booking',
        ],
        [
            'question' => 'How do I book a vehicle?',
            'answer'   => 'Go to Vehicle Management → Book Vehicle. Enter the borrower name, purpose, destination, and travel times, then submit.',
            'category' => 'Vehicle',
        ],
        [
            'question' => 'How do I check vehicle status?',
            'answer'   => 'Go to Vehicle Management → Vehicle Status to see the current status of all vehicles in real time.',
            'category' => 'Vehicle',
        ],
        [
            'question' => 'How do I register a guest?',
            'answer'   => 'Go to Guest Management → GuestBook. Fill in the guest\'s name, purpose, and check-in time, then save the entry.',
            'category' => 'Guestbook',
        ],
        [
            'question' => 'How do I submit a DocPac form?',
            'answer'   => 'Go to DocPac Management → DocPac Form. Complete all required fields and attach any necessary documents before submitting.',
            'category' => 'DocPac',
        ],
        [
            'question' => 'How do I track a DocPac status?',
            'answer'   => 'Go to DocPac Management → DocPac Status to see the current processing status of all submitted document packages.',
            'category' => 'DocPac',
        ],
        [
            'question' => 'How do I change my password?',
            'answer'   => 'Go to Settings (the cog icon in the sidebar). Expand the Change Password section, enter your current password and your new password, then save.',
            'category' => 'Account',
        ],
    ];

    public function filteredFaqs(): array
    {
        if (blank($this->search)) {
            return $this->faqs;
        }

        $q = strtolower($this->search);

        return array_values(array_filter($this->faqs, function ($faq) use ($q) {
            return str_contains(strtolower($faq['question']), $q)
                || str_contains(strtolower($faq['answer']), $q)
                || str_contains(strtolower($faq['category']), $q);
        }));
    }

    public function render()
    {
        return view('livewire.pages.receptionist.help', [
            'filteredFaqs' => $this->filteredFaqs(),
        ]);
    }
}
