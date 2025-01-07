<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Article;
use Illuminate\View\View;
use Livewire\Component;

class Home extends Component
{
    public function render(): View
    {
        return view('pages.home', [
            'articles' => $this->articles(),
            'cvItems' => $this->cvItems(),
            'contactItems' => $this->contactItems(),
        ]);
    }

    private function articles()
    {
        return Article::published()
            ->take(5)
            ->get()
            ->pluck('frontmatter');
    }

    private function cvItems()
    {
        return [
            [
                'title' => 'Chief Executive Officer',
                'date' => '08.2020 – today',
                'employer' => 'cäsar gustav Digitalagentur',
                'link' => 'https://www.caesargustav.de',
                'content' => 'Focusing on optimizing our processes, internal mentoring and architectural development decisions. Consulting of key accounts to empower them to reach their full potential.',
            ],
            [
                'title' => 'Teamlead Web Development',
                'date' => '11.2016 – 07.2020',
                'employer' => 'cäsar gustav Digitalagentur',
                'link' => 'https://www.caesargustav.de',
                'content' => 'Working together with 6 great colleagues. Responsible for internal training and self-development. Technical contact for key accounts. Furthering development of the used custom ecommerce solution.',
            ],
            [
                'title' => 'Frontend developer',
                'date' => '12.2015 – 10.2016',
                'employer' => 'cäsar gustav Digitalagentur',
                'link' => 'https://www.caesargustav.de',
                'content' => 'Implementation of custom ecommerce projects in the pharmaceutical industry.',
            ],
            [
                'title' => 'Junior Account Manager',
                'date' => '10.2012 – 11.2015',
                'employer' => 'executive now GmbH',
                'link' => 'https://www.executivenow.eu',
                'content' => 'Digital consulting, client success, conception and implementation of strategies to reach clients on the go. Assistant to the CEO.',
            ],
        ];
    }

    private function contactItems(): array
    {
        return [
            [
                'title' => 'timkley@gmail.com',
                'url' => 'mailto:timkley@gmail.com',
            ],
            [
                'title' => 'GitHub',
                'url' => 'https://github.com/timkley',
            ],
        ];
    }
}
