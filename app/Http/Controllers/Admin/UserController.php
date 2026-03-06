<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function block(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'is_blocked'     => true,
            'blocked_reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Пользователь {$user->name} заблокирован.");
    }

    public function unblock(User $user): RedirectResponse
    {
        $user->update([
            'is_blocked'     => false,
            'blocked_reason' => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Пользователь {$user->name} разблокирован.");
    }

    public function makeModerator(User $user): RedirectResponse
    {
        abort_unless($user->isPsychologist(), 422, 'Модератором может стать только психолог.');

        $user->update(['role' => 'MODERATOR']);

        return redirect()->route('admin.users.index')
            ->with('success', "Пользователь {$user->name} назначен модератором.");
    }

    public function removeModerator(User $user): RedirectResponse
    {
        abort_unless($user->isModerator(), 422, 'Пользователь не является модератором.');

        $user->update(['role' => 'PSYCHOLOGIST']);

        return redirect()->route('admin.users.index')
            ->with('success', "Роль модератора снята с {$user->name}.");
    }
}
