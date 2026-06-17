<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(protected AuditService $auditService)
    {
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        $this->ensureIsAdmin();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'cashier',
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditService->log(Auth::id(), 'create', 'users', $user->id, "Created user: {$user->username}");

        return $user;
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $id, array $data): User
    {
        $this->ensureIsAdmin();

        $user = User::findOrFail($id);
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        $this->auditService->log(Auth::id(), 'update', 'users', $user->id, "Updated user: {$user->username}");

        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): void
    {
        $this->ensureIsAdmin();

        if (Auth::id() === $id) {
            throw new Exception("Cannot delete your own account.");
        }

        $user = User::findOrFail($id);
        $username = $user->username;
        $user->delete();

        $this->auditService->log(Auth::id(), 'delete', 'users', $id, "Deleted user: {$username}");
    }

    /**
     * Ensure current user is an admin.
     */
    protected function ensureIsAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            throw new Exception("Unauthorized. Only admins can manage users.");
        }
    }
}
