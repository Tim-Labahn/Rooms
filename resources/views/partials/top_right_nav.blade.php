@auth
<style>
    .logout-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
        transition: opacity 0.2s;
    }
    .logout-btn:hover {
        opacity: 0.9;
        color: white;
    }
</style>
<div class="top-right" style="position: fixed; top: 20px; right: 20px; z-index: 1001; display: flex; flex-direction: row; gap: 10px; align-items: center;">
    @php
        $isEditingOrBooking = Route::is('rooms.edit', 'rooms.create', 'bookings.create');
    @endphp

    @if(Auth::user()->is_admin)
        @php
            $currentEditMode = request('edit_mode') == '1';
        @endphp

        @if(!isset($room) && !$isEditingOrBooking)
            <a href="{{ route('admin.overview') }}" class="logout-btn" style="background: #17a2b8; text-decoration: none;">Stats</a>
            <a href="{{ request()->fullUrlWithQuery(['edit_mode' => $currentEditMode ? '0' : '1']) }}"
               class="logout-btn" style="background: {{ $currentEditMode ? '#ffc107' : '#6c757d' }}; text-decoration: none; color: {{ $currentEditMode ? '#000' : '#fff' }};">
                {{ $currentEditMode ? 'Exit Edit Mode' : 'Edit' }}
            </a>
        @elseif(isset($room) && !$isEditingOrBooking)
            <a href="{{ request()->fullUrlWithQuery(['edit_mode' => $currentEditMode ? '0' : '1']) }}"
               class="logout-btn" style="background: {{ $currentEditMode ? '#ffc107' : '#6c757d' }}; text-decoration: none; color: {{ $currentEditMode ? '#000' : '#fff' }};">
                {{ $currentEditMode ? 'Exit Edit Mode' : 'Edit' }}
            </a>
        @endif
    @endif

    @if(!$isEditingOrBooking)
        <a href="{{ route('user.profile') }}" class="logout-btn" style="background: #28a745; text-decoration: none;">Profile</a>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    @endif
</div>
@endauth
