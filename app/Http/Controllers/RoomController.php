<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use App\Models\Section;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $sections = Section::all();
        $users = User::all();
        $features = Feature::all();

        $defaultSectionId = $request->query('section_id');
        $defaultFloor = $request->query('floor');

        return view('rooms.form', compact('sections', 'users', 'features', 'defaultSectionId', 'defaultFloor'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'is_flexible' => 'boolean',
            'floor' => 'required|integer',
            'section_id' => 'required|exists:sections,id',
            'capacity' => 'required|integer|min:1',
            'order' => 'nullable|integer',
            'status' => 'nullable|string',
            'owner_ids' => 'nullable|array',
            'owner_ids.*' => 'exists:users,id',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
            'feature_values' => 'nullable|array',
        ]);

        $data['created_by'] = Auth::id();
        $room = Room::create($data);

        // Ensure "Capacity" feature is synced
        $capacityFeature = Feature::firstOrCreate(['name' => 'Capacity']);
        $featureData = [];
        if (!empty($data['feature_ids'])) {
            foreach ($data['feature_ids'] as $id) {
                if ($id == $capacityFeature->id) continue;
                $featureData[$id] = ['value' => $data['feature_values'][$id] ?? null];
            }
        }
        $featureData[$capacityFeature->id] = ['value' => (string)$data['capacity']];
        $room->features()->sync($featureData);

        return redirect()->route('home', ['section_id' => $room->section_id, 'floor' => $room->floor])
            ->with('success', 'Room created successfully.');
    }

    public function edit(Room $room)
    {
        $sections = Section::all();
        $users = User::all();
        $features = Feature::all();
        return view('rooms.form', compact('room', 'sections', 'users', 'features'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'is_flexible' => 'boolean',
            'floor' => 'required|integer',
            'section_id' => 'required|exists:sections,id',
            'capacity' => 'required|integer|min:1',
            'order' => 'nullable|integer',
            'status' => 'nullable|string',
            'owner_ids' => 'nullable|array',
            'owner_ids.*' => 'exists:users,id',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
            'feature_values' => 'nullable|array',
        ]);

        $room->update($data);

        $room->owners()->sync($data['owner_ids'] ?? []);

        $capacityFeature = Feature::firstOrCreate(['name' => 'Capacity']);
        $featureData = [];
        if (!empty($data['feature_ids'])) {
            foreach ($data['feature_ids'] as $id) {
                if ($id == $capacityFeature->id) continue;
                $featureData[$id] = ['value' => $data['feature_values'][$id] ?? null];
            }
        }
        $featureData[$capacityFeature->id] = ['value' => (string)$data['capacity']];
        $room->features()->sync($featureData);

        return redirect()->route('home', ['section_id' => $room->section_id, 'floor' => $room->floor])
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        // End all bookings (active and future)
        $room->bookings()->where('is_ended', false)
            ->where('end_time', '>=', now())
            ->update(['end_time' => now(), 'is_ended' => true]);

        $room->deleted_by = Auth::id();
        $room->save();

        $room->delete();
        return redirect()->route('home', ['section_id' => $room->section_id, 'floor' => $room->floor])
            ->with('success', 'Room deleted successfully.');
    }


    public function show(Request $request, Room $room)
    {
        $weekOffset = (int)$request->get('week', 0);
        if ($weekOffset < 0) $weekOffset = 0;
        if ($weekOffset > 3) $weekOffset = 3;

        $room->load(['features', 'owners', 'bookings' => function($q) {
            $q->where('is_ended', false)->where('end_time', '>=', now())->with('user')->orderBy('start_time');
        }]);

        $activeBookings = $room->activeBookings()->with('user')->get();
        $myActiveBooking = $room->activeBooking(Auth::id());
        $futureBookings = $room->bookings->filter(function($b) {
            return $b->start_time > now() && !$b->is_ended;
        });

        $whenEmptyAgain = null;
        if ($room->isFull()) {
            $earliestEnd = $activeBookings->sortBy('end_time')->first()?->end_time;
            $whenEmptyAgain = $earliestEnd;
        }

        $startDate = now()->addWeeks($weekOffset)->startOfWeek(\Carbon\Carbon::MONDAY);
        $endDate = $startDate->copy()->addDays(5)->endOfDay(); // Saturday

        $sevenDaysBookings = $room->bookings()
            ->where('is_ended', false)
            ->where('end_time', '>=', $startDate)
            ->where('start_time', '<=', $endDate)
            ->get();

        $currentBookings = Auth::user()->bookings()
            ->where('is_ended', false)
            ->where('end_time', '>=', now())
            ->with('room')
            ->get();

        return view('rooms.show', compact('room', 'activeBookings', 'myActiveBooking', 'futureBookings', 'whenEmptyAgain', 'sevenDaysBookings', 'weekOffset', 'startDate', 'currentBookings'));
    }

    public function toggle(Room $room)
    {
        $user = Auth::user();

        // Find active booking (any user)
        $activeBooking = $room->bookings()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        // Find active booking by THIS user
        $userBooking = $room->bookings()
            ->where('user_id', $user->id)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->first();

        // If user booked it → unbook
        if ($userBooking) {
            $userBooking->end_time = now();
            $userBooking->save();
            return redirect()->route('home');
        }

        // If someone else booked it → deny
        if ($activeBooking) {
            return redirect()->route('home')
                ->with('error', 'This room is currently not available');
        }

        // Otherwise → create new booking for 1 hour
        Booking::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'booking_time' => now(),
        ]);

        return redirect()->route('home');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:rooms,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Room::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
