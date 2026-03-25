<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * AuthController
 *
 * Handles login, logout, and session management.
 */
class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ─────────────────────────────────────────────────────────────
    // GET /auth/login
    // ─────────────────────────────────────────────────────────────
    public function login(): RedirectResponse|string
    {
        // Already logged in → go to dashboard
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('/'));
        }

        return view('auth/login', [
            'pageTitle' => lang('General.login_title'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /auth/login
    // ─────────────────────────────────────────────────────────────
    public function loginProcess(): RedirectResponse
    {
        // Validate input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');

        // Find user
        $user = $this->userModel->findActiveByEmail($email);

        if (! $user || ! $this->userModel->verifyPassword($password, $user['password'])) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Invalid email or password.');
        }

        // Build session payload
        $this->createUserSession($user);

        // Optional: extend session if "remember me" checked
        if ($remember) {
            ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 days
        }

        // Update last login timestamp
        $this->userModel->touchLastLogin($user['id']);

        // Redirect to originally requested URL or dashboard
        $redirectTo = session()->getFlashdata('redirect_url') ?? base_url('/');

        return redirect()->to($redirectTo)->with('success', 'Welcome, ' . $user['name'] . '!');
    }

    // ─────────────────────────────────────────────────────────────
    // GET /auth/logout
    // ─────────────────────────────────────────────────────────────
    public function logout(): RedirectResponse
    {
        // Destroy session data
        session()->remove([
            'logged_in',
            'user_id',
            'user_name',
            'user_email',
            'user_role',
        ]);
        session()->destroy();

        return redirect()->to(base_url('auth/login'))
                         ->with('success', 'You have been logged out.');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Persist user data to session.
     */
    private function createUserSession(array $user): void
    {
        session()->set([
            'logged_in'  => true,
            'user_id'    => (int) $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'user_role'  => $user['role'],
        ]);

        // Regenerate session ID to prevent fixation
        session()->regenerate(false);
    }
}
