<?php

namespace App\Http\Controllers\Triads;

use App\Enums\InvitationStatus;
use App\Enums\SlotStatus;
use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\SlotInvitation;
use App\Models\SlotParticipant;
use App\Models\TriadNotification;
use App\Models\User;
use App\Notifications\InvitationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlotInvitationController extends Controller
{
    /** Отправить приглашение */
    public function store(Request $request, Slot $slot)
    {
        if ($slot->creator_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($slot->status->value, ['open', 'full'])) {
            return back()->with('error', 'Нельзя приглашать в слот с текущим статусом');
        }

        $request->validate([
            'invitee_id'    => 'required|exists:users,id',
            'proposed_role' => 'required|in:therapist,client,observer',
        ], [
            'invitee_id.required'    => 'Выберите психолога',
            'proposed_role.required' => 'Выберите роль',
        ]);

        $invitee = User::findOrFail($request->invitee_id);

        if (!$invitee->isPsychologist()) {
            return back()->with('error', 'Можно приглашать только психологов');
        }

        // Уже участник?
        $alreadyParticipant = $slot->activeParticipants
            ->firstWhere('user_id', $invitee->id);

        if ($alreadyParticipant) {
            return back()->with('error', 'Этот пользователь уже участвует в слоте');
        }

        // Роль уже занята?
        $takenRoles = $slot->activeParticipants
            ->keyBy(fn($p) => $p->role->value ?? $p->role);

        if ($takenRoles->has($request->proposed_role)) {
            return back()->with('error', 'Эта роль уже занята');
        }

        // Уже есть активное приглашение на эту роль?
        $existing = SlotInvitation::where('slot_id', $slot->id)
            ->where('proposed_role', $request->proposed_role)
            ->where('status', InvitationStatus::Pending)
            ->exists();

        if ($existing) {
            return back()->with('error', 'На эту роль уже отправлено приглашение');
        }

        DB::transaction(function () use ($slot, $invitee, $request) {
            $invitation = SlotInvitation::create([
                'slot_id'       => $slot->id,
                'inviter_id'    => Auth::id(),
                'invitee_id'    => $invitee->id,
                'proposed_role' => $request->proposed_role,
                'status'        => InvitationStatus::Pending,
            ]);

            TriadNotification::create([
                'user_id'    => $invitee->id,
                'type'       => 'invitation_received',
                'data'       => [
                    'slot_id'       => $slot->id,
                    'invitation_id' => $invitation->id,
                    'user_name'     => Auth::user()->name,
                    'role'          => $request->proposed_role,
                ],
                'created_at' => now(),
            ]);

            // Email-уведомление приглашённому
            $invitee->notify(new InvitationReceived($slot, $invitation));
        });

        return back()->with('success', 'Приглашение отправлено');
    }

    /** Принять приглашение */
    public function accept(Slot $slot, SlotInvitation $invitation)
    {
        if ($invitation->invitee_id !== Auth::id()) {
            abort(403);
        }

        if ($invitation->status !== InvitationStatus::Pending) {
            return back()->with('error', 'Приглашение уже обработано');
        }

        if (!in_array($slot->status->value, ['open', 'full'])) {
            return back()->with('error', 'Слот больше недоступен');
        }

        DB::transaction(function () use ($slot, $invitation) {
            $slot->loadMissing('activeParticipants');

            // Проверяем роль с блокировкой
            $slot->activeParticipants()->lockForUpdate()->get();

            $takenRoles = $slot->fresh()->activeParticipants
                ->keyBy(fn($p) => $p->role->value ?? $p->role);

            if ($takenRoles->has($invitation->proposed_role->value)) {
                throw new \RuntimeException('role_taken');
            }

            // Уже участник?
            $alreadyIn = $slot->activeParticipants
                ->firstWhere('user_id', Auth::id());

            if ($alreadyIn) {
                throw new \RuntimeException('already_participant');
            }

            SlotParticipant::create([
                'slot_id'       => $slot->id,
                'user_id'       => Auth::id(),
                'role'          => $invitation->proposed_role->value,
                'original_role' => $invitation->proposed_role->value,
                'source'        => 'invitation',
                'status'        => 'active',
            ]);

            $invitation->update(['status' => InvitationStatus::Accepted]);

            // Если 3 участника → full
            $count = $slot->activeParticipants()->count() + 1;
            if ($count >= 3) {
                $slot->update(['status' => SlotStatus::Full]);
            }

            // Уведомление пригласившему
            TriadNotification::create([
                'user_id'    => $invitation->inviter_id,
                'type'       => 'invitation_accepted',
                'data'       => [
                    'slot_id'   => $slot->id,
                    'user_name' => Auth::user()->name,
                ],
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Вы приняли приглашение');
    }

    /** Отклонить приглашение */
    public function decline(Slot $slot, SlotInvitation $invitation)
    {
        if ($invitation->invitee_id !== Auth::id()) {
            abort(403);
        }

        if ($invitation->status !== InvitationStatus::Pending) {
            return back()->with('error', 'Приглашение уже обработано');
        }

        $invitation->update(['status' => InvitationStatus::Declined]);

        TriadNotification::create([
            'user_id'    => $invitation->inviter_id,
            'type'       => 'invitation_declined',
            'data'       => [
                'slot_id'   => $slot->id,
                'user_name' => Auth::user()->name,
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Приглашение отклонено');
    }
}
