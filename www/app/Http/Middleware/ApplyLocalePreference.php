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

    private const GLOBAL_ADMIN_LOCALE = 'pt_BR';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('global-admin') || $request->is('global-admin/*')) {
            return $this->handleGlobalAdminRequest($request, $next);
        }

        if ($this->isApiRequest($request)) {
            return $this->handleApiRequest($request, $next);
        }

        $locale = null;

        $queryLocale = $request->query('locale');
        if (is_string($queryLocale) && in_array($queryLocale, self::ALLOWED_LOCALES, true)) {
            $locale = $queryLocale;
        }

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if ($locale === null && is_string($sessionLocale) && in_array($sessionLocale, self::ALLOWED_LOCALES, true)) {
                $locale = $sessionLocale;
            }
        }

        $userLocale = $request->user()?->preferred_locale;
        if (is_string($userLocale) && in_array($userLocale, self::ALLOWED_LOCALES, true)) {
            $locale ??= $userLocale;
        }

        $cookieLocale = $request->cookie('locale');
        if ($locale === null && is_string($cookieLocale) && in_array($cookieLocale, self::ALLOWED_LOCALES, true)) {
            $locale = $cookieLocale;
        }

        if (! is_string($locale) || ! in_array($locale, self::ALLOWED_LOCALES, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);
        $resolvedLocale = $this->resolvedLocale($request, $locale);
        $response->headers->setCookie(cookie('locale', $resolvedLocale, 60 * 24 * 365));

        return $response;
    }

    private function handleGlobalAdminRequest(Request $request, Closure $next): Response
    {
        $locale = self::GLOBAL_ADMIN_LOCALE;

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);
        $response->headers->setCookie(cookie('locale', $locale, 60 * 24 * 365));

        return $response;
    }

    private function handleApiRequest(Request $request, Closure $next): Response
    {
        $locale = 'en';

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);
        $response->headers->setCookie(cookie('locale', $locale, 60 * 24 * 365));

        return $response;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api') || $request->is('api/*') || $request->expectsJson();
    }

    private function resolvedLocale(Request $request, string $fallback): string
    {
        $userLocale = $request->user()?->preferred_locale;
        if (is_string($userLocale) && in_array($userLocale, self::ALLOWED_LOCALES, true)) {
            return $userLocale;
        }

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if (is_string($sessionLocale) && in_array($sessionLocale, self::ALLOWED_LOCALES, true)) {
                return $sessionLocale;
            }
        }

        return $fallback;
    }
}
