<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * Autoloader Configuration
 *
 * Adds application namespaces and classmap entries.
 * Most classes are autoloaded via Composer (vendor/autoload.php).
 */
class Autoload extends AutoloadConfig
{
    /**
     * PSR-4 namespace map.
     * 'Namespace' => '/path/to/dir'
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'Config'      => APPPATH . 'Config',
    ];

    /**
     * Class map — used for classes that don't follow PSR-4.
     */
    public $classmap = [];

    /**
     * Files to load on every request (helpers, etc.).
     */
    public $files = [];

    /**
     * Helpers to load automatically.
     */
    public $helpers = [];
}
