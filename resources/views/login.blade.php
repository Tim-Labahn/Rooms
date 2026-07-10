<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 300px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #4a67ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #3a55d6;
        }
        .error {
            color: red;
            margin-top: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <input
            type="email"
            name="email"
            placeholder="Email"
            value="{{ old('email') }}"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        @if($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit">Login</button>
    </form>

    <div style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
        Don't have an account? <a href="{{ route('register') }}" style="color: #4a67ff; text-decoration: none;">Register here</a>
    </div>
</div>

</body>
</html>
