<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    public array $templates = [
        'default_full'        => 'CodeIgniter\Pager\Views\default_full',
        'default_simple'      => 'CodeIgniter\Pager\Views\default_simple',
        'default_head'        => 'CodeIgniter\Pager\Views\default_head',
        // ✅ Template kustom Tailwind
        'tailwind_pagination' => 'App\Views\layout\tailwind_pagination',
    ];

    public int $perPage = 20;
}
