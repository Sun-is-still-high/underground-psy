<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_psychologist_dashboard_shows_intervision_groups_link(): void
    {
        $user = User::factory()->create([
            'role' => 'PSYCHOLOGIST',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Личный кабинет');
        $response->assertSee(route('psychologist.intervisions.groups', absolute: false), false);
    }
}
