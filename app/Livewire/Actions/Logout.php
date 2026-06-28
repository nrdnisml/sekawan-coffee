<?php

namespace App\Livewire\Actions;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(AuthService $authService): RedirectResponse
    {
        $authService->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
