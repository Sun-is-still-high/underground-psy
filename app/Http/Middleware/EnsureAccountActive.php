<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Блокирует доступ психологам со статусом pending_verification.
     * Клиентам и администраторам - пропускает всегда.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isPsychologist() && $user->isPendingVerification()) {
            // Разрешаем только dashboard, logout, email-верификацию и повторную загрузку диплома.
            $allowedRoutes = [
                'dashboard',
                'logout',
                'verification.notice',
                'verification.verify',
                'verification.send',
                'psychologist.diploma.reupload',
                'settings.index',
                'settings.timezone',
                'settings.gender',
            ];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Ваш аккаунт ожидает проверки диплома. Доступ к платформе будет открыт после одобрения.');
            }
        }

        return $next($request);
    }
}