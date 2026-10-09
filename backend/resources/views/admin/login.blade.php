<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — BANTULOKAL HUB</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f6f2eb;
            color: #292820;
            font-family: system-ui, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 36px;
            background: #fff;
            border: 1px solid #e6e0d6;
            border-radius: 16px;
            box-shadow: 0 16px 45px #29282012;
        }

        .brand {
            margin: 0 0 8px;
            color: #a64520;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        h1 { margin: 0 0 10px; font-size: 28px; }

        .subtitle {
            margin: 0 0 28px;
            color: #716e65;
            line-height: 1.6;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-size: 14px;
            font-weight: 650;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8d2c8;
            border-radius: 8px;
            font: inherit;
        }

        input:focus {
            outline: 2px solid #a64520;
            outline-offset: 2px;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #a64520;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover { background: #883719; }

        .error {
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            background: #fff0ed;
            color: #922b18;
            font-size: 14px;
        }

        .footer {
            margin: 24px 0 0;
            color: #817d73;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="login-card">
        <p class="brand">BANTULOKAL HUB</p>
        <h1>Admin Login</h1>
        <p class="subtitle">
            Masuk untuk mengelola permintaan jasa, freelancer, dan penyewaan alat.
        </p>

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <label for="email">Email administrator</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Kata sandi</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >

            @if ($errors->any())
                <div class="error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit">Masuk ke dashboard</button>
        </form>

        <p class="footer">Akses khusus administrator.</p>
    </main>
</body>
</html>
