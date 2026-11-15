<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Akun Anda tidak aktif atau tidak memiliki izin akses.');
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Peran akun Anda (' . $user->role . ') tidak diizinkan melakukan tindakan ini.');
        }

        return $next($request);
    }
}