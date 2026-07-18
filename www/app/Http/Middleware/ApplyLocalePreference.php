<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class ApplyLocalePreference
{
    private const ALLOWED_LOCALES = ['pt_BR', 'en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        $queryLocale = $request->query('locale');
        if (is_string($queryLocale) && in_array($queryLocale, self::ALLOWED_LOCALES, true)) {
            $locale = $queryLocale;
        }

        $cookieLocale = $request->cookie('locale');
        if ($locale === null && is_string($cookieLocale) && in_array($cookieLocale, self::ALLOWED_LOCALES, true)) {
            $locale = $cookieLocale;
        }

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if ($locale === null && is_string($sessionLocale) && in_array($sessionLocale, self::ALLOWED_LOCALES, true)) {
                $locale = $sessionLocale;
            }
        }

        $userLocale = $request->user()?->preferred_locale;
        if (is_string($userLocale) && in_array($userLocale, self::ALLOWED_LOCALES, true)) {
            $locale = $userLocale;
        }

        if (! is_string($locale) || ! in_array($locale, self::ALLOWED_LOCALES, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);
        $response->headers->setCookie(cookie('locale', $locale, 60 * 24 * 365));

        return $response;
    }
}
