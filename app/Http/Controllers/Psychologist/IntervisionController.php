<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\IntervisionGroup;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Services\CanConsultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IntervisionController extends Controller
{
    public function groups(Request $request): View
    {
        $user = $request->user();
        $service = new CanConsultService();

        $myGroupIds = IntervisionParticipant::query()
            ->where('psychologist_id', $user->id)
            ->where('is_active', true)
            ->pluck('group_id')
            ->all();

        $groups = IntervisionGroup::query()
            ->with('creator')
            ->withCount(['activeParticipants as participants_count'])
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (IntervisionGroup $group) use ($myGroupIds) {
                $group->is_member = in_array($group->id, $myGroupIds, true);
                $group->has_free_places = $group->participants_count < $group->max_participants;
                return $group;
            });

        $myUpcomingSessions = IntervisionSession::query()
            ->with('group')
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
            ->where('scheduled_at', '>=', now()->subMinutes(30))
            ->whereIn('group_id', $myGroupIds)
            ->orderBy('scheduled_at')
            ->limit(20)
            ->get();

        $attended = $service->attendedLast30Days($user);
        $required = $service->minSessions();
        $canConsultInfo = [
            'attended' => $attended,
            'required' => $required,
            'can_consult' => $attended >= $required,
            'remaining' => max(0, $required - $attended),
        ];

        return view('psychologist.intervisions.groups', compact('groups', 'myUpcomingSessions', 'canConsultInfo'));
    }

    public function joinGroup(Request $request, IntervisionGroup $group): RedirectResponse
    {
        $user = $request->user();

        if (!$group->is_active) {
            return back()->with('error', 'Группа неактивна');
        }

        $activeMembership = IntervisionParticipant::query()
            ->where('group_id', $group->id)
            ->where('psychologist_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if ($activeMembership) {
            return back()->with('error', 'Вы уже состоите в этой группе');
        }

        $activeCount = IntervisionParticipant::query()
            ->where('group_id', $group->id)
            ->where('is_active', true)
            ->count();

        if ($activeCount >= $group->max_participants) {
            return back()->with('error', 'В группе нет свободных мест');
        }

        // Проверяем конфликты по всем предстоящим сессиям группы.
        $upcomingGroupSessions = IntervisionSession::query()
            ->where('group_id', $group->id)
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
            ->where('scheduled_at', '>=', now()->subMinutes(30))
            ->orderBy('scheduled_at')
            ->get();

        foreach ($upcomingGroupSessions as $session) {
            $start = $session->scheduled_at->copy();
            $end = $session->scheduled_at->copy()->addMinutes($session->duration_minutes);

            if ($this->hasAnyTimeConflict($user->id, $start, $end, $group->id)) {
                return back()->with(
                    'error',
                    'Найден конфликт по времени: ' . $session->scheduled_at->format('d.m.Y H:i')
                );
            }
        }

        DB::transaction(function () use ($group, $user): void {
            $existing = IntervisionParticipant::query()
                ->where('group_id', $group->id)
                ->where('psychologist_id', $user->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'is_active' => true,
                    'left_at' => null,
                ]);
                $participantId = $existing->id;
            } else {
                $participant = IntervisionParticipant::create([
                    'group_id' => $group->id,
                    'psychologist_id' => $user->id,
                    'is_active' => true,
                    'left_at' => null,
                ]);
                $participantId = $participant->id;
            }

            // Добавляем attendance для будущих сессий группы, где записи еще нет.
            $futureSessionIds = IntervisionSession::query()
                ->where('group_id', $group->id)
                ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
                ->where('scheduled_at', '>=', now()->subMinutes(30))
                ->pluck('id');

            $existingAttendanceSessionIds = DB::table('intervision_attendance')
                ->where('participant_id', $participantId)
                ->whereIn('session_id', $futureSessionIds)
                ->pluck('session_id')
                ->all();

            $missingSessionIds = $futureSessionIds
                ->reject(fn($id) => in_array($id, $existingAttendanceSessionIds, true))
                ->values();

            if ($missingSessionIds->isNotEmpty()) {
                $rows = $missingSessionIds->map(fn($sessionId) => [
                    'session_id' => $sessionId,
                    'participant_id' => $participantId,
                    'attended' => false,
                    'marked_at' => null,
                    'marked_by' => null,
                    'notes' => null,
                ])->all();

                DB::table('intervision_attendance')->insert($rows);
            }
        });

        return back()->with('success', 'Вы вступили в группу интервизий');
    }

    public function leaveGroup(Request $request, IntervisionGroup $group): RedirectResponse
    {
        $user = $request->user();

        $participant = IntervisionParticipant::query()
            ->where('group_id', $group->id)
            ->where('psychologist_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$participant) {
            return back()->with('error', 'Вы не состоите в этой группе');
        }

        $participant->update([
            'is_active' => false,
            'left_at' => now(),
        ]);

        return back()->with('success', 'Вы вышли из группы');
    }

    private function hasAnyTimeConflict(
        int $userId,
        \Carbon\Carbon $startsAt,
        \Carbon\Carbon $endsAt,
        int $joiningGroupId
    ): bool {
        $slotEndExpression = $this->dateAddMinutesExpression('sl.starts_at', 't.duration_minutes');
        $sessionEndExpression = $this->dateAddMinutesExpression('s.scheduled_at', 's.duration_minutes');

        $triadConflict = DB::table('slot_participants as sp')
            ->join('slots as sl', 'sl.id', '=', 'sp.slot_id')
            ->join('tasks as t', 't.id', '=', 'sl.task_id')
            ->where('sp.user_id', $userId)
            ->where('sp.status', 'active')
            ->whereIn('sl.status', ['open', 'full', 'in_progress'])
            ->whereRaw('sl.starts_at < ?', [$endsAt])
            ->whereRaw("{$slotEndExpression} > ?", [$startsAt])
            ->exists();

        if ($triadConflict) {
            return true;
        }

        return DB::table('intervision_participants as ip')
            ->join('intervision_sessions as s', 's.group_id', '=', 'ip.group_id')
            ->where('ip.psychologist_id', $userId)
            ->where('ip.is_active', true)
            ->where('ip.group_id', '!=', $joiningGroupId)
            ->whereIn('s.status', ['SCHEDULED', 'IN_PROGRESS'])
            ->whereRaw('s.scheduled_at < ?', [$endsAt])
            ->whereRaw("{$sessionEndExpression} > ?", [$startsAt])
            ->exists();
    }

    private function dateAddMinutesExpression(string $datetimeColumn, string $minutesColumn): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "datetime({$datetimeColumn}, '+' || {$minutesColumn} || ' minutes')",
            'pgsql' => "({$datetimeColumn} + ({$minutesColumn} || ' minutes')::interval)",
            default => "DATE_ADD({$datetimeColumn}, INTERVAL {$minutesColumn} MINUTE)",
        };
    }
}