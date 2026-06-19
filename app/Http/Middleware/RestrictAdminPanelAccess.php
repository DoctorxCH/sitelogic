<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->hasRole('super_admin') || $user->hasRole('manager'))) {
            return $next($request);
        }

        Auth::logout();
        return redirect()->route('login')->with('error', 'Zugriff verweigert für Techniker.');
    }
}
