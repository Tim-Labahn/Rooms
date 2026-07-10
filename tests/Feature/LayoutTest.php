<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_layout_can_be_updated()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $section = Section::create(['name' => 'Test Section']);
        $room = Room::create([
            'number' => '101',
            'section_id' => $section->id,
            'capacity' => 1,
            'floor' => 1,
            'grid_column' => 0,
            'grid_row' => 0
        ]);

        $response = $this->actingAs($admin)->postJson(route('rooms.updateLayout', $room), [
            'grid_column' => 5,
            'grid_row' => 3,
            'grid_width' => 2,
            'grid_height' => 1,
            '_token' => 'fake-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $room->refresh();
        $this->assertEquals(5, $room->grid_column);
        $this->assertEquals(3, $room->grid_row);
        $this->assertEquals(2, $room->grid_width);
    }

    public function test_section_layout_can_be_updated()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $section = Section::create(['name' => 'Test Section', 'grid_column' => 0, 'grid_row' => 0]);

        $response = $this->actingAs($admin)->postJson(route('sections.updateLayout', $section), [
            'grid_column' => 2,
            'grid_row' => 1,
            'grid_width' => 3,
            'grid_height' => 2,
        ]);

        $response->assertStatus(200);
        $section->refresh();
        $this->assertEquals(2, $section->grid_column);
        $this->assertEquals(1, $section->grid_row);
    }

    public function test_hallway_layout_can_be_updated()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $section = Section::create(['name' => 'Test Section']);

        $response = $this->actingAs($admin)->postJson(route('sections.updateHallway', $section), [
            'hallway_column' => 0,
            'hallway_row' => 4,
            'hallway_width' => 10,
            'hallway_height' => 1,
        ]);

        $response->assertStatus(200);
        $section->refresh();
        $this->assertEquals(0, $section->hallway_column);
        $this->assertEquals(10, $section->hallway_width);
    }
}
