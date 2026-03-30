<?php

namespace App\Http\Controllers;

use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Models\User;
use App\Services\CanConsultService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profileWarning = null;
        $canConsultInfo = null;

        if ($user->isPsychologist() && $user->isActive()) {
            $profile = $user->psychologistProfile;
            if (!$profile || !$profile->is_published) {
                $profileWarning = 'Ваш профиль не опубликован. Заполните его, чтобы клиенты могли вас найти!';
            }

            $service = new CanConsultService();
            $attended = $service->attendedLast30Days($user);
            $required = $service->minSessions();
            $canConsultInfo = [
                'attended' => $attended,
                'required' => $required,
                'can_consult' => $attended >= $required,
            ];
        }

        return view('dashboard', compact('user', 'profileWarning', 'canConsultInfo'));
    }

    public function intervisionStatus(Request $request): View
    {
        $user = $request->user();
        $status = $this->getPsychologistIntervisionStatus($user->id);

        return view('psychologist.intervision-status', compact('user', 'status'));
    }

    private function getPsychologistIntervisionStatus(int $userId): array
    {
        $user = User::with('psychologistProfile')->findOrFail($userId);
        $service = new CanConsultService();

        $participant = IntervisionParticipant::where('psychologist_id', $userId)
            ->where('is_active', true)
            ->with('group')
            ->first();

        $attended = $service->attendedLast30Days($user);
        $required = $service->minSessions();
        $canConsult = $attended >= $required;

        if (!$participant) {
            return [
                'in_group' => false,
                'group' => null,
                // Оставляем ключ для совместимости с текущим шаблоном.
                'monthly_sessions' => $attended,
                'required_sessions' => $required,
                'can_consult' => $canConsult,
                'upcoming_sessions' => collect(),
            ];
        }

        $upcomingSessions = IntervisionSession::query()
            ->where('group_id', $participant->group_id)
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
            ->where('scheduled_at', '>=', now()->subMinutes(30))
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        return [
            'in_group' => true,
            'group' => $participant->group,
            // Оставляем ключ для совместимости с текущим шаблоном.
            'monthly_sessions' => $attended,
            'required_sessions' => $required,
            'can_consult' => $canConsult,
            'upcoming_sessions' => $upcomingSessions,
        ];
    }
}