<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    /**
     * Minimum log level. In production use 'warning' or 'error'.
     * Levels: emergency, alert, critical, error, warning, notice, info, debug
     */
    public int $threshold = 4; // 4 = warning+

    /**
     * Date format for log entries.
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * Log handlers.
     */
    public array $handlers = [
        'CodeIgniter\Log\Handlers\FileHandler' => [
            'handles' => ['critical', 'alert', 'emergency', 'debug', 'error', 'info', 'notice', 'warning'],
            'path'    => WRITEPATH . 'logs/',
        ],
    ];
}
