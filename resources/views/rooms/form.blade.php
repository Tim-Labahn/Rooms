<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($room) ? 'Edit Room' : 'Add Room' }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; margin-right: 10px; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #ff6b6b; color: white; }
        .btn-danger:hover { background: #d64545; }
        .actions { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .error { color: #ff6b6b; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>{{ isset($room) ? 'Edit Room: ' . $room->name : 'Add New Room' }}</h2>

    <form action="{{ isset($room) ? route('rooms.update', $room->id) : route('rooms.store') }}" method="POST">
        @csrf
        @if(isset($room))
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="name">Room Name / Number</label>
            <input type="text" name="name" id="name" value="{{ old('name', $room->name ?? '') }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="owner_name">Assigned Occupant (Optional)</label>
            <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name', $room->owner_name ?? '') }}" placeholder="e.g. Fritz Meier">
            @error('owner_name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px; background: #f8f9fa; padding: 10px; border-radius: 6px;">
            <input type="hidden" name="is_flexible" value="0">
            <input type="checkbox" name="is_flexible" id="is_flexible" value="1" {{ old('is_flexible', $room->is_flexible ?? true) ? 'checked' : '' }} style="width: auto;">
            <label for="is_flexible" style="margin: 0;">Flexible Workplace (Bookable by others when owner is absent)</label>
        </div>

        <div class="form-group">
            <label for="floor">Floor</label>
            <input type="number" name="floor" id="floor" value="{{ old('floor', $room->floor ?? $defaultFloor ?? '1') }}" required>
            @error('floor') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="section_id">Section</label>
            <select name="section_id" id="section_id" required>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ (old('section_id', $room->section_id ?? $defaultSectionId ?? '') == $section->id) ? 'selected' : '' }}>
                        {{ $section->name }} ({{ $section->building->name ?? 'No Building' }})
                    </option>
                @endforeach
            </select>
            @error('section_id') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="capacity">Capacity (Persons)</label>
            <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $room->capacity ?? '1') }}" min="1">
            @error('capacity') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="available" {{ old('status', $room->status ?? 'available') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="out_of_order" {{ old('status', $room->status ?? 'available') == 'out_of_order' ? 'selected' : '' }}>Out of Order / Non-bookable</option>
            </select>
            @error('status') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Features</label>
            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 6px;">
                @foreach($features as $feature)
                    @if($feature->name === 'Capacity') @continue @endif
                    @php
                        $pivot = isset($room) ? $room->features->where('id', $feature->id)->first() : null;
                        $isChecked = collect(old('feature_ids', $pivot ? [$feature->id] : []))->contains($feature->id);
                        $value = old('feature_values.'.$feature->id, $pivot ? $pivot->pivot->value : '');
                    @endphp
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                        <input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}" {{ $isChecked ? 'checked' : '' }} style="width: auto;">
                        <span style="flex: 1; font-size: 14px;">{{ $feature->name }}</span>
                        <input type="text" name="feature_values[{{ $feature->id }}]" placeholder="Value" value="{{ $value }}" style="width: 80px; padding: 5px;">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label for="owner_ids">Permanent Owners</label>
            <select name="owner_ids[]" id="owner_ids" multiple style="height: 100px;">
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ (collect(old('owner_ids', isset($room) ? $room->owners->pluck('id')->toArray() : []))->contains($user->id)) ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="actions">
            <div>
                <a href="{{ route('home', ['section_id' => $room->section_id ?? $defaultSectionId ?? null, 'floor' => $room->floor ?? $defaultFloor ?? null]) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ isset($room) ? 'Update Room' : 'Create Room' }}</button>
            </div>
        </div>
    </form>

    @if(isset($room))
        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="color: #ff6b6b;">Danger Zone</label>
                    <button type="submit" class="btn btn-danger">Delete Room</button>
                </div>
            </form>
        </div>
    @endif
</div>

</body>
</html>
