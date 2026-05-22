<?php

namespace App\Livewire\Pages\Superadmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.superadmin')]
#[Title('Help')]
class Help extends Component
{
    public string $search = '';

    public array $faqs = [
        [
            'question' => 'How do I manage receptionist accounts?',
            'answer'   => 'Go to User Management → Receptionists. You can create, edit, or deactivate receptionist accounts from that page.',
            'category' => 'User Management',
        ],
        [
            'question' => 'How do I view room booking statistics?',
            'answer'   => 'Go to Analytics → Room Bookings. You will see charts and tables summarising all room booking activity.',
            'category' => 'Analytics',
        ],
        [
            'question' => 'How do I view vehicle booking statistics?',
            'answer'   => 'Go to Analytics → Vehicle Bookings for a full breakdown of vehicle usage and booking trends.',
            'category' => 'Analytics',
        ],
        [
            'question' => 'How do I view delivery statistics?',
            'answer'   => 'Go to Analytics → Deliveries to see incoming and outgoing delivery records and their statuses.',
            'category' => 'Analytics',
        ],
        [
            'question' => 'How do I view guestbook statistics?',
            'answer'   => 'Go to Analytics → Guestbook for visitor frequency data and entry history.',
            'category' => 'Analytics',
        ],
        [
            'question' => 'What is the Visitor Predictions page?',
            'answer'   => 'The Visitor Predictions page (AI & Security System → Visitor Predictions) uses an LSTM model to forecast future visitor volumes based on historical data.',
            'category' => 'AI & Security',
        ],
        [
            'question' => 'What is Occupancy Forecasting?',
            'answer'   => 'Occupancy Forecasting (AI & Security System → Occupancy Forecast) predicts room occupancy rates for upcoming periods to help with resource planning.',
            'category' => 'AI & Security',
        ],
        [
            'question' => 'What are Security Reports?',
            'answer'   => 'Security Reports (AI & Security System → Security Reports) surfaces anomalies and flagged events detected by the AI monitoring layer.',
            'category' => 'AI & Security',
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
        return view('livewire.pages.superadmin.help', [
            'filteredFaqs' => $this->filteredFaqs(),
        ]);
    }
}
