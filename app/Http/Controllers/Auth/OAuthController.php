<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PsychologistProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['vkontakte', 'yandex'];

    /** Редирект к провайдеру */
    public function redirect(string $provider)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /** Обработка callback от провайдера */
    public function callback(string $provider, Request $request)
    {
        if (!in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Ошибка авторизации через ' . $this->providerLabel($provider));
        }

        // Найти существующий аккаунт по provider+provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            // Аккаунт найден — обновляем токен и логиним
            $user->update(['provider_token' => $socialUser->token]);
            Auth::login($user);
            return redirect()->intended(route('dashboard'));
        }

        // Попытка найти по email (если провайдер вернул email)
        if ($socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
            if ($user) {
                // Email совпал — привязываем провайдера
                $user->update([
                    'provider'       => $provider,
                    'provider_id'    => $socialUser->getId(),
                    'provider_token' => $socialUser->token,
                ]);
                Auth::login($user);
                return redirect()->intended(route('dashboard'));
            }
        }

        // Новый пользователь — сохраняем данные OAuth в сессию, отправляем на форму диплома
        $request->session()->put('oauth_data', [
            'provider'    => $provider,
            'provider_id' => $socialUser->getId(),
            'token'       => $socialUser->token,
            'name'        => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Психолог',
            'email'       => $socialUser->getEmail(),
            'avatar'      => $socialUser->getAvatar(),
        ]);

        return redirect()->route('oauth.diploma.form');
    }

    /** Форма загрузки диплома после OAuth */
    public function diplomaForm(Request $request)
    {
        if (!$request->session()->has('oauth_data')) {
            return redirect()->route('login');
        }

        $oauthData = $request->session()->get('oauth_data');
        return view('auth.oauth-diploma', compact('oauthData'));
    }

    /** Сохранение диплома и создание аккаунта после OAuth */
    public function diplomaStore(Request $request)
    {
        if (!$request->session()->has('oauth_data')) {
            return redirect()->route('login');
        }

        $request->validate([
            'diploma_scan'        => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'diploma_number'      => ['required', 'string', 'max:100'],
            'diploma_year'        => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'diploma_institution' => ['required', 'string', 'max:255'],
            'gender'              => ['required', 'in:male,female,not_specified'],
        ], [
            'diploma_scan.required' => 'Загрузите скан диплома',
            'diploma_scan.mimes'    => 'Допустимые форматы: jpg, png, pdf',
            'diploma_scan.max'      => 'Максимальный размер файла — 10 МБ',
        ]);

        $oauthData = $request->session()->get('oauth_data');

        // Проверка уникальности provider_id (на случай двойной отправки)
        $exists = User::where('provider', $oauthData['provider'])
            ->where('provider_id', $oauthData['provider_id'])
            ->exists();

        if ($exists) {
            $request->session()->forget('oauth_data');
            return redirect()->route('login')->with('error', 'Аккаунт уже существует. Войдите через ' . $this->providerLabel($oauthData['provider']));
        }

        // Email из OAuth может быть пустым — генерируем placeholder
        $email = $oauthData['email'] ?? ($oauthData['provider_id'] . '@' . $oauthData['provider'] . '.oauth');

        $user = User::create([
            'name'       => $oauthData['name'],
            'email'      => $email,
            'password'   => null,
            'role'       => 'PSYCHOLOGIST',
            'status'     => 'pending_verification',
            'gender'     => $request->input('gender'),
            'provider'   => $oauthData['provider'],
            'provider_id' => $oauthData['provider_id'],
            'provider_token' => $oauthData['token'],
        ]);

        $path = $request->file('diploma_scan')->store("diplomas/{$user->id}", 'local');

        PsychologistProfile::create([
            'user_id'             => $user->id,
            'diploma_scan_url'    => $path,
            'diploma_number'      => $request->input('diploma_number'),
            'diploma_year'        => $request->input('diploma_year'),
            'diploma_institution' => $request->input('diploma_institution'),
            'diploma_verified'    => false,
            'can_consult'         => false,
        ]);

        $request->session()->forget('oauth_data');

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Аккаунт создан. Ваш диплом отправлен на проверку.');
    }

    private function providerLabel(string $provider): string
    {
        return match ($provider) {
            'vkontakte' => 'ВКонтакте',
            'yandex'    => 'Яндекс',
            default     => $provider,
        };
    }
}
