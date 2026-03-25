<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminFilter
 *
 * Restricts access to admin-only routes.
 * Assumes AuthFilter has already verified the user is logged in.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Must be logged in first
        if (! session()->get('logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }

        // Must have admin role
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to(base_url('/'))
                             ->with('error', 'Access denied. Administrator privileges required.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
