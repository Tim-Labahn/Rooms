<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_book_available_room()
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['capacity' => 1]);

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'room_id' => $room->id,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'reason' => 'Test Booking'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'reason' => 'Test Booking'
        ]);
    }

    public function test_user_cannot_book_room_exceeding_capacity()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = Room::factory()->create(['capacity' => 1]);

        // First booking
        Booking::create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
            'reason' => 'Meeting 1'
        ]);

        // Second booking attempt for same time
        $response = $this->actingAs($user2)->from(route('home'))->post(route('bookings.store'), [
            'room_id' => $room->id,
            'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
            'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'reason' => 'Should fail'
        ]);

        // It should redirect back with an error
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'This room is already full for the selected time.');
        $this->assertDatabaseCount('bookings', 1);
    }
}
