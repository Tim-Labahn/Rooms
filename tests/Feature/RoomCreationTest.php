<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_create_room_page()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $section = Section::create(['name' => 'Test Section']);

        $response = $this->actingAs($admin)->get(route('rooms.create', [
            'section_id' => $section->id,
            'floor' => 1
        ]));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_create_room_page()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $section = Section::create(['name' => 'Test Section']);

        $response = $this->actingAs($user)->get(route('rooms.create', [
            'section_id' => $section->id,
            'floor' => 1
        ]));

        // Based on EnsureUserIsAdmin middleware, it redirects to home
        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }
}
