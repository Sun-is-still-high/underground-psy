<?php

namespace Tests\Feature\Console;

use App\Models\IntervisionGroup;
use App\Models\IntervisionSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncIntervisionSessionStatusesTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_updates_session_statuses_by_time_window(): void
    {
        $now = Carbon::create(2026, 3, 30, 12, 0, 0);
        Carbon::setTestNow($now);
        try {
            $creator = User::factory()->create(['role' => 'ADMIN']);
            $group = IntervisionGroup::query()->create([
                'name' => 'Status Group',
                'description' => null,
                'max_participants' => 10,
                'is_active' => true,
                'created_by' => $creator->id,
            ]);

            $toInProgress = IntervisionSession::query()->create([
                'group_id' => $group->id,
                'topic' => 'Should become IN_PROGRESS',
                'scheduled_at' => $now->copy()->subMinutes(10),
                'duration_minutes' => 60,
                'status' => 'SCHEDULED',
            ]);
            $toCompletedFromScheduled = IntervisionSession::query()->create([
                'group_id' => $group->id,
                'topic' => 'Should become COMPLETED (scheduled)',
                'scheduled_at' => $now->copy()->subMinutes(100),
                'duration_minutes' => 30,
                'status' => 'SCHEDULED',
            ]);
            $toCompletedFromInProgress = IntervisionSession::query()->create([
                'group_id' => $group->id,
                'topic' => 'Should become COMPLETED (in progress)',
                'scheduled_at' => $now->copy()->subMinutes(100),
                'duration_minutes' => 30,
                'status' => 'IN_PROGRESS',
            ]);
            $cancelled = IntervisionSession::query()->create([
                'group_id' => $group->id,
                'topic' => 'Cancelled must stay cancelled',
                'scheduled_at' => $now->copy()->subMinutes(100),
                'duration_minutes' => 30,
                'status' => 'CANCELLED',
            ]);

            $this->artisan('intervision:sync-session-statuses')->assertExitCode(0);

            $this->assertSame('IN_PROGRESS', $toInProgress->refresh()->status);
            $this->assertSame('COMPLETED', $toCompletedFromScheduled->refresh()->status);
            $this->assertSame('COMPLETED', $toCompletedFromInProgress->refresh()->status);
            $this->assertSame('CANCELLED', $cancelled->refresh()->status);
        } finally {
            Carbon::setTestNow();
        }
    }
}
