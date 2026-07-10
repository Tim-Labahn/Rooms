<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Section;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_produces_expected_data()
    {
        $this->seed();

        // Check Admin
        $this->assertDatabaseHas('users', [
            'email' => 'admin@rooms.test',
            'is_admin' => true
        ]);

        // Check Sections
        $this->assertDatabaseCount('sections', 2);
        $this->assertDatabaseHas('sections', [
            'name' => 'Main Wing',
            'grid_width' => 10
        ]);

        // Check Rooms
        $this->assertDatabaseHas('rooms', [
            'name' => 'Conference A',
            'capacity' => 10,
            'grid_column' => 1,
            'grid_row' => 1
        ]);

        // Check Bookings
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_room_grid_coordinates_are_within_section_bounds()
    {
        $this->seed();

        $rooms = Room::with('section')->get();
        foreach ($rooms as $room) {
            $section = $room->section;

            // Check if room fits within section grid
            $this->assertGreaterThanOrEqual(1, $room->grid_column, "Room {$room->name} col out of bounds");
            $this->assertGreaterThanOrEqual(1, $room->grid_row, "Room {$room->name} row out of bounds");

            $this->assertLessThanOrEqual(
                $section->grid_width,
                $room->grid_column + $room->width - 1,
                "Room {$room->name} exceeds section width"
            );
            $this->assertLessThanOrEqual(
                $section->grid_height,
                $room->grid_row + $room->height - 1,
                "Room {$room->name} exceeds section height"
            );
        }
    }
}
