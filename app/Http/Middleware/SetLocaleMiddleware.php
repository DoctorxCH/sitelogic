<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Language;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Check for default language in database
            try {
                $defaultLanguage = Language::where('is_default', true)->first();
                if ($defaultLanguage) {
                    App::setLocale($defaultLanguage->code);
                    Session::put('locale', $defaultLanguage->code);
                } else {
                    App::setLocale(config('app.locale'));
                }
            } catch (\Exception $e) {
                // Table might not exist yet
                App::setLocale(config('app.locale'));
            }
        }

        return $next($request);
    }
}
