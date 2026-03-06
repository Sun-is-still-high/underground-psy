<?php

namespace App\Http\Controllers;

use App\Models\IntervisionAttendance;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Services\CanConsultService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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
            $canConsultInfo = [
                'attended'    => $service->attendedLast30Days($user),
                'required'    => $service->minSessions(),
                'can_consult' => $profile?->can_consult ?? false,
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
        $participant = IntervisionParticipant::where('psychologist_id', $userId)
            ->where('is_active', true)
            ->with('group.sessions')
            ->first();

        if (!$participant) {
            return ['in_group' => false, 'group' => null, 'monthly_sessions' => 0, 'required_sessions' => 2, 'can_consult' => false];
        }

        $minSessions = (int) DB::table('intervision_settings')
            ->where('setting_key', 'min_monthly_sessions')
            ->value('setting_value') ?? 2;

        $monthStart = now()->startOfMonth();
        $attended = IntervisionAttendance::whereHas('session', function ($q) use ($participant, $monthStart) {
            $q->where('group_id', $participant->group_id)
              ->where('scheduled_at', '>=', $monthStart)
              ->where('status', 'COMPLETED');
        })->where('participant_id', $participant->id)
          ->where('attended', true)
          ->count();

        return [
            'in_group' => true,
            'group' => $participant->group,
            'monthly_sessions' => $attended,
            'required_sessions' => $minSessions,
            'can_consult' => $attended >= $minSessions,
        ];
    }
}
