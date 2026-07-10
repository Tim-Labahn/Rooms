<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - Simple Booking</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>

    @include('partials.my_bookings')
    @include('partials.search')
    @include('partials.quick_book')
    @include('partials.top_right_nav')
    @include('partials.legend')

    <div class="main-content">
        @if(!$selectedBuilding)
            <div style="text-align: center; padding: 100px;">
                <h1>No Buildings Found</h1>
                <p>Please run the seeder or add a building in the database.</p>
            </div>
        @elseif(!$selectedSection)
            {{-- SECTIONS SELECTION VIEW --}}
            <div class="sections-container">
                <div class="sections-grid">
                    @foreach($selectedBuilding->sections as $section)
                        <a href="{{ route('home', ['section_id' => $section->id, 'edit_mode' => request('edit_mode')]) }}" class="section-block">
                            <div class="section-name">{{ $section->name }}</div>
                            <div class="section-info">{{ $section->rooms->count() }} Rooms</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            {{-- ROOMS VIEW WITH ELEVATOR --}}
            <div class="rooms-container">
                <div class="rooms-header" style="justify-content: space-between; flex-direction: row-reverse;">
                    <div class="header-titles" style="text-align: right;">
                        <h1>{{ $selectedSection->name }}</h1>
                            </div>
                    <a href="{{ route('home') }}" class="back-btn" style="margin-right: 0; margin-left: 20px;">← Areas</a>
                </div>

                <div class="view-layout" style="flex-direction: row-reverse;">
                    {{-- Elevator Component --}}
                    <div class="elevator" style="margin-right: 0; margin-left: 30px; margin-top: 20px; margin-bottom: 20px;">
                        <div class="elevator-shaft">
                            @php
                                $maxFloor = $selectedSection->rooms->max('floor') ?? 3;
                            @endphp
                            @for($f = $maxFloor; $f >= 0; $f--)
                                <a href="{{ route('home', ['section_id' => $selectedSection->id, 'floor' => $f, 'edit_mode' => request('edit_mode')]) }}"
                                   class="floor-btn {{ $selectedFloor == $f ? 'active' : '' }}">
                                    {{ $f == 0 ? 'E' : $f }}
                                </a>
                            @endfor
                        </div>
                    </div>

                    {{-- Rooms Grid --}}
                    <div class="rooms-grid">
                        @if($editMode)
                            <a href="{{ route('rooms.create', ['section_id' => $selectedSection->id, 'floor' => $selectedFloor]) }}" class="room-card available" style="border-style: dashed; background: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                <div class="room-name" style="font-size: 3rem; line-height: 1;">+</div>
                                <div class="room-status">Add Room</div>
                            </a>
                        @endif
                        @forelse($rooms as $room)
                            @php
                                $isOccupied = $room->isOccupied();
                                $isFull = $room->isFull();
                                $status = 'Available';
                                $statusClass = 'available';

                                if ($room->hasPermaBooking()) {
                                    $status = 'Permanent';
                                    $statusClass = 'permanent';
                                } elseif ($isFull) {
                                    $status = 'Full';
                                    $statusClass = 'full';
                                } elseif ($isOccupied) {
                                    $status = 'Occupied';
                                    $statusClass = 'occupied';
                                }
                            @endphp
                            <a href="{{ $editMode ? route('rooms.edit', $room->id) : route('rooms.show', $room->id) }}" class="room-card {{ $statusClass }}">
                                <div class="room-name">{{ $room->name }}</div>
                                @if($room->owner_name)
                                    <div class="room-owner" style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;">👤 {{ $room->owner_name }}</div>
                                @endif
                                <div class="room-status">{{ $status }}</div>
                            </a>
                        @empty
                            <div class="no-rooms">
                                No rooms on this floor.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>

    @php
        $msg = session('error') ?? session('success');
        $isError = session()->has('error');
    @endphp
    @if($msg)
        <div id="toast" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: {{ $isError ? '#ff6b6b' : '#40c057' }}; color: white; padding: 15px 25px; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 20000; width: max-content; max-width: 90vw; text-align: center;">
            {{ $msg }}
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('toast').style.display = 'none';
            }, 3000);
        </script>
    @endif

    <script src="{{ asset('js/home.js') }}"></script>
</body>
</html>
