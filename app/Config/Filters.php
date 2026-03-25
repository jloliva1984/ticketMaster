<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    /**
     * Configured filter aliases
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'forcehttps'    => ForceHTTPS::class,
        // Phase 3: auth filter
        // 'auth'          => \App\Filters\AuthFilter::class,
        // 'admin'         => \App\Filters\AdminFilter::class,
    ];

    /**
     * List of filter processing rules.
     */
    public array $required = [
        'before' => [
            'forcehttps',  // Redirect HTTP → HTTPS in production
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * Filters that run before every request.
     */
    public array $globals = [
        'before' => [
            'csrf' => ['except' => []],
        ],
        'after' => [],
    ];

    /**
     * Filters that apply to specific HTTP methods.
     */
    public array $methods = [];

    /**
     * Filters that apply to specific URI patterns.
     */
    public array $filters = [];
}
