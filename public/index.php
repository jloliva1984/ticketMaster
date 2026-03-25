<?php

/**
 * CodeIgniter 4 — Front Controller
 * TicketMaster.LT
 *
 * This is the ONLY entry point for the application.
 * All HTTP requests are routed here via public/.htaccess.
 */

/*
|--------------------------------------------------------------------------
| Minimum PHP Version Check
|--------------------------------------------------------------------------
*/
$minPhpVersion = '8.1';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'PHP %s or higher is required. Server is running PHP %s.',
        $minPhpVersion,
        PHP_VERSION
    );
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;
    exit(1);
}

/*
|--------------------------------------------------------------------------
| FCPATH — path to this directory (with trailing slash)
|--------------------------------------------------------------------------
| MUST be defined before loading Paths.php or the bootstrap.
*/
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

/*
|--------------------------------------------------------------------------
| Load Paths Configuration
|--------------------------------------------------------------------------
| Paths.php defines where the system/, app/, writable/ directories live.
| Using __DIR__ inside Paths.php makes this completely portable.
*/
require FCPATH . '../app/Config/Paths.php';

$pathsConfig = new Config\Paths();

/*
|--------------------------------------------------------------------------
| Define Path Constants
|--------------------------------------------------------------------------
*/
define('SYSTEMPATH', rtrim($pathsConfig->systemDirectory, '\\/') . DIRECTORY_SEPARATOR);
define('APPPATH',    rtrim($pathsConfig->appDirectory,    '\\/') . DIRECTORY_SEPARATOR);
define('ROOTPATH',   realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
define('WRITEPATH',  rtrim($pathsConfig->writableDirectory, '\\/') . DIRECTORY_SEPARATOR);
define('TESTPATH',   rtrim($pathsConfig->testsDirectory,    '\\/') . DIRECTORY_SEPARATOR);

if (! isset($pathsConfig->viewDirectory) || $pathsConfig->viewDirectory === '') {
    define('VIEWPATH', APPPATH . 'Views' . DIRECTORY_SEPARATOR);
} else {
    define('VIEWPATH', rtrim($pathsConfig->viewDirectory, '\\/') . DIRECTORY_SEPARATOR);
}

/*
|--------------------------------------------------------------------------
| Bootstrap CodeIgniter
|--------------------------------------------------------------------------
| system/bootstrap.php loads Composer's autoloader, sets up the
| framework, and returns the configured CodeIgniter application instance.
*/
require SYSTEMPATH . 'bootstrap.php';
