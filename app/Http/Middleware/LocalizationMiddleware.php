<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available languages
        $availableLanguages = ['en', 'ar'];

        // Check if locale is provided in URL
        if ($request->has('locale') && in_array($request->get('locale'), $availableLanguages)) {
            $locale = $request->get('locale');
            Session::put('locale', $locale);
        }
        // Check if locale is stored in session
        elseif (Session::has('locale') && in_array(Session::get('locale'), $availableLanguages)) {
            $locale = Session::get('locale');
        }
        // Use default locale
        else {
            $locale = config('app.locale', 'en');
        }

        // Set the application locale
        App::setLocale($locale);

        // Process the request
        $response = $next($request);

        return $response;
    }
}
