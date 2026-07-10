<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $room->number }} Details</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        h1 { margin: 0; color: #333; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .badge-available { background: #e6ffed; color: #28a745; }
        .badge-occupied { background: #fff3cd; color: #856404; }
        .badge-full { background: #f8d7da; color: #721c24; }
        .badge-perma { background: #e2e3e5; color: #383d41; }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .info-section h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 18px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px; }
        .info-label { color: #666; font-weight: bold; }

        .booking-list { list-style: none; padding: 0; }
        .booking-item { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #007bff; }
        .booking-item.mine { border-left-color: #28a745; background: #f0fff4; }
        .booking-time { font-weight: bold; display: block; margin-bottom: 5px; }
        .booking-user { color: #555; font-size: 14px; }

        .action-btns { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn { padding: 12px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 16px; font-weight: bold; text-decoration: none; text-align: center; transition: background 0.2s; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-danger { background: #ff6b6b; color: white; }
        .btn-danger:hover { background: #d64545; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }

        .feature-tag { display: inline-block; background: #e9ecef; padding: 4px 10px; border-radius: 4px; margin-right: 5px; margin-bottom: 5px; font-size: 14px; }

        .empty-status { color: #d64545; font-weight: bold; margin-top: 10px; }

        .availability-window { margin-top: 40px; }
        .availability-grid { display: grid; grid-template-columns: 80px repeat(6, 1fr); gap: 1px; background: #ddd; border: 1px solid #ddd; }
        .grid-header { background: #f8f9fa; padding: 10px 5px; text-align: center; font-weight: bold; font-size: 14px; }
        .grid-time-col { background: #f8f9fa; font-size: 12px; padding: 5px; text-align: right; font-weight: bold; }
        .grid-cell { background: white; height: 35px; position: relative; border: 1px solid #eee; transition: background 0.2s; }
        .grid-cell.booked { background: #f8d7da; }
        .grid-cell.mine { background: #d1ecf1; }
        .grid-cell.partially-booked { background: #fff3cd; }
        .grid-cell.past { background: #f1f3f5; opacity: 0.6; cursor: not-allowed; }
        .grid-cell.today { border: 2px solid #007bff !important; z-index: 2; }
        .grid-cell-label { position: absolute; font-size: 10px; color: #721c24; top: 50%; left: 50%; transform: translate(-50%, -50%); white-space: nowrap; pointer-events: none; }
        .grid-cell:not(.booked):not(.past):hover { background: #f0f0f0; }
        .grid-cell:not(.booked):not(.past):hover::after { content: "+"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 20px; color: #007bff; font-weight: bold; }
        .grid-cell a { display: block; width: 100%; height: 100%; text-decoration: none; }
        .week-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .week-nav .btn { padding: 5px 15px; font-size: 14px; }
    </style>
</head>
<body>
    @include('partials.my_bookings')
    @include('partials.top_right_nav')
    @include('partials.legend')

<div class="container">
    <div class="header" style="flex-direction: row-reverse; text-align: right;">
        <div style="margin-left: 20px;">
            @if($room->hasPermaBooking())
                <span class="badge badge-perma">Non-bookable</span>
            @elseif($room->isFull())
                <span class="badge badge-full">Full</span>
            @elseif($room->isOccupied())
                <span class="badge badge-occupied">Occupied ({{ $room->occupancyCount() }}/{{ $room->capacity }})</span>
            @else
                <span class="badge badge-available">Available</span>
            @endif
        </div>
        <div style="flex-grow: 1;">
            <h1>{{ $room->name ?? 'Büro' }} ({{ $room->number }})</h1>
            @if($room->owner_name)
                <p style="margin: 5px 0; color: #333; font-weight: bold;">👤 Assigned to: {{ $room->owner_name }} ({{ $room->is_flexible ? 'Flexible' : 'Private' }} Office)</p>
            @endif
            <p style="margin: 5px 0 0; color: #888;">Section: {{ $room->section->name }} | Floor: {{ $room->floor == 0 ? 'E' : $room->floor }} | Capacity: {{ $room->capacity }}</p>
        </div>
    </div>

    <div class="grid">
        <div class="info-section">
            <h3>Room Information</h3>
            <div class="info-row">
                <span class="info-label">Capacity:</span>
                <span>{{ $room->capacity }} Persons</span>
            </div>
            <div class="info-row">
                <span class="info-label">Features:</span>
                <div>
                    @forelse($room->features as $feature)
                        <span class="feature-tag">{{ $feature->name }} {{ $feature->pivot->value ? '('.$feature->pivot->value.')' : '' }}</span>
                    @empty
                        <span style="color: #999;">None</span>
                    @endforelse
                </div>
            </div>
            <div class="info-row">
                <span class="info-label">Permanent Owners:</span>
                <div>
                    @forelse($room->owners as $owner)
                        <div style="font-size: 14px;">{{ $owner->name }}</div>
                    @empty
                        <span style="color: #999;">None</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Current Status</h3>
            @if($activeBookings->isEmpty())
                <p style="color: #28a745; font-weight: bold;">Room is currently empty.</p>
            @else
                <ul class="booking-list">
                    @foreach($activeBookings as $booking)
                        <li class="booking-item {{ $booking->user_id === Auth::id() ? 'mine' : '' }}">
                            <span class="booking-time">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</span>
                            <span class="booking-user">Booked by: {{ $booking->user->name }}</span>
                            @if($booking->reason)
                                <div style="font-size: 12px; color: #777; margin-top: 5px;">Reason: {{ $booking->reason }}</div>
                            @endif

                            @if($booking->user_id === Auth::id() || Auth::user()->is_admin)
                                <div style="margin-top: 10px; display: flex; gap: 5px;">
                                    <form id="extend-form-{{ $booking->id }}" action="{{ route('bookings.extend', $booking->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        <input type="hidden" name="minutes" id="extend-minutes-{{ $booking->id }}" value="60">
                                    </form>
                                    <button type="button" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="extendBooking({{ $booking->id }})">Extend</button>

                                    <form action="{{ route('bookings.end', $booking->id) }}" method="POST"
                                          onsubmit="return {{ Auth::user()->is_admin && $booking->user_id !== Auth::id() ? 'confirm(\'Admin: End this booking for '.$booking->user->name.' early?\')' : 'confirm(\'End this booking early?\')' }}">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">End Early</button>
                                    </form>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if($room->isFull() && $whenEmptyAgain)
                    <div class="empty-status">
                        Available again at: {{ $whenEmptyAgain->format('H:i') }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if(!$futureBookings->isEmpty())
        <div class="info-section">
            <h3>Future Bookings</h3>
            <ul class="booking-list">
                @foreach($futureBookings as $booking)
                    <li class="booking-item {{ $booking->user_id === Auth::id() ? 'mine' : '' }}">
                        <span class="booking-time">{{ $booking->start_time->format('D, d.m. H:i') }} - {{ $booking->end_time->format('H:i') }}</span>
                        <span class="booking-user">Booked by: {{ $booking->user->name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="availability-window">
        <div class="week-nav">
            <div style="flex-grow: 1;">
                <h3 style="margin: 0;">Availability Grid</h3>
                <span style="font-size: 14px; color: #666;">KW {{ $startDate->format('W') }} ({{ $startDate->format('d.m.') }} - {{ $startDate->copy()->addDays(5)->format('d.m.Y') }})</span>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('rooms.show', [$room->id, 'week' => $weekOffset - 1]) }}" class="btn btn-secondary {{ $weekOffset <= 0 ? 'disabled' : '' }}" style="{{ $weekOffset <= 0 ? 'pointer-events: none; opacity: 0.5;' : '' }}">« Previous Week</a>
                <a href="{{ route('rooms.show', [$room->id, 'week' => $weekOffset + 1]) }}" class="btn btn-secondary {{ $weekOffset >= 3 ? 'disabled' : '' }}" style="{{ $weekOffset >= 3 ? 'pointer-events: none; opacity: 0.5;' : '' }}">Next Week »</a>
            </div>
        </div>
        <div class="availability-grid">
            <div class="grid-header">Time</div>
            @for($i = 0; $i < 6; $i++)
                @php
                    $date = $startDate->copy()->addDays($i);
                    $isToday = $date->isToday();
                @endphp
                <div class="grid-header" style="{{ $isToday ? 'background: #e7f3ff; color: #007bff;' : '' }}">
                    {{ $date->format('D') }}<br>
                    <span style="font-size: 10px; font-weight: normal;">{{ $date->format('d.m.') }}</span>
                </div>
            @endfor

            @for($hour = 7; $hour <= 20; $hour++)
                <div class="grid-time-col">{{ sprintf('%02d:00', $hour) }}</div>
                @for($day = 0; $day < 6; $day++)
                    @php
                        $slotStart = $startDate->copy()->addDays($day)->hour($hour)->minute(0)->second(0);
                        $slotEnd = $slotStart->copy()->addHour();

                        $bookingsInSlot = $sevenDaysBookings->filter(function($b) use ($slotStart, $slotEnd) {
                            return $b->start_time < $slotEnd && $b->end_time > $slotStart;
                        });

                        $class = '';
                        $title = '';
                        $label = '';
                        $isPast = $slotStart->isPast();
                        $isToday = $slotStart->isToday();
                        $canBook = !$room->hasPermaBooking() && !$isPast;

                        if ($bookingsInSlot->isNotEmpty()) {
                            $isMine = $bookingsInSlot->contains('user_id', Auth::id());
                            $isFull = $bookingsInSlot->count() >= $room->capacity;

                            if ($isMine) {
                                $class = 'mine';
                            } elseif ($isFull) {
                                $class = 'booked';
                                $canBook = false;
                            } else {
                                $class = 'partially-booked';
                            }

                            $names = $bookingsInSlot->map(fn($b) => $b->user->name)->unique()->implode(', ');
                            $title = "Booked by: " . $names . ($isFull ? " (Full)" : " (" . $bookingsInSlot->count() . "/" . $room->capacity . ")");

                            if ($bookingsInSlot->count() === 1) {
                                $label = \Illuminate\Support\Str::limit($bookingsInSlot->first()->user->name, 10);
                            } else {
                                $label = "Booked (" . $bookingsInSlot->count() . ")";
                            }
                        }

                        if ($isPast) $class .= ' past';
                        if ($isToday) $class .= ' today';
                    @endphp
                    <div class="grid-cell {{ $class }}" title="{{ $title }}">
                        @if($canBook)
                            <a href="{{ route('bookings.create', [$room->id, 'start_time' => $slotStart->format('Y-m-d H:i')]) }}" title="Book at this time"></a>
                        @endif
                        @if($label)
                            <span class="grid-cell-label">{{ $label }}</span>
                        @endif
                    </div>
                @endfor
            @endfor
        </div>
    </div>

    <div class="action-btns">
        <a href="{{ route('home', ['section_id' => $room->section_id, 'floor' => $room->floor, 'edit_mode' => request('edit_mode')]) }}" class="btn btn-secondary">← Back to Hallway</a>

        @if(!$room->hasPermaBooking() && !$room->isFull())
            <a href="{{ route('bookings.create', $room->id) }}" class="btn btn-success">Book Room</a>
        @endif

        @if(Auth::user()->is_admin)
            <a href="{{ route('rooms.edit', [$room->id, 'edit_mode' => request('edit_mode')]) }}" class="btn btn-primary">Edit Room (Admin)</a>
        @endif
    </div>
</div>

@php
    $msg = session('error') ?? session('success');
    $isError = session()->has('error');
@endphp
@if($msg)
    <div id="toast" style="position: fixed; bottom: 30px; right: 30px; background: {{ $isError ? '#ff6b6b' : '#40c057' }}; color: white; padding: 15px 25px; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 20000;">
        {{ $msg }}
    </div>
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if(toast) toast.style.display = 'none';
        }, 3000);
    </script>
@endif

    <script>
    function extendBooking(id) {
        let minutes = prompt("How many minutes would you like to extend? (e.g. 30, 60, 120)", "60");
        if (minutes != null && minutes !== "") {
            document.getElementById('extend-minutes-' + id).value = minutes;
            document.getElementById('extend-form-' + id).submit();
        }
    }
    </script>
</body>
</html>
