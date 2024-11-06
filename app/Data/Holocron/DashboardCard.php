<?php

namespace App\Data\Holocron;

class DashboardCard
{
    public function __construct(public string $heading, public string $link, public string $icon, public array $data = [])
    {
    }
}
