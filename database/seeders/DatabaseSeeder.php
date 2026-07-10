<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Building;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Feature;
use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@rooms.test',
            'password' => Hash::make('password'),
            'is_admin' => true
        ]);

        // 2. Create Regular Users
        $users = User::factory()->count(250)->create();

        // 3. Create Features
        $features = [
            'Capacity' => Feature::firstOrCreate(['name' => 'Capacity']),
            'Projector' => Feature::firstOrCreate(['name' => 'Projector']),
            'Whiteboard' => Feature::firstOrCreate(['name' => 'Whiteboard']),
            'Coffee' => Feature::firstOrCreate(['name' => 'Coffee Machine']),
            'TV' => Feature::firstOrCreate(['name' => 'Large TV']),
            'AC' => Feature::firstOrCreate(['name' => 'Air Conditioning']),
        ];

        // 3.5 Create Buildings
        $buildings = [
            Building::create(['name' => 'HQ East']),
            Building::create(['name' => 'Tech Hub West']),
        ];

        // 4. Create Sections
        $sections = [];
        foreach ($buildings as $building) {
            $sections[] = Section::create(['name' => 'Main Wing', 'building_id' => $building->id]);
            $sections[] = Section::create(['name' => 'Executive Floor', 'building_id' => $building->id]);
            $sections[] = Section::create(['name' => 'Innovation Lab', 'building_id' => $building->id]);
        }

        // 5. Create Rooms across floors and sections
        $roomNames = ['Conference', 'Focus', 'Meeting', 'Office', 'Lounge', 'Lab', 'Studio', 'Booth', 'War Room'];
        foreach ($sections as $section) {
            for ($floor = 0; $floor <= 4; $floor++) {
                $numRooms = rand(15, 25);
                for ($i = 1; $i <= $numRooms; $i++) {
                    $type = $roomNames[array_rand($roomNames)];
                    $roomNumber = ($floor == 0 ? 'E' : $floor) . sprintf('%02d', $i);

                    $isPrivateOffice = rand(1, 10) > 8;
                    $ownerName = null;
                    $isFlexible = true;

                    if ($isPrivateOffice) {
                        $ownerName = $users->random()->name;
                        $isFlexible = false;
                    }

                    $room = Room::create([
                        'name' => "$type $roomNumber",
                        'owner_name' => $ownerName,
                        'is_flexible' => $isFlexible,
                        'section_id' => $section->id,
                        'floor' => $floor,
                        'status' => rand(1, 100) > 95 ? 'out_of_order' : 'available',
                    ]);

                    // Add features
                    $roomCapacity = rand(2, 20);
                    $room->features()->attach($features['Capacity']->id, ['value' => (string)$roomCapacity]);
                    if (rand(1, 10) > 4) $room->features()->attach($features['Projector']->id, ['value' => 'Yes']);
                    if (rand(1, 10) > 5) $room->features()->attach($features['Whiteboard']->id, ['value' => 'Yes']);
                    if (rand(1, 10) > 7) $room->features()->attach($features['TV']->id, ['value' => 'Yes']);
                    if (rand(1, 10) > 3) $room->features()->attach($features['AC']->id, ['value' => 'Yes']);
                }
            }
        }

        // 6. Create some Bookings (Historical, Current and Future)
        $rooms = Room::all();
        $reasons = [
            'Weekly sync meeting',
            'Focus time for development',
            'Client presentation',
            'Internal workshop',
            'Project planning',
            'Interview',
            'Brainstorming session',
            'Quick check-in',
            'Documentation sprint',
            'Testing session',
            'Budget review',
            'Design critique',
            'All-hands meeting prep',
            'One-on-one session'
        ];

        for($k=0; $k<3000; $k++) {
            $room = $rooms->random();
            $user = $users->random();

            // Random date in a 10-week window around today
            $start = now()->subWeeks(4)->addDays(rand(0, 70))->hour(rand(8, 17))->minute(rand(0, 3) * 15)->second(0);

            // Skip weekends
            if ($start->isWeekend()) {
                $start->addDays(2);
            }

            $duration = rand(1, 4) * 30; // 30 mins to 2 hours usually

            // Simple collision check avoid overlapping bookings for same room same time
            $collision = Booking::where('room_id', $room->id)
                ->where('start_time', '<', $start->copy()->addMinutes($duration))
                ->where('end_time', '>', $start)
                ->exists();

            if (!$collision) {
                Booking::create([
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'start_time' => $start,
                    'end_time' => $start->copy()->addMinutes($duration),
                    'reason' => $reasons[array_rand($reasons)]
                ]);
            }
        }
    }
}
