<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * After filter that injects the current CSRF token into every response header
 * so the JS layer can keep its meta tag in sync when regenerate=true.
 */
class CsrfTokenFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $security = service('security');
        $response->setHeader('X-CSRF-TOKEN', $security->getHash());

        return $response;
    }
}
