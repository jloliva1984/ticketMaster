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
     * Filter aliases
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
        'locale'        => \App\Filters\LocaleFilter::class,

        // Phase 3 — uncomment when auth is implemented
        // 'auth'  => \App\Filters\AuthFilter::class,
        // 'admin' => \App\Filters\AdminFilter::class,
    ];

    /**
     * Always-run filters (framework-level)
     */
    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    /**
     * Global filters — run on every request
     */
    public array $globals = [
        'before' => [
            'locale',                       // Set locale from session
            'csrf' => ['except' => [        // CSRF protection (except API)
                'api/*',
                'ping',
            ]],
        ],
        'after' => [],
    ];

    /**
     * Per-HTTP-method filters
     */
    public array $methods = [];

    /**
     * Per-URI filters
     */
    public array $filters = [];
}
