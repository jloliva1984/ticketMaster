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
     * Filter aliases — mapped to their fully-qualified class names
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

        // Application filters
        'locale'        => \App\Filters\LocaleFilter::class,
        'auth'          => \App\Filters\AuthFilter::class,
        'admin'         => \App\Filters\AdminFilter::class,
        'csrftoken'     => \App\Filters\CsrfTokenFilter::class,
    ];

    /**
     * Framework-required filters (always run)
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
            'locale',                        // Restore language from session
            'csrf' => ['except' => [         // Skip CSRF for API/webhook endpoints
                'api/*',
                'ping',
            ]],
        ],
        'after' => ['csrftoken'],
    ];

    /**
     * Per-HTTP-method filters
     */
    public array $methods = [];

    /**
     * Per-URI-pattern filters
     */
    public array $filters = [];
}
