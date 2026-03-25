<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Session extends BaseConfig
{
    /**
     * Session driver class.
     * FileHandler is safest for shared hosting.
     */
    public string $driver = 'CodeIgniter\Session\Handlers\FileHandler';

    /**
     * Session cookie name
     */
    public string $cookieName = 'tm_session';

    /**
     * Session expiration in seconds (0 = until browser close)
     */
    public int $expiration = 7200;

    /**
     * Save path.
     * NULL = use system temp dir; or set absolute path in .env
     * Recommended: set WRITEPATH . 'session' on production.
     */
    public string $savePath = WRITEPATH . 'session';

    /**
     * Match user IP address?
     */
    public bool $matchIP = false;

    /**
     * How often to regenerate session ID (seconds)
     */
    public int $timeToUpdate = 300;

    /**
     * Destroy old session data on regeneration?
     */
    public bool $regenerateDestroy = false;

    /**
     * Database group (used only by DatabaseHandler)
     */
    public ?string $DBGroup = null;

    /**
     * Table name (used only by DatabaseHandler)
     */
    public string $DBTable = 'ci_sessions';
}
