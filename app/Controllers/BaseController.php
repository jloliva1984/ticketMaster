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
 * All application controllers extend this class.
 * Provides: auth session loading, view helpers, JSON response wrapper.
 *
 * @property IncomingRequest $request
 */
abstract class BaseController extends Controller
{
    /** Helpers loaded on every request */
    protected $helpers = ['url', 'form', 'text', 'language'];

    /** Current authenticated user (null if guest) */
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

        // Hydrate current user from session
        if (session()->get('logged_in')) {
            $this->currentUser = [
                'id'    => session()->get('user_id'),
                'name'  => session()->get('user_name'),
                'email' => session()->get('user_email'),
                'role'  => session()->get('user_role'),
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // View helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Merge base view data (currentUser, flash messages) with page data
     * and render the view.
     */
    protected function render(string $view, array $data = []): string
    {
        $base = [
            'currentUser' => $this->currentUser,
        ];

        return view($view, array_merge($base, $data));
    }

    // ─────────────────────────────────────────────────────────────
    // Response helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Return a JSON response — convenience wrapper for AJAX endpoints.
     */
    protected function jsonResponse(mixed $data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setBody(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Return a standard JSON success payload.
     */
    protected function jsonSuccess(string $message, mixed $data = null): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Return a standard JSON error payload.
     */
    protected function jsonError(string $message, int $status = 400, mixed $errors = null): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    // ─────────────────────────────────────────────────────────────
    // Role helpers
    // ─────────────────────────────────────────────────────────────

    protected function isAdmin(): bool
    {
        return ($this->currentUser['role'] ?? '') === 'admin';
    }

    protected function isLoggedIn(): bool
    {
        return $this->currentUser !== null;
    }
}
