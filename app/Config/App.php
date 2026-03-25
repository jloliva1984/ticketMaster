<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * App Configuration
 *
 * @phpstan-consistent-constructor
 */
class App extends BaseConfig
{
    /*
    |--------------------------------------------------------------------------
    | Base Site URL
    |--------------------------------------------------------------------------
    | Set via .env: app.baseURL
    | Example for subdirectory: 'https://yourdomain.com/ticketmaster/'
    | Example for subdomain:    'https://tickets.yourdomain.com/'
    */
    public string $baseURL = 'http://localhost:8080/';

    /*
    |--------------------------------------------------------------------------
    | Allowed Hostnames in URL
    |--------------------------------------------------------------------------
    | Set via .env: app.allowedHostnames
    */
    public array $allowedHostnames = [];

    /*
    |--------------------------------------------------------------------------
    | Index File
    |--------------------------------------------------------------------------
    | Set to '' when using mod_rewrite (recommended).
    */
    public string $indexPage = '';

    /*
    |--------------------------------------------------------------------------
    | URI Protocol
    |--------------------------------------------------------------------------
    */
    public string $uriProtocol = 'REQUEST_URI';

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    */
    public string $defaultLocale = 'en';

    /*
    |--------------------------------------------------------------------------
    | Negotiate Locale
    |--------------------------------------------------------------------------
    */
    public bool $negotiateLocale = false;

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    */
    public array $supportedLocales = ['en', 'es'];

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */
    public string $appTimezone = 'America/New_York';

    /*
    |--------------------------------------------------------------------------
    | Default Character Set
    |--------------------------------------------------------------------------
    */
    public string $charset = 'UTF-8';

    /*
    |--------------------------------------------------------------------------
    | Force Global Secure Requests
    |--------------------------------------------------------------------------
    | Set to true to force HTTPS on production.
    | Set via .env: app.forceGlobalSecureRequests
    */
    public bool $forceGlobalSecureRequests = false;

    /*
    |--------------------------------------------------------------------------
    | Proxy IPs
    |--------------------------------------------------------------------------
    */
    public string|array $proxyIPs = '';

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    public bool $CSPEnabled = false;
}
