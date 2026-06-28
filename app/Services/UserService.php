<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected AuditService $auditService) {}

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        $this->ensureIsAdmin();

        $password = $data['password'] ?? throw new Exception('Kata sandi wajib diisi.');

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => filled($data['email'] ?? null) ? $data['email'] : null,
            'password' => Hash::make($password),
            'role' => $data['role'] ?? 'cashier',
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditService->log(Auth::id(), 'create', 'users', $user->id, "Menambahkan pengguna: {$user->username}");

        return $user;
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $id, array $data): User
    {
        $this->ensureIsAdmin();

        $user = User::findOrFail($id);

        if (! filled($data['email'] ?? null)) {
            $data['email'] = null;
        }

        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        $this->auditService->log(Auth::id(), 'update', 'users', $user->id, "Memperbarui pengguna: {$user->username}");

        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): void
    {
        $this->ensureIsAdmin();

        if (Auth::id() === $id) {
            throw new Exception('Anda tidak dapat menghapus akun sendiri.');
        }

        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        $this->auditService->log(Auth::id(), 'delete', 'users', $id, "Menghapus pengguna: {$username}");
    }

    /**
     * Ensure current user is an admin.
     */
    protected function ensureIsAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            throw new Exception('Tidak diizinkan. Hanya admin yang dapat mengelola pengguna.');
        }
    }
}
