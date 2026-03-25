<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * CSRF Protection method: 'cookie' or 'session'
     */
    public string $csrfProtection = 'cookie';

    /**
     * CSRF token randomization
     */
    public bool $tokenRandomize = true;

    /**
     * CSRF token name
     */
    public string $tokenName = 'csrf_token';

    /**
     * CSRF header name
     */
    public string $headerName = 'X-CSRF-TOKEN';

    /**
     * CSRF cookie name
     */
    public string $cookieName = 'csrf_cookie';

    /**
     * CSRF token lifetime in seconds
     */
    public int $expires = 7200;

    /**
     * Regenerate CSRF token on every request
     */
    public bool $regenerate = true;

    /**
     * Clean old CSRF tokens
     */
    public bool $redirect = false;

    /**
     * SameSite cookie attribute: 'None', 'Lax', or 'Strict'
     */
    public string $samesite = 'Lax';
}
