<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $menuSlug): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        // Super admin bisa akses semua menu
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check jika user punya akses ke menu
        if ($user->canAccessMenu($menuSlug)) {
            return $next($request);
        }

        // Tidak punya akses
        // Tidak punya akses
        abort(403, 'Anda tidak memiliki akses ke halaman ini. (Role: ' . $user->role . ', ID: ' . $user->id . ', Menu: ' . $menuSlug . ')');
    }
}
