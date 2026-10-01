# Rooms - Simple Room Booking Service

## Project Summary
A Laravel-based application for booking office rooms. It provides a simple list-based interface to browse buildings and sections, view room availability, and manage bookings.

## Key Features
- **Building and Section Selection**: Browse HQ East and Tech Hub West, each with three sections.
- **Floor Navigation**: Switch between the elevator floor and floors 1 through 4.
- **Room Directory**: View color-coded room states, owner names, capacities, and facilities such as smart boards, tables, couches, and projectors. Add reusable custom facilities from the room editor's Other field; similar names are suggested to avoid duplicates.
- **Local Testing**: Add, edit, and remove rooms; changes persist in the current browser only.
- **Demo Accounts and Bookings**: Sign in as one of 12 premade users or create a local test account. Bookings are associated with the signed-in account.
- **Search**: Find rooms by name, owner, or room feature.

## GitHub Pages Room Directory

The root `index.html` is a static room directory for GitHub Pages at `https://rooms.timlabahn.de/`. New browser profiles get a randomized 6–12 rooms per section on each floor, with facilities randomly assigned from the preset list. Twelve premade company demo users have varied sample bookings; all use the password `password`. The default account is `admin@rooms.test`. Users can also create test accounts with their own passwords. To show the directory inside a WordPress page, add a Custom HTML block containing:

```html
<iframe src="https://rooms.timlabahn.de/" title="Room directory" style="width:100%; min-height:720px; border:0"></iframe>
```

Room, feature, account, and booking changes on this static page are saved in the current browser's local storage. Custom feature names are added to the reusable room feature list when their room is saved; the Other field suggests similar existing names. Bookings are shown only for the signed-in account. This is a local-only demo sign-in, not a Laravel account or real authentication. Data persists across reloads in that browser, but is not shared with other browsers or devices. Clearing that browser's site data removes it. This Pages directory is independent of the Laravel app below; GitHub Pages cannot run the Laravel backend or its database. The GitHub Pages source must publish the repository root containing `index.html`; the live URL will keep showing the README until these changes are pushed to that source and Pages finishes deploying.

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
