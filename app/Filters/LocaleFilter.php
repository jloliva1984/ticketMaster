<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * LocaleFilter
 *
 * Sets the application locale from the session on every request.
 * Runs as a 'before' global filter.
 */
class LocaleFilter implements FilterInterface
{
    /**
     * Restore locale from session, defaulting to 'en'.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $locale  = $session->get('locale') ?? 'en';

        // Validate against supported locales to prevent injection
        $supported = config('App')->supportedLocales ?? ['en', 'es'];
        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        $request->setLocale($locale);

        return null; // continue request processing
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
