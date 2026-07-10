<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create(Request $request, Room $room)
    {
        $startTime = $request->query('start_time');
        return view('bookings.create', compact('room', 'startTime'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required_without:book_day|date|after_or_equal:now',
            'end_time' => 'required_without:book_day|date|after:start_time',
            'book_day' => 'boolean',
            'booking_date' => 'required_if:book_day,1|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $room = Room::findOrFail($data['room_id']);

        if ($data['book_day'] ?? false) {
            $date = \Carbon\Carbon::parse($data['booking_date']);
            $data['start_time'] = $date->copy()->hour(7)->minute(0)->second(0);
            $data['end_time'] = $date->copy()->hour(21)->minute(0)->second(0);
        }

        if ($room->hasPermaBooking()) {
            return redirect()->back()->with('error', 'This room is not available for booking.');
        }

        if ($room->isFullAt($data['start_time'], $data['end_time'])) {
            return redirect()->back()->with('error', 'This room is already full for the selected time.');
        }

        if (empty($data['reason'])) {
            $data['reason'] = 'used for working';
        }

        $data['user_id'] = Auth::id();

        // Check for conflicts with user's other bookings
        $hasConflict = Booking::where('user_id', $data['user_id'])
            ->where('is_ended', false)
            ->where('start_time', '<', $data['end_time'])
            ->where('end_time', '>', $data['start_time'])
            ->exists();

        if ($hasConflict && !$request->has('force_booking')) {
            return redirect()->back()
                ->withInput()
                ->with('conflict_warning', 'You already have another room booked for this time. Are you sure you want to continue?');
        }

        Booking::create($data);

        return redirect()->route('home', [
            'section_id' => $room->section_id,
            'floor' => $room->floor
        ])->with('success', 'Room booked successfully.');
    }

    public function end(Booking $booking)
    {
        $user = Auth::user();
        if ($booking->user_id !== $user->id && !$user->is_admin) {
            abort(403);
        }

        $booking->end_time = now();
        $booking->is_ended = true;

        if ($booking->user_id !== $user->id && $user->is_admin) {
            $booking->ended_by_admin = true;
        }

        $booking->save();

        return redirect()->route('home', [
            'section_id' => $booking->room->section_id,
            'floor' => $booking->room->floor
        ])->with('success', 'Booking ended.');
    }

    public function extend(Request $request, Booking $booking)
    {
        $user = Auth::user();
        if ($booking->user_id !== $user->id && !$user->is_admin) {
            abort(403);
        }

        $minutes = (int)$request->input('minutes', 60);
        $newEndTime = $booking->end_time->copy()->addMinutes($minutes);

        if ($booking->room->isFullAt($booking->end_time, $newEndTime)) {
             return redirect()->back()->with('error', 'Cannot extend: room will be full at that time.');
        }

        $booking->end_time = $newEndTime;
        $booking->save();

        return redirect()->back()->with('success', 'Booking extended until ' . $newEndTime->format('H:i'));
    }
}
