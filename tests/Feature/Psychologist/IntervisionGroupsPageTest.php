<?php

namespace Tests\Feature\Psychologist;

use App\Models\IntervisionAttendance;
use App\Models\IntervisionGroup;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntervisionGroupsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_page_shows_progress_counter_from_attendance_and_settings(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['role' => 'ADMIN']);

        DB::table('intervision_settings')->updateOrInsert(
            ['setting_key' => 'min_sessions'],
            ['setting_value' => '4', 'description' => 'Minimum sessions']
        );

        $group = IntervisionGroup::query()->create([
            'name' => 'Progress Group',
            'description' => null,
            'max_participants' => 10,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $participant = IntervisionParticipant::query()->create([
            'group_id' => $group->id,
            'psychologist_id' => $psychologist->id,
            'is_active' => true,
            'left_at' => null,
        ]);

        $completedA = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Completed A',
            'scheduled_at' => now()->subDays(5),
            'duration_minutes' => 90,
            'status' => 'COMPLETED',
        ]);
        $completedB = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Completed B',
            'scheduled_at' => now()->subDays(10),
            'duration_minutes' => 90,
            'status' => 'COMPLETED',
        ]);

        IntervisionAttendance::query()->create([
            'session_id' => $completedA->id,
            'participant_id' => $participant->id,
            'attended' => true,
        ]);
        IntervisionAttendance::query()->create([
            'session_id' => $completedB->id,
            'participant_id' => $participant->id,
            'attended' => true,
        ]);

        IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Upcoming Meeting',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 90,
            'status' => 'SCHEDULED',
        ]);

        $response = $this->actingAs($psychologist)->get(route('psychologist.intervisions.groups'));

        $response->assertOk();
        $response->assertSee('2 / 4');
        $response->assertSee('Требуется ещё');
        $response->assertSee('Запланирована');
    }
}
