<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room {{ $room->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 450px; }
        h2 { margin-top: 0; color: #333; }
        .room-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #007bff; }
        .room-info h3 { margin-top: 0; font-size: 18px; }
        .room-info p { margin: 5px 0; font-size: 14px; color: #555; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; width: 100%; }
        .btn-secondary { background: #6c757d; color: white; margin-top: 10px; display: block; text-align: center; }
        .error { color: #ff6b6b; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Book {{ $room->name ?? 'Room' }} ({{ $room->number }})</h2>

    <div class="room-info">
        <h3>Room Details</h3>
        <p><strong>Capacity:</strong> {{ $room->capacity ?? 'N/A' }}</p>
        <p><strong>Features:</strong>
            @if($room->features->count() > 0)
                {{ $room->features->map(fn($f) => $f->name . ($f->pivot->value ? ' (' . $f->pivot->value . ')' : ''))->implode(', ') }}
            @else
                None
            @endif
        </p>
        <p><strong>Owners:</strong>
            @if($room->owners->count() > 0)
                {{ $room->owners->pluck('name')->implode(', ') }}
            @else
                None
            @endif
        </p>
    </div>

    @if(session('error'))
        <div class="error" style="background: #fff5f5; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    @php
        $now = now();
        $minute = (int)$now->format('i');
        $roundMinute = ceil($minute / 5) * 5;
        $defaultStartTime = $now->copy()->setMinute(0)->setSecond(0)->addMinutes($roundMinute);

        if (isset($startTime) && !empty($startTime)) {
            try {
                $defaultStartTime = \Carbon\Carbon::parse($startTime);
            } catch (\Exception $e) {}
        }

        $defaultEndTime = $defaultStartTime->copy()->addHour();
    @endphp

    <form action="{{ route('bookings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="room_id" value="{{ $room->id }}">

        <div class="form-group" style="display: flex; align-items: center; gap: 10px; background: #e7f3ff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <input type="checkbox" name="book_day" id="book_day" value="1" style="width: auto;" {{ old('book_day') ? 'checked' : '' }}>
            <label for="book_day" style="margin: 0; cursor: pointer;">Book for the whole day (07:00 - 21:00)</label>
        </div>

        <div id="date-group" class="form-group" style="display: {{ old('book_day') ? 'block' : 'none' }};">
            <label for="booking_date">Select Date</label>
            <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date', $defaultStartTime->format('Y-m-d')) }}">
            @error('booking_date') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div id="time-range-group" style="display: {{ old('book_day') ? 'none' : 'block' }};">
            <div class="form-group">
                <label for="start_time">From</label>
                <input type="datetime-local" name="start_time" id="start_time" value="{{ old('start_time', $defaultStartTime->format('Y-m-d\TH:i')) }}" {{ old('book_day') ? '' : 'required' }}>
                @error('start_time') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="end_time">To</label>
                <input type="datetime-local" name="end_time" id="end_time" value="{{ old('end_time', $defaultEndTime->format('Y-m-d\TH:i')) }}" {{ old('book_day') ? '' : 'required' }}>
                @error('end_time') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="reason">Reason (optional)</label>
            <textarea name="reason" id="reason" placeholder="Why do you need this room? (Default: used for working)">{{ old('reason') }}</textarea>
            @error('reason') <div class="error">{{ $message }}</div> @enderror
        </div>

        @if(session('conflict_warning'))
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ffeeba; font-size: 14px;">
                <div style="display: flex; align-items: flex-start; gap: 10px;">
                    <span style="font-size: 20px;">⚠️</span>
                    <div>
                        <strong>Wait a moment!</strong><br>
                        {{ session('conflict_warning') }}
                        <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="force_booking" value="1" id="force_booking" style="width: auto;" required>
                            <label for="force_booking" style="margin: 0; font-weight: normal; cursor: pointer;">Yes, I am sure and want to continue.</label>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="btn btn-primary">Confirm Booking</button>
    </form>

    <script>
        document.getElementById('book_day').addEventListener('change', function() {
            const isDay = this.checked;
            document.getElementById('time-range-group').style.display = isDay ? 'none' : 'block';
            document.getElementById('date-group').style.display = isDay ? 'block' : 'none';

            document.getElementById('start_time').required = !isDay;
            document.getElementById('end_time').required = !isDay;
            document.getElementById('booking_date').required = isDay;
        });
    </script>

    <a href="{{ route('home', ['section_id' => $room->section_id, 'floor' => $room->floor]) }}" class="btn btn-secondary">Cancel</a>
</div>

</body>
</html>
