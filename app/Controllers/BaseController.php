<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * All controllers extend this class.
 * Do NOT add any business logic here — only shared infrastructure:
 * helpers, services, shared properties.
 *
 * @property IncomingRequest $request
 */
abstract class BaseController extends Controller
{
    /**
     * Helpers loaded for every request.
     */
    protected $helpers = ['url', 'form', 'text'];

    /**
     * Current logged-in user (set by AuthFilter in Phase 3).
     */
    protected ?array $currentUser = null;

    /**
     * @param CLIRequest|IncomingRequest $request
     */
    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Load the current user from session (Phase 3 will populate this).
        // $this->currentUser = session()->get('user') ?? null;
    }

    /**
     * Return a JSON response — convenience wrapper for API/AJAX calls.
     */
    protected function jsonResponse(mixed $data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
