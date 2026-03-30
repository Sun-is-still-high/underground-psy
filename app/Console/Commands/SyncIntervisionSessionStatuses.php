<?php

namespace App\Console\Commands;

use App\Models\IntervisionSession;
use Illuminate\Console\Command;

class SyncIntervisionSessionStatuses extends Command
{
    protected $signature = 'intervision:sync-session-statuses';

    protected $description = 'Автоматически синхронизирует статусы интервизионных сессий по времени';

    public function handle(): int
    {
        $now = now();
        $sessionEndExpression = $this->dateAddMinutesExpression('scheduled_at', 'duration_minutes');

        // Сессия считается активной в окне [scheduled_at, scheduled_at + duration).
        $movedToInProgress = IntervisionSession::query()
            ->where('status', 'SCHEDULED')
            ->where('scheduled_at', '<=', $now)
            ->whereRaw("{$sessionEndExpression} > ?", [$now])
            ->update(['status' => 'IN_PROGRESS']);

        // По завершении интервала переводим в COMPLETED, если сессия не отменена.
        $movedToCompleted = IntervisionSession::query()
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])
            ->whereRaw("{$sessionEndExpression} <= ?", [$now])
            ->update(['status' => 'COMPLETED']);

        $this->info("Intervision status sync: IN_PROGRESS={$movedToInProgress}, COMPLETED={$movedToCompleted}");

        return self::SUCCESS;
    }

    private function dateAddMinutesExpression(string $datetimeColumn, string $minutesColumn): string
    {
        $driver = IntervisionSession::query()->getConnection()->getDriverName();

        return match ($driver) {
            'sqlite' => "datetime({$datetimeColumn}, '+' || {$minutesColumn} || ' minutes')",
            'pgsql' => "({$datetimeColumn} + ({$minutesColumn} || ' minutes')::interval)",
            default => "DATE_ADD({$datetimeColumn}, INTERVAL {$minutesColumn} MINUTE)",
        };
    }
}
