<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_book_future_if_currently_full()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $section = Section::create(['name' => 'Test Section']);
        $room = Room::create([
            'number' => '101',
            'section_id' => $section->id,
            'capacity' => 1,
            'floor' => 1
        ]);

        // Room is currently full
        Booking::create([
            'room_id' => $room->id,
            'user_id' => $admin->id,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'reason' => 'Working',
            'is_ended' => false
        ]);

        $this->assertTrue($room->isFull());

        // Try to book for tomorrow
        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'room_id' => $room->id,
            'start_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'reason' => 'Future meeting'
        ]);

        // This should now SUCCEED
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Room booked successfully.');
        
        $this->assertEquals(2, Booking::count());
    }
}
