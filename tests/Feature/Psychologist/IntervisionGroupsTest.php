<?php

namespace Tests\Feature\Psychologist;

use App\Models\IntervisionAttendance;
use App\Models\IntervisionGroup;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntervisionGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_psychologist_can_join_group_and_attendance_is_created_for_upcoming_sessions(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);
        $creator = User::factory()->create(['role' => 'ADMIN']);

        $group = IntervisionGroup::query()->create([
            'name' => 'Group A',
            'description' => null,
            'max_participants' => 10,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $upcomingScheduled = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Topic 1',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 90,
            'status' => 'SCHEDULED',
        ]);
        $upcomingInProgress = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Topic 2',
            'scheduled_at' => now()->subMinutes(10),
            'duration_minutes' => 60,
            'status' => 'IN_PROGRESS',
        ]);
        IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Old Topic',
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'status' => 'SCHEDULED',
        ]);

        $response = $this->actingAs($psychologist)
            ->post(route('psychologist.intervisions.groups.join', $group));

        $response->assertRedirect();

        $participant = IntervisionParticipant::query()
            ->where('group_id', $group->id)
            ->where('psychologist_id', $psychologist->id)
            ->first();

        $this->assertNotNull($participant);
        $this->assertTrue((bool) $participant->is_active);

        $attendanceSessionIds = IntervisionAttendance::query()
            ->where('participant_id', $participant->id)
            ->pluck('session_id')
            ->all();

        $this->assertEqualsCanonicalizing(
            [$upcomingScheduled->id, $upcomingInProgress->id],
            $attendanceSessionIds
        );
    }

    public function test_rejoining_group_reactivates_existing_participant_without_duplicate_attendance(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);
        $creator = User::factory()->create(['role' => 'ADMIN']);

        $group = IntervisionGroup::query()->create([
            'name' => 'Group B',
            'description' => null,
            'max_participants' => 10,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $sessionA = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Topic A',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 90,
            'status' => 'SCHEDULED',
        ]);
        $sessionB = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Topic B',
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 90,
            'status' => 'SCHEDULED',
        ]);

        $participant = IntervisionParticipant::query()->create([
            'group_id' => $group->id,
            'psychologist_id' => $psychologist->id,
            'is_active' => false,
            'left_at' => now()->subDay(),
        ]);

        IntervisionAttendance::query()->create([
            'session_id' => $sessionA->id,
            'participant_id' => $participant->id,
            'attended' => false,
            'marked_at' => null,
            'marked_by' => null,
            'notes' => null,
        ]);

        $response = $this->actingAs($psychologist)
            ->post(route('psychologist.intervisions.groups.join', $group));

        $response->assertRedirect();

        $participant->refresh();
        $this->assertTrue((bool) $participant->is_active);
        $this->assertNull($participant->left_at);

        $this->assertSame(
            1,
            IntervisionParticipant::query()
                ->where('group_id', $group->id)
                ->where('psychologist_id', $psychologist->id)
                ->count()
        );

        $attendanceSessionIds = IntervisionAttendance::query()
            ->where('participant_id', $participant->id)
            ->pluck('session_id')
            ->all();

        $this->assertEqualsCanonicalizing([$sessionA->id, $sessionB->id], $attendanceSessionIds);
    }

    public function test_psychologist_can_leave_group(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);
        $creator = User::factory()->create(['role' => 'ADMIN']);

        $group = IntervisionGroup::query()->create([
            'name' => 'Group C',
            'description' => null,
            'max_participants' => 10,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $participant = IntervisionParticipant::query()->create([
            'group_id' => $group->id,
            'psychologist_id' => $psychologist->id,
            'is_active' => true,
            'left_at' => null,
        ]);

        $response = $this->actingAs($psychologist)
            ->post(route('psychologist.intervisions.groups.leave', $group));

        $response->assertRedirect();

        $participant->refresh();
        $this->assertFalse((bool) $participant->is_active);
        $this->assertNotNull($participant->left_at);
    }
}
