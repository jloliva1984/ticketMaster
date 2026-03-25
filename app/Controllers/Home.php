<?php

namespace App\Controllers;

/**
 * Home Controller
 * Temporary landing page — will be replaced by auth redirect in Phase 3.
 */
class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}
