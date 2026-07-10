<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
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
        .footer-link {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9rem;
        }
        .footer-link a {
            color: #4a67ff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Register</h2>

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <input
            type="text"
            name="name"
            placeholder="Full Name"
            value="{{ old('name') }}"
            required
        >

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

        <input
            type="password"
            name="password_confirmation"
            placeholder="Confirm Password"
            required
        >

        @if($errors->any())
            <div class="error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button type="submit">Register</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>
</div>

</body>
</html>
