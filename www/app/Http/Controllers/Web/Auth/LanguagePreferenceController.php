<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

final class LanguagePreferenceController extends Controller
{
    private const ALLOWED_LOCALES = ['pt_BR', 'en', 'es'];

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_locale' => ['required', 'string', Rule::in(self::ALLOWED_LOCALES)],
        ]);

        $locale = (string) $validated['preferred_locale'];

        $user = $request->user();
        if ($user !== null) {
            $user->forceFill([
                'preferred_locale' => $locale,
            ])->save();
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        return back()
            ->with('status', __('messages.language_updated'));
    }
}
