# Rooms - Simple Room Booking Service

## Project Summary
A Laravel-based application for booking office rooms. It provides a simple list-based interface to browse buildings and sections, view room availability, and manage bookings.

## Key Features
- **Area-Based Selection**: Browse building areas (4 main sections) through an intuitive 2x2 grid.
- **Elevator Navigation**: Easily switch between 4 floors (E, 1, 2, 3) within each area.
- **Auto-Sizing UI**: A "no-scroll" interface that automatically scales rooms and areas to fit the viewport.
- **Booking System**: Users can book rooms for specific times and view their current bookings.
- **Quick Book**: Quickly find and book available rooms for immediate use.
- **Search**: Search for rooms and features.

## File Structure & Organization

### Backend (Laravel)
- `app/Models/Room.php`: Core model for rooms, handles status and bookings.
- `app/Models/Section.php`: Represents building sections.
- `app/Models/Booking.php`: Handles room reservations.
- `app/Http/Controllers/HomeController.php`: Main controller for the room list and search.
- `app/Http/Controllers/RoomController.php`: CRUD operations for rooms and room details.

### Frontend (Blade Partials)
Located in `resources/views/partials/`:
- `my_bookings.blade.php`: User's active and upcoming bookings sidebar.
- `search.blade.php`: Search bar with real-time suggestions.
- `quick_book.blade.php`: Quick booking interface overlay.

### Assets
- `public/css/home.css`: Simplified stylesheet for the room list and sidebars.
- `public/js/home.js`: Basic UI logic for toggles and notifications.

## Legacy Documentation
For a list of previous features and planned ideas that were removed during simplification, see `FEATURES_ARCHIVE.md`.
