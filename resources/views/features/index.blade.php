<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Features</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 400px; }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; display: flex; gap: 10px; }
        input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        .btn { padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; cursor: pointer; border: none; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #ff6b6b; color: white; padding: 5px 10px; }
        .feature-list { list-style: none; padding: 0; margin-top: 20px; }
        .feature-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .feature-item:last-child { border-bottom: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Room Features</h2>

    <form action="{{ route('features.store') }}" method="POST" class="form-group">
        @csrf
        <input type="text" name="name" placeholder="New feature name..." required>
        <button type="submit" class="btn btn-primary">Add</button>
    </form>

    <ul class="feature-list">
        @foreach($features as $feature)
            <li class="feature-item">
                <form action="{{ route('features.update', $feature->id) }}" method="POST" style="display: flex; flex: 1; gap: 10px;">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $feature->name }}" required style="padding: 5px;">
                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px;">Save</button>
                </form>
                <form action="{{ route('features.destroy', $feature->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="margin-left: 10px;">Delete</button>
                </form>
            </li>
        @endforeach
    </ul>

    <div style="margin-top: 20px;">
        <a href="{{ route('home') }}" class="btn btn-secondary">Back to Home</a>
    </div>
</div>

</body>
</html>
