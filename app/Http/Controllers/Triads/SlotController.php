<?php

namespace App\Http\Controllers\Triads;

use App\Enums\ParticipantRole;
use App\Enums\SlotStatus;
use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\SlotParticipant;
use App\Models\Task;
use App\Models\TriadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlotController extends Controller
{
    /** Лента публичных слотов */
    public function index()
    {
        return view('triads.slots.index');
    }

    /** Форма создания слота */
    public function create()
    {
        $tasks = Task::approved()->orderBy('title')->get();
        return view('triads.slots.create', compact('tasks'));
    }

    /** Создать слот */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id'    => 'required|exists:tasks,id',
            'starts_at'  => 'required|date|after:now',
            'role'       => 'required|in:therapist,client,observer',
            'visibility' => 'required|in:public,private',
            'blind_mode' => 'boolean',
        ], [
            'task_id.required'   => 'Выберите задание',
            'task_id.exists'     => 'Задание не найдено',
            'starts_at.required' => 'Укажите дату и время',
            'starts_at.after'    => 'Дата должна быть в будущем',
            'role.required'      => 'Выберите свою роль',
        ]);

        $task     = Task::findOrFail($validated['task_id']);
        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($task->duration_minutes);

        // Проверка конфликта по времени
        if ($this->hasTimeConflict(Auth::id(), $startsAt, $endsAt)) {
            return back()->withErrors(['starts_at' => 'В это время у вас уже есть другой слот'])->withInput();
        }

        DB::transaction(function () use ($validated, $startsAt, $task) {
            $slot = Slot::create([
                'creator_id' => Auth::id(),
                'task_id'    => $validated['task_id'],
                'starts_at'  => $startsAt,
                'visibility' => $validated['visibility'],
                'blind_mode' => $validated['blind_mode'] ?? false,
                'status'     => SlotStatus::Open,
            ]);

            SlotParticipant::create([
                'slot_id'       => $slot->id,
                'user_id'       => Auth::id(),
                'role'          => $validated['role'],
                'original_role' => $validated['role'],
                'source'        => 'signup',
                'status'        => 'active',
            ]);
        });

        return redirect()->route('triads.slots.index')
            ->with('success', 'Слот создан');
    }

    /** Страница слота */
    public function show(Slot $slot)
    {
        $slot->load(['task', 'creator', 'activeParticipants.user']);

        $myParticipation = $slot->activeParticipants
            ->firstWhere('user_id', Auth::id());

        return view('triads.slots.show', compact('slot', 'myParticipation'));
    }

    /** Мои тройки */
    public function mySlots()
    {
        $user = Auth::user();

        $participating = SlotParticipant::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['slot.task', 'slot.activeParticipants'])
            ->get()
            ->pluck('slot')
            ->filter()
            ->sortByDesc('starts_at');

        $created = $user->createdSlots()
            ->with(['task', 'activeParticipants'])
            ->orderByDesc('starts_at')
            ->get();

        return view('triads.slots.my', compact('participating', 'created'));
    }

    /** Записаться на слот */
    public function join(Request $request, Slot $slot)
    {
        $request->validate([
            'role' => 'required|in:therapist,client,observer',
        ]);

        $role = $request->role;

        // Слот должен быть открыт
        if ($slot->status !== SlotStatus::Open) {
            return back()->with('error', 'Запись на этот слот закрыта');
        }

        // Дедлайн — за 1 час до начала
        if (now()->gte($slot->starts_at->subHour())) {
            return back()->with('error', 'Запись закрыта (менее 1 часа до начала)');
        }

        $task   = $slot->task;
        $endsAt = $slot->starts_at->copy()->addMinutes($task->duration_minutes);

        // Конфликт по времени
        if ($this->hasTimeConflict(Auth::id(), $slot->starts_at, $endsAt, $slot->id)) {
            return back()->with('error', 'В это время у вас уже есть другой слот');
        }

        try {
            DB::transaction(function () use ($slot, $role) {
                // Pessimistic lock — блокируем строки слота
                $slot->lockForUpdate()->find($slot->id);

                // Пользователь уже участник?
                $alreadyIn = SlotParticipant::where('slot_id', $slot->id)
                    ->where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->exists();

                if ($alreadyIn) {
                    throw new \RuntimeException('already_participant');
                }

                // Роль уже занята?
                $roleTaken = SlotParticipant::where('slot_id', $slot->id)
                    ->where('role', $role)
                    ->where('status', 'active')
                    ->exists();

                if ($roleTaken) {
                    throw new \RuntimeException('role_taken');
                }

                SlotParticipant::create([
                    'slot_id'       => $slot->id,
                    'user_id'       => Auth::id(),
                    'role'          => $role,
                    'original_role' => $role,
                    'source'        => 'signup',
                    'status'        => 'active',
                ]);

                // Если все 3 роли заняты — переводим в full
                $activeCount = SlotParticipant::where('slot_id', $slot->id)
                    ->where('status', 'active')
                    ->count();

                if ($activeCount >= 3) {
                    $slot->update(['status' => SlotStatus::Full]);
                }

                // Уведомление автору и участникам
                $this->notifyParticipants($slot, 'participant_joined', [
                    'slot_id'   => $slot->id,
                    'user_name' => Auth::user()->name,
                    'role'      => $role,
                ], excludeUserId: Auth::id());
            });
        } catch (\RuntimeException $e) {
            $msg = match($e->getMessage()) {
                'role_taken'          => 'Роль уже занята — попробуйте другую',
                'already_participant' => 'Вы уже записаны на этот слот',
                default               => 'Не удалось записаться, попробуйте ещё раз',
            };
            return back()->with('error', $msg);
        }

        return back()->with('success', 'Вы записались на слот');
    }

    /** Отписаться от слота */
    public function leave(Slot $slot)
    {
        $participant = SlotParticipant::where('slot_id', $slot->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        DB::transaction(function () use ($slot, $participant) {
            $participant->update(['status' => 'cancelled']);

            // Если слот был full — возвращаем в open
            if ($slot->status === SlotStatus::Full) {
                $slot->update(['status' => SlotStatus::Open]);
            }

            $this->notifyParticipants($slot, 'participant_left', [
                'slot_id'   => $slot->id,
                'user_name' => Auth::user()->name,
            ], excludeUserId: Auth::id());
        });

        return back()->with('success', 'Вы отписались от слота');
    }

    /** Отменить слот (только автор) */
    public function cancel(Slot $slot)
    {
        abort_unless($slot->creator_id === Auth::id(), 403);
        abort_unless(in_array($slot->status, [SlotStatus::Open, SlotStatus::Full]), 403);

        DB::transaction(function () use ($slot) {
            $slot->update(['status' => SlotStatus::Cancelled]);

            $this->notifyParticipants($slot, 'slot_cancelled', [
                'slot_id'    => $slot->id,
                'task_title' => $slot->task->title,
            ], excludeUserId: Auth::id());
        });

        return redirect()->route('triads.my-slots')
            ->with('success', 'Слот отменён');
    }

    /** Проверка конфликта по времени */
    private function hasTimeConflict(int $userId, \Carbon\Carbon $startsAt, \Carbon\Carbon $endsAt, ?int $excludeSlotId = null): bool
    {
        return SlotParticipant::where('user_id', $userId)
            ->where('status', 'active')
            ->whereHas('slot', function ($q) use ($startsAt, $endsAt, $excludeSlotId) {
                $q->whereNotIn('status', ['cancelled', 'completed'])
                  ->when($excludeSlotId, fn($q) => $q->where('id', '!=', $excludeSlotId))
                  ->whereHas('task', function ($q) use ($startsAt, $endsAt) {
                      // slot.starts_at < endsAt AND (slot.starts_at + duration) > startsAt
                      $q->whereRaw(
                          'slots.starts_at < ? AND DATE_ADD(slots.starts_at, INTERVAL tasks.duration_minutes MINUTE) > ?',
                          [$endsAt, $startsAt]
                      );
                  });
            })
            ->exists();
    }

    /** Уведомить всех активных участников слота (кроме одного) */
    private function notifyParticipants(Slot $slot, string $type, array $data, int $excludeUserId): void
    {
        $slot->load('activeParticipants');

        $userIds = $slot->activeParticipants
            ->pluck('user_id')
            ->merge([$slot->creator_id])
            ->unique()
            ->reject(fn($id) => $id === $excludeUserId);

        $now = now();
        $rows = $userIds->map(fn($uid) => [
            'user_id'    => $uid,
            'type'       => $type,
            'data'       => json_encode($data),
            'created_at' => $now,
        ])->values()->toArray();

        if ($rows) {
            TriadNotification::insert($rows);
        }
    }
}
