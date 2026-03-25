<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter
 *
 * Verifies the user is logged in before any protected route.
 * On failure: saves the requested URL to flash and redirects to login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('logged_in')) {
            // Save the originally requested URL so we can redirect after login
            session()->setFlashdata('redirect_url', current_url());

            return redirect()->to(base_url('auth/login'))
                             ->with('error', 'Please log in to continue.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
