<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('locales.supported', [])), 404);
        $request->session()->put('locale', $locale);
        $response = back()->with('locale_changed', __('ui.language_changed'));

        if (config("locales.supported.{$locale}.native")) {
            return $response->withoutCookie('googtrans');
        }

        return $response->withCookie(cookie(
            'googtrans',
            "/en/{$locale}",
            525600,
            '/',
            null,
            request()->isSecure(),
            false,
            false,
            'Lax'
        ));
    }
}
