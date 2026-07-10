<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Overview - Rooms</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <style>
        .dashboard { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        .stat-value { font-size: 2.5rem; font-weight: bold; color: #007bff; }
        .stat-label { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .data-sections { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .data-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .data-card h3 { margin-top: 0; border-bottom: 2px solid #f8f9fa; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .reason-tag { display: inline-block; padding: 4px 8px; background: #e7f3ff; color: #007bff; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>
    @include('partials.top_right_nav')

    <div class="dashboard">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Admin Dashboard</h1>
            <a href="{{ route('home') }}" class="back-btn">← Back to Building</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $totalRooms }}</div>
                <div class="stat-label">Total Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $activeBookings }}</div>
                <div class="stat-label">Live Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($occupancyRate, 1) }}%</div>
                <div class="stat-label">Occupancy</div>
            </div>
        </div>

        <div class="data-sections">
            <div class="data-card">
                <h3>Most Popular Booking Reasons</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Reason</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reasons as $reason)
                            <tr>
                                <td><span class="reason-tag">{{ \Illuminate\Support\Str::limit($reason->reason, 40) ?: 'General' }}</span></td>
                                <td><strong>{{ $reason->total }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="data-card">
                <h3>Recently Booked</h3>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Room</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $booking)
                            <tr>
                                <td>{{ $booking->user->name }}</td>
                                <td>{{ $booking->room->name }}</td>
                                <td style="font-size: 0.8rem; color: #666;">{{ $booking->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="data-card" style="margin-top: 30px;">
            <h3>Busiest Rooms (Total Bookings)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                @foreach($busyRooms as $room)
                    <div style="padding: 15px; border: 1px solid #eee; border-radius: 8px;">
                        <strong style="display: block;">{{ $room->name }}</strong>
                        <span style="color: #666; font-size: 0.9rem;">{{ $room->bookings_count }} Bookings</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
