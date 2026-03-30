<?php

namespace Tests\Feature\Psychologist;

use App\Models\IntervisionGroup;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntervisionStatusPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_page_shows_groups_link_and_upcoming_sessions_for_member(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);
        $creator = User::factory()->create(['role' => 'ADMIN']);

        $group = IntervisionGroup::query()->create([
            'name' => 'Intervision Team',
            'description' => 'Weekly peer supervision',
            'max_participants' => 10,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        IntervisionParticipant::query()->create([
            'group_id' => $group->id,
            'psychologist_id' => $psychologist->id,
            'is_active' => true,
            'left_at' => null,
        ]);

        $session = IntervisionSession::query()->create([
            'group_id' => $group->id,
            'topic' => 'Burnout cases',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 90,
            'status' => 'SCHEDULED',
        ]);

        $response = $this->actingAs($psychologist)->get(route('psychologist.intervisions'));

        $response->assertOk();
        $response->assertSee(route('psychologist.intervisions.groups', absolute: false), false);
        $response->assertSee($session->topic);
        $response->assertSee($group->name);
        $response->assertSee('Запланирована');
    }

    public function test_status_page_shows_non_member_message(): void
    {
        $psychologist = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);

        $response = $this->actingAs($psychologist)->get(route('psychologist.intervisions'));

        $response->assertOk();
        $response->assertSee(route('psychologist.intervisions.groups', absolute: false), false);
        $response->assertSee('Вы не состоите ни в одной группе интервизий.');
    }
}
