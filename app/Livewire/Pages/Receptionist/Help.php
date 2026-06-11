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

    public function getFaqs(): array
    {
        return [
            [
                'question' => __('app.faq_rec_q1'),
                'answer'   => __('app.faq_rec_a1'),
                'category' => __('app.faq_cat_room_booking'),
            ],
            [
                'question' => __('app.faq_rec_q2'),
                'answer'   => __('app.faq_rec_a2'),
                'category' => __('app.faq_cat_room_booking'),
            ],
            [
                'question' => __('app.faq_rec_q3'),
                'answer'   => __('app.faq_rec_a3'),
                'category' => __('app.faq_cat_vehicle'),
            ],
            [
                'question' => __('app.faq_rec_q4'),
                'answer'   => __('app.faq_rec_a4'),
                'category' => __('app.faq_cat_vehicle'),
            ],
            [
                'question' => __('app.faq_rec_q5'),
                'answer'   => __('app.faq_rec_a5'),
                'category' => __('app.faq_cat_guestbook'),
            ],
            [
                'question' => __('app.faq_rec_q6'),
                'answer'   => __('app.faq_rec_a6'),
                'category' => __('app.faq_cat_docpac'),
            ],
            [
                'question' => __('app.faq_rec_q7'),
                'answer'   => __('app.faq_rec_a7'),
                'category' => __('app.faq_cat_docpac'),
            ],
            [
                'question' => __('app.faq_shared_q_password'),
                'answer'   => __('app.faq_shared_a_password'),
                'category' => __('app.faq_cat_account'),
            ],
        ];
    }

    public function filteredFaqs(): array
    {
        $faqs = $this->getFaqs();

        if (blank($this->search)) {
            return $faqs;
        }

        $q = strtolower($this->search);

        return array_values(array_filter($faqs, function ($faq) use ($q) {
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
