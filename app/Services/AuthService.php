<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(protected AuditService $auditService) {}

    /**
     * Authenticate a user.
     */
    public function login(string $username, string $password): bool
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new Exception('Username atau kata sandi tidak valid.');
        }

        if (! $user->is_active) {
            throw new Exception('Akun sedang tidak aktif.');
        }

        Auth::login($user);

        $this->auditService->log($user->id, 'login', 'users', $user->id, 'Pengguna masuk melalui AuthService');

        return true;
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->auditService->log($user->id, 'logout', 'users', $user->id, 'Pengguna keluar dari aplikasi');
            Auth::logout();
        }
    }
}
