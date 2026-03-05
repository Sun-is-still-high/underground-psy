<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntervisionAttendance;
use App\Models\IntervisionGroup;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntervisionController extends Controller
{
    // ==================== ГРУППЫ ====================

    public function groups(): View
    {
        $groups = IntervisionGroup::withCount('activeParticipants')->where('is_active', true)->get();
        return view('admin.intervision.groups.index', compact('groups'));
    }

    public function createGroup(): View
    {
        return view('admin.intervision.groups.create');
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_participants' => ['required', 'integer', 'min:2', 'max:50'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $group = IntervisionGroup::create($validated);

        return redirect()->route('admin.intervision.groups.show', $group->id)
            ->with('success', 'Группа успешно создана');
    }

    public function showGroup(int $id): View|RedirectResponse
    {
        $group = IntervisionGroup::with(['activeParticipants.psychologist'])->find($id);

        if (!$group) {
            return redirect()->route('admin.intervision.groups')->with('error', 'Группа не найдена');
        }

        $sessions = IntervisionSession::where('group_id', $id)->orderBy('scheduled_at', 'desc')->get();
        $availablePsychologists = User::where('role', 'PSYCHOLOGIST')
            ->whereDoesntHave('intervisionParticipations', fn($q) => $q->where('group_id', $id)->where('is_active', true))
            ->orderBy('name')
            ->get();

        return view('admin.intervision.groups.show', compact('group', 'sessions', 'availablePsychologists'));
    }

    public function editGroup(int $id): View|RedirectResponse
    {
        $group = IntervisionGroup::find($id);
        if (!$group) {
            return redirect()->route('admin.intervision.groups')->with('error', 'Группа не найдена');
        }
        return view('admin.intervision.groups.edit', compact('group'));
    }

    public function updateGroup(Request $request, int $id): RedirectResponse
    {
        $group = IntervisionGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_participants' => ['required', 'integer', 'min:2', 'max:50'],
        ]);

        $group->update($validated);

        return redirect()->route('admin.intervision.groups.show', $id)->with('success', 'Группа обновлена');
    }

    public function deleteGroup(int $id): RedirectResponse
    {
        IntervisionGroup::where('id', $id)->update(['is_active' => false]);
        return redirect()->route('admin.intervision.groups')->with('success', 'Группа деактивирована');
    }

    // ==================== УЧАСТНИКИ ====================

    public function addParticipant(Request $request, int $groupId): RedirectResponse
    {
        $group = IntervisionGroup::findOrFail($groupId);

        $request->validate(['psychologist_id' => ['required', 'exists:users,id']]);

        $psychologist = User::findOrFail($request->integer('psychologist_id'));
        if ($psychologist->role !== 'PSYCHOLOGIST') {
            return back()->with('error', 'Психолог не найден');
        }

        $activeCount = IntervisionParticipant::where('group_id', $groupId)->where('is_active', true)->count();
        if ($activeCount >= $group->max_participants) {
            return back()->with('error', 'В группе нет свободных мест');
        }

        $exists = IntervisionParticipant::where('group_id', $groupId)
            ->where('psychologist_id', $psychologist->id)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Психолог уже состоит в этой группе');
        }

        IntervisionParticipant::create([
            'group_id' => $groupId,
            'psychologist_id' => $psychologist->id,
        ]);

        return redirect()->route('admin.intervision.groups.show', $groupId)
            ->with('success', 'Психолог добавлен в группу');
    }

    public function removeParticipant(int $groupId, int $psychologistId): RedirectResponse
    {
        IntervisionParticipant::where('group_id', $groupId)
            ->where('psychologist_id', $psychologistId)
            ->update(['is_active' => false, 'left_at' => now()]);

        return redirect()->route('admin.intervision.groups.show', $groupId)
            ->with('success', 'Психолог удалён из группы');
    }

    // ==================== СЕССИИ ====================

    public function createSession(int $groupId): View|RedirectResponse
    {
        $group = IntervisionGroup::find($groupId);
        if (!$group) {
            return redirect()->route('admin.intervision.groups')->with('error', 'Группа не найдена');
        }
        return view('admin.intervision.sessions.create', compact('group'));
    }

    public function storeSession(Request $request, int $groupId): RedirectResponse
    {
        $group = IntervisionGroup::findOrFail($groupId);

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:480'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
        ]);

        $validated['group_id'] = $groupId;
        $session = IntervisionSession::create($validated);

        $participants = IntervisionParticipant::where('group_id', $groupId)->where('is_active', true)->get();
        foreach ($participants as $participant) {
            IntervisionAttendance::create([
                'session_id' => $session->id,
                'participant_id' => $participant->id,
                'attended' => false,
            ]);
        }

        return redirect()->route('admin.intervision.sessions.show', $session->id)
            ->with('success', 'Сессия создана');
    }

    public function showSession(int $id): View|RedirectResponse
    {
        $session = IntervisionSession::with(['group', 'attendance.participant.psychologist'])->find($id);
        if (!$session) {
            return redirect()->route('admin.intervision.groups')->with('error', 'Сессия не найдена');
        }
        return view('admin.intervision.sessions.show', compact('session'));
    }

    public function changeSessionStatus(Request $request, int $id): RedirectResponse
    {
        $session = IntervisionSession::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:SCHEDULED,IN_PROGRESS,COMPLETED,CANCELLED'],
            'cancelled_reason' => ['nullable', 'string'],
        ]);

        $session->update([
            'status' => $request->input('status'),
            'cancelled_reason' => $request->input('cancelled_reason'),
        ]);

        return redirect()->route('admin.intervision.sessions.show', $id)->with('success', 'Статус обновлён');
    }

    public function attendanceForm(int $id): View|RedirectResponse
    {
        $session = IntervisionSession::with(['attendance.participant.psychologist'])->find($id);
        if (!$session) {
            return redirect()->route('admin.intervision.groups')->with('error', 'Сессия не найдена');
        }
        return view('admin.intervision.sessions.attendance', compact('session'));
    }

    public function saveAttendance(Request $request, int $id): RedirectResponse
    {
        $session = IntervisionSession::findOrFail($id);
        $attended = $request->input('attended', []);

        foreach ($session->attendance as $record) {
            $record->update([
                'attended' => in_array($record->participant_id, array_map('intval', $attended)),
                'marked_at' => now(),
                'marked_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('admin.intervision.sessions.show', $id)->with('success', 'Посещаемость сохранена');
    }
}
