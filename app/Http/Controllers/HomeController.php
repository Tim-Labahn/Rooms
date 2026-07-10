<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Section;
use App\Models\Building;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class HomeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();
        $buildings = Building::all();
        $selectedBuildingId = $request->get('building_id', $buildings->first()->id ?? null);
        $selectedBuilding = $selectedBuildingId ? Building::with('sections')->find($selectedBuildingId) : null;

        $selectedSectionId = $request->get('section_id');
        $selectedFloor = $request->get('floor', 0); // Default to Ground Floor E

        $selectedSection = null;
        $rooms = collect();

        if ($selectedSectionId) {
            $selectedSection = Section::find($selectedSectionId);
            if ($selectedSection) {
                $rooms = Room::where('section_id', $selectedSectionId)
                    ->where('floor', $selectedFloor)
                    ->with(['bookings', 'features'])
                    ->get();
            }
        }

        $currentBookings = $user->bookings()
            ->where('is_ended', false)
            ->where('end_time', '>=', now())
            ->with('room')
            ->get();

        $searchResults = collect();
        if ($request->has('search') && !empty($request->get('search'))) {
            $search = $request->get('search');
            $searchResults = Room::where('name', 'like', "%$search%")
                ->orWhere('owner_name', 'like', "%$search%")
                ->orWhereHas('features', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                })
                ->with(['section.building', 'features'])
                ->limit(10)
                ->get();

            if ($searchResults->count() === 1) {
                $room = $searchResults->first();
                $params = ['edit_mode' => $request->get('edit_mode')];
                $url = Auth::user()->is_admin ? route('rooms.edit', array_merge([$room->id], $params)) : route('rooms.show', array_merge([$room->id], $params));
                return redirect($url);
            }
        }

        $quickBook = $request->get('quick_book') === '1';
        $editMode = $request->get('edit_mode') === '1' && Auth::user()->is_admin;

        $rooms_all = collect();
        if ($quickBook) {
            $rooms_all = Room::where('status', '!=', 'out_of_order')
                ->with(['features', 'bookings' => function($q) {
                    $q->where('is_ended', false)
                      ->where('start_time', '<=', now())
                      ->where('end_time', '>=', now());
                }])
                ->get()
                ->filter(function($room) {
                    // Check capacity from eager loaded features and active bookings from eager loaded bookings
                    return !$room->hasPermaBooking() && $room->bookings->count() < $room->capacity && $room->capacity > 0;
                })
                ->take(20);
        }

        return view('home', compact(
            'buildings',
            'selectedBuilding',
            'selectedSection',
            'selectedFloor',
            'rooms',
            'currentBookings',
            'searchResults',
            'quickBook',
            'rooms_all',
            'editMode'
        ));
    }

    public function searchSuggestions(Request $request)
    {
        $search = $request->get('q');
        if (empty($search)) return response()->json([]);

        $rooms = Room::where('name', 'like', "%$search%")
            ->orWhere('owner_name', 'like', "%$search%")
            ->with('section')
            ->limit(5)
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'name' => ($room->owner_name && stripos($room->owner_name, request('q')) !== false) ? "$room->name ($room->owner_name)" : ($room->name ?? 'Büro'),
                    'number' => $room->number,
                    'section' => $room->section->name,
                    'floor' => $room->floor,
                    'url' => (Auth::user()->is_admin && request('edit_mode') == '1') ? route('rooms.edit', $room->id) : route('rooms.show', $room->id)
                ];
            });

        return response()->json($rooms);
    }

    public function adminOverview()
    {
        $this->authorize('admin');

        $totalRooms = Room::count();
        $totalUsers = User::count();
        $activeBookings = Booking::where('is_ended', false)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        $roomsWithCurrentBookings = Room::whereHas('bookings', function($q) {
            $q->where('is_ended', false)
              ->where('start_time', '<=', now())
              ->where('end_time', '>=', now());
        })->count();

        $occupancyRate = $totalRooms > 0 ? ($roomsWithCurrentBookings / $totalRooms) * 100 : 0;

        // Booking reasons distribution
        $reasons = Booking::select('reason', DB::raw('count(*) as total'))
            ->groupBy('reason')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Busy rooms
        $busyRooms = Room::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // Recent bookings
        $recentBookings = Booking::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.overview', compact(
            'totalRooms',
            'totalUsers',
            'activeBookings',
            'occupancyRate',
            'reasons',
            'busyRooms',
            'recentBookings'
        ));
    }
}
