<?php

namespace App\Controllers;

/**
 * Language Switcher Controller
 *
 * Route:  GET /lang/{locale}
 * Stores chosen locale in session and redirects back.
 */
class Language extends BaseController
{
    public function switch(string $locale): \CodeIgniter\HTTP\RedirectResponse
    {
        $supported = config('App')->supportedLocales ?? ['en', 'es'];

        if (in_array($locale, $supported, true)) {
            session()->set('locale', $locale);
        }

        // Redirect back to the referring page, or home
        $referer = $this->request->getHeaderLine('Referer');
        $baseUrl = base_url();

        // Only redirect to same-origin URLs
        if ($referer && str_starts_with($referer, $baseUrl)) {
            return redirect()->to($referer);
        }

        return redirect()->to(base_url('/'));
    }
}
