<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Rooms</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            height: calc(100vh - 120px);
            overflow: hidden;
        }
        .profile-card, .bookings-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .bookings-card {
            overflow: hidden;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .save-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }
        .save-btn:hover {
            background: #0056b3;
        }
        .bookings-list {
            flex-grow: 1;
            overflow-y: auto;
            margin-top: 20px;
        }
        .booking-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .booking-item:last-child {
            border-bottom: none;
        }
        .booking-info h4 {
            margin: 0;
            font-size: 16px;
        }
        .booking-info p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #777;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-upcoming { background: #e7f5ff; color: #1971c2; }
        .status-past { background: #f1f3f5; color: #495057; }
        .status-active { background: #ebfbee; color: #2b8a3e; }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
                height: auto;
                overflow: visible;
                padding-bottom: 40px;
            }
            .profile-card, .bookings-card {
                height: auto;
            }
        }
    </style>
</head>
<body>
    @include('partials.top_right_nav')

    <div class="main-content" style="padding-top: 100px;">
        <div class="rooms-header">
            <a href="{{ route('home') }}" class="back-btn">← Back to Map</a>
            <div class="header-titles">
                <h1>My Profile</h1>
            </div>
        </div>

        <div class="profile-container">
            <div class="profile-card">
                <h3>Edit Information</h3>
                <form action="{{ route('user.update') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email (cannot be changed)</label>
                        <input type="email" value="{{ $user->email }}" disabled style="background: #f8f9fa;">
                    </div>
                    <div class="form-group">
                        <label>New Password (optional)</label>
                        <input type="password" name="password" placeholder="Min 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation">
                    </div>
                    <button type="submit" class="save-btn">Update Profile</button>
                </form>

                @if($errors->any())
                    <div style="margin-top: 20px; color: #fa5252; font-size: 14px;">
                        @foreach($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bookings-card">
                <h3>My Bookings</h3>
                <div class="bookings-list">
                    @forelse($bookings as $booking)
                        @php
                            $isPast = $booking->end_time->isPast();
                            $isActive = $booking->start_time->isPast() && $booking->end_time->isFuture();
                            $statusLabel = $isActive ? 'Active' : ($isPast ? 'Past' : 'Upcoming');
                            $statusClass = $isActive ? 'status-active' : ($isPast ? 'status-past' : 'status-upcoming');
                        @endphp
                        <div class="booking-item">
                            <div class="booking-info">
                                <h4>{{ $booking->room->name }}</h4>
                                <p>{{ $booking->room->section->building->name }} - {{ $booking->room->section->name }}</p>
                                <p>{{ $booking->start_time->format('M d, H:i') }} - {{ $booking->end_time->format('H:i') }}</p>
                            </div>
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    @empty
                        <p style="text-align: center; color: #999; margin-top: 40px;">No bookings found.</p>
                    @endforelse
                </div>
                <div style="margin-top: 20px;">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
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
            setTimeout(() => { document.getElementById('toast').style.display = 'none'; }, 3000);
        </script>
    @endif
</body>
</html>
