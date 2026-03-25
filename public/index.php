<?php

use CodeIgniter\Boot;
use Config\Paths;

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
*/
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
|--------------------------------------------------------------------------
| Load Paths Configuration
|--------------------------------------------------------------------------
*/
require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

/*
|--------------------------------------------------------------------------
| Bootstrap CodeIgniter
|--------------------------------------------------------------------------
*/
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
