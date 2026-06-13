<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'throttle'      => \App\Filters\ThrottleFilter::class,
        'webAuth'       => \App\Filters\WebAuthFilter::class,
        'apiAuth'       => \App\Filters\ApiAuthFilter::class,
        'dynamicAccess' => \App\Filters\DynamicAccessFilter::class,
    ];

    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
        ],
    ];

    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['api/*']],
        ],
        'after' => [
            'secureheaders',
            'toolbar' => ['except' => ['api/*']],
        ],
    ];

    public array $methods = [];

    public array $filters = [
        'throttle' => ['before' => ['api/v1/auth/login', 'admin/login']],
    ];
}
