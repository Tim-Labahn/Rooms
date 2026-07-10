{{--
    Quick Book Overlay
    - Provides a simplified flow for creating a new booking.
    - Suggests available rooms.
--}}
@if($quickBook)
<div id="quick-book-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; display: flex; justify-content: center; align-items: center; backdrop-filter: blur(8px);">
    <div style="background: white; padding: 40px; border-radius: 20px; width: 500px; box-shadow: 0 25px 60px rgba(0,0,0,0.4); position: relative; max-height: 80vh; overflow-y: auto;">
        <a href="{{ route('home') }}" style="position: absolute; top: 20px; right: 25px; font-size: 30px; text-decoration: none; color: #adb5bd; transition: color 0.2s;" onmouseover="this.style.color='#333'" onmouseout="this.style.color='#adb5bd'">&times;</a>

        <h2 style="margin-top: 0; font-size: 26px; color: #212529; border-bottom: 2px solid #f8f9fa; padding-bottom: 15px; margin-bottom: 25px;">Quick Room Booking</h2>

        <p style="color: #666; font-size: 15px; margin-bottom: 20px;">Choose an available room to book it immediately for the next hour.</p>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($rooms_all as $room)
                <div style="display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 15px 20px; border-radius: 12px; border: 1px solid #eee; transition: all 0.2s;" onmouseover="this.style.borderColor='#007bff'; this.style.background='#fff'; this.style.boxShadow='0 4px 12px rgba(0,123,255,0.08)'" onmouseout="this.style.borderColor='#eee'; this.style.background='#f8f9fa'; this.style.boxShadow='none'">
                    <div>
                        <strong style="display: block; font-size: 16px;">{{ $room->name ?? 'Room' }}</strong>
                        <span style="font-size: 12px; color: #888;">Floor {{ $room->floor == 0 ? 'E' : $room->floor }} | {{ $room->section->name }}</span>
                    </div>
                    <a href="{{ route('bookings.create', $room->id) }}" class="search-btn" style="text-decoration: none; font-size: 14px; padding: 10px 18px;">Book Now</a>
                </div>
            @empty
                <p style="text-align: center; color: #adb5bd; padding: 20px;">No rooms available for immediate booking.</p>
            @endforelse
        </div>

        <div style="margin-top: 30px; text-align: center; border-top: 1px solid #f8f9fa; padding-top: 20px;">
            <p style="font-size: 13px; color: #adb5bd;">Can't find your room? Use the main search or browse the building sections.</p>
        </div>
    </div>
</div>
@endif
