# Rooms - Simple Room Booking Service

## Project Summary
A Laravel-based application for booking office rooms. It provides a simple list-based interface to browse buildings and sections, view room availability, and manage bookings.

## Key Features
- **Building and Section Selection**: Browse HQ East and Tech Hub West, each with three sections.
- **Floor Navigation**: Switch between the elevator floor and floors 1 through 4.
- **Room Directory**: View color-coded room states, owner names, and capacities.
- **Local Testing**: Add, edit, and remove rooms; changes persist in the current browser only.
- **Search**: Find rooms by name or owner.

## GitHub Pages Room Directory

The root `index.html` is a static room directory for GitHub Pages at `https://rooms.timlabahn.de/`. Its sample data contains 10 rooms per section on each floor (300 rooms total). To show it inside a WordPress page, add a Custom HTML block containing:

```html
<iframe src="https://rooms.timlabahn.de/" title="Room directory" style="width:100%; min-height:720px; border:0"></iframe>
```

Room changes on this static page are saved in the current browser's local storage. They persist across reloads in that browser, but are not shared with other browsers or devices. Clearing that browser's site data removes them. This Pages directory is independent of the Laravel app below; GitHub Pages cannot run the Laravel backend or its database. The GitHub Pages source must publish the repository root containing `index.html`; the live URL will keep showing the README until these changes are pushed to that source and Pages finishes deploying.

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
