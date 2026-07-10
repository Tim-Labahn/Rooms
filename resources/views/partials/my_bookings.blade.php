{{--
    My Bookings Sidebar Component
    - Displays active and future bookings for the logged-in user.
--}}
<div class="top-left-nav" style="position: fixed; top: 20px; left: 20px; z-index: 1002; display: flex; gap: 10px;">
    <div onclick="toggleBookings()" title="View My Bookings" style="background: white; width: 45px; height: 45px; border-radius: 50%; display: flex; justify-content: center; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; font-size: 22px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        📅
    </div>
    <div onclick="window.location.href='?quick_book=1'" title="Fast Book" style="background: white; width: 45px; height: 45px; border-radius: 50%; display: flex; justify-content: center; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; font-size: 22px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        ⚡
    </div>
</div>

<div class="top-left" id="bookings-panel" style="display: none; top: 80px; left: 20px; position: fixed; z-index: 1001; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); width: 320px; max-height: 70vh; overflow-y: auto;">
    <div style="position: absolute; top: 15px; right: 20px; cursor: pointer; font-size: 24px; color: #adb5bd;" onclick="toggleBookings()">&times;</div>

    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #333; border-bottom: 2px solid #f8f9fa; padding-bottom: 10px;">My Bookings</h3>

    @if($currentBookings->isEmpty())
        <p style="font-size: 14px; color: #adb5bd; text-align: center; padding: 20px 0;">No active or future bookings found.</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($currentBookings as $booking)
                @php $isActive = $booking->start_time <= now(); @endphp
                <div style="background: #f8f9fa; padding: 15px; border-radius: 12px; border: 1px solid #eee; position: relative; border-left: 4px solid {{ $isActive ? '#28a745' : '#007bff' }};">
                    <a href="{{ route('rooms.show', $booking->room->id) }}" style="text-decoration: none; color: inherit;">
                        <strong style="display: block; font-size: 15px; margin-bottom: 5px;">{{ $booking->room->name ?? 'Room' }} {{ $booking->room->number }}</strong>
                        <div style="font-size: 12px; color: {{ $isActive ? '#28a745' : '#007bff' }}; font-weight: 600;">
                            {{ $isActive ? 'Active until' : 'Starts' }}: {{ $isActive ? $booking->end_time->format('H:i') : $booking->start_time->format('D, H:i') }}
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    function toggleBookings() {
        const panel = document.getElementById('bookings-panel');
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }
</script>
