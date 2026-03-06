<?php

namespace App\Console\Commands;

use App\Models\Slot;
use App\Models\TriadNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredSlots extends Command
{
    protected $signature   = 'triads:cancel-expired-slots';
    protected $description = 'Автоматически отменяет незаполненные слоты, дедлайн записи которых прошёл (менее 1 часа до начала)';

    public function handle(): void
    {
        // Ищем слоты со статусом 'open', до начала которых осталось менее 1 часа
        $slots = Slot::where('status', 'open')
            ->where('starts_at', '<=', now()->addHour())
            ->where('starts_at', '>', now()) // ещё не начались
            ->with('activeParticipants')
            ->get();

        if ($slots->isEmpty()) {
            $this->info('Нет слотов для отмены.');
            return;
        }

        foreach ($slots as $slot) {
            DB::transaction(function () use ($slot) {
                $slot->update(['status' => 'cancelled']);

                // Уведомляем участников
                $notifications = $slot->activeParticipants->map(fn($p) => [
                    'user_id'    => $p->user_id,
                    'type'       => 'slot_cancelled',
                    'data'       => json_encode(['slot_id' => $slot->id]),
                    'created_at' => now(),
                ]);

                if ($notifications->isNotEmpty()) {
                    TriadNotification::insert($notifications->toArray());
                }
            });

            $this->line("Слот #{$slot->id} отменён.");
        }

        $this->info("Отменено слотов: {$slots->count()}");
    }
}
