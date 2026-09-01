<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_with_branch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $branch = Branch::create(['name' => 'Jakarta']);

        $response = $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'branch_id' => $branch->id,
        ]);

        $response->assertRedirect(route('dashboard.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'branch_id' => $branch->id,
            'role' => 'user',
        ]);
    }

    public function test_dashboard_lists_events_in_active_draft_closed_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create([
            'title' => 'Closed Event',
            'slug' => 'closed-event',
            'status' => 'closed',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Draft Event',
            'slug' => 'draft-event',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Active Event',
            'slug' => 'active-event',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'Active Event',
            'Draft Event',
            'Closed Event',
        ]);
    }
}
