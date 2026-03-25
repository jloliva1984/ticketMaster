<?php

namespace Config;

/**
 * Paths Configuration
 *
 * Defines the location of the key directories used by CodeIgniter.
 * IMPORTANT: Uses __DIR__ relative paths to avoid issues on shared
 * hosting environments where absolute paths may change.
 */
class Paths
{
    /**
     * Path to the system directory.
     * When installed via Composer this points into vendor/.
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * Path to the application directory.
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Path to the writable directory.
     * Must be writable by the web server.
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * Path to the tests directory.
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * Path to the views directory.
     * Leave empty to use the default (appDirectory/Views).
     */
    public string $viewDirectory = __DIR__ . '/../Views';
}
