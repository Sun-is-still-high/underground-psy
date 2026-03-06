<?php

namespace App\Services;

use App\Models\PsychologistProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CanConsultService
{
    /**
     * Пересчитывает can_consult для психолога и сохраняет в БД.
     */
    public function recalculate(User $user): void
    {
        if (!$user->isPsychologist()) {
            return;
        }

        $profile = $user->psychologistProfile;
        if (!$profile) {
            return;
        }

        $minSessions = (int) DB::table('intervision_settings')
            ->where('setting_key', 'min_sessions')
            ->value('setting_value') ?: 3;

        $attended = DB::table('intervision_attendance as ia')
            ->join('intervision_participants as ip', 'ia.participant_id', '=', 'ip.id')
            ->join('intervision_sessions as s', 'ia.session_id', '=', 's.id')
            ->where('ip.psychologist_id', $user->id)
            ->where('ia.attended', true)
            ->where('s.status', 'COMPLETED')
            ->where('s.scheduled_at', '>=', now()->subDays(30))
            ->count();

        $canConsult = $attended >= $minSessions;

        if ($profile->can_consult !== $canConsult) {
            $profile->update(['can_consult' => $canConsult]);
        }
    }

    /**
     * Считает посещённые интервизии за последние 30 дней (для дашборда).
     */
    public function attendedLast30Days(User $user): int
    {
        return DB::table('intervision_attendance as ia')
            ->join('intervision_participants as ip', 'ia.participant_id', '=', 'ip.id')
            ->join('intervision_sessions as s', 'ia.session_id', '=', 's.id')
            ->where('ip.psychologist_id', $user->id)
            ->where('ia.attended', true)
            ->where('s.status', 'COMPLETED')
            ->where('s.scheduled_at', '>=', now()->subDays(30))
            ->count();
    }

    /**
     * Читает минимум интервизий из настроек.
     */
    public function minSessions(): int
    {
        return (int) DB::table('intervision_settings')
            ->where('setting_key', 'min_sessions')
            ->value('setting_value') ?: 3;
    }
}
