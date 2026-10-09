<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Admin - BANTULOKAL HUB</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --background: #f6f2eb;
            --surface: #fff;
            --text: #292820;
            --muted: #716e65;
            --accent: #a64520;
            --border: #e6e0d6;
            --accent-soft: #f7e9df;
            --success: #216e45;
            --danger: #a12626;
        }
        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: system-ui, sans-serif;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 20px 6%;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }
        header strong { color: var(--accent); }
        a { color: var(--accent); }
        .header-links { display: flex; align-items: center; gap: 16px; }
        main { max-width: 1150px; margin: 36px auto; padding: 0 22px 48px; }
        h1 { margin-bottom: 8px; font-size: 30px; }
        h2 { font-size: 21px; margin-top: 0; }
        .muted { color: var(--muted); line-height: 1.6; }
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        label { display: block; font-weight: 600; margin-bottom: 7px; }
        input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            background: #fff;
        }
        button, .button {
            display: inline-block;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            background: #fff;
            color: var(--text);
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }
        button:hover, .button:hover { background: var(--accent-soft); }
        .primary { background: var(--accent); color: #fff; border-color: var(--accent); }
        .primary:hover { background: #873919; color: #fff; }
        .danger { color: var(--danger); }
        .success-message {
            background: #eaf6ee; color: var(--success);
            padding: 13px 15px; border-radius: 8px; margin-top: 18px;
        }
        .error-message {
            background: #fff0ef; color: var(--danger);
            padding: 13px 15px; border-radius: 8px; margin-top: 18px;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left; padding: 13px 10px;
            border-bottom: 1px solid var(--border); vertical-align: top;
        }
        th { color: var(--muted); font-size: 13px; }
        .status {
            display: inline-block; padding: 4px 9px;
            border-radius: 999px; font-size: 12px; font-weight: 700;
        }
        .active { background: #eaf6ee; color: var(--success); }
        .inactive { background: #fff0ef; color: var(--danger); }
        .inline-form { display: inline; }
        .actions { white-space: nowrap; }
        .pagination { margin-top: 18px; }
        .pagination nav { display: flex; justify-content: space-between; gap: 12px; }
        .pagination p { color: var(--muted); }
        details summary { cursor: pointer; color: var(--accent); }
        pre { white-space: pre-wrap; overflow-wrap: anywhere; }
        @media (max-width: 600px) {
            .panel { padding: 16px; }
            header { padding: 16px 5%; }
            h1 { font-size: 25px; }
        }
    </style>
</head>
<body>
<header>
    <strong>BANTULOKAL HUB · ADMIN</strong>
    <div class="header-links">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span>{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
</header>

<main>
    <h1>Manajemen Administrator</h1>
    <p class="muted">Kelola akses administrator dan tinjau riwayat perubahan akun.</p>

    @if (session('success'))
        <div class="success-message" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="error-message" role="alert">
            <strong>Perubahan tidak dapat diproses:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel">
        <h2>Buat Administrator</h2>
        <p class="muted">Gunakan alamat email unik dan kata sandi minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</p>

        <form method="POST" action="{{ route('admin.admins.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                </div>
                <div>
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" required minlength="12" autocomplete="new-password">
                </div>
                <div>
                    <label for="password_confirmation">Konfirmasi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password">
                </div>
            </div>
            <p><button class="primary" type="submit">Buat Administrator</button></p>
        </form>
    </section>

    <section class="panel">
        <h2>Daftar Administrator</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($admins as $admin)
                    <tr>
                        <td>
                            {{ $admin->name }}
                            @if ($admin->id === auth()->id())
                                <span class="muted">(Anda)</span>
                            @endif
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            @if ($admin->is_active)
                                <span class="status active">Aktif</span>
                            @else
                                <span class="status inactive">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ $admin->created_at?->format('d M Y H:i') }}</td>
                        <td class="actions">
                            @if ($admin->id === auth()->id())
                                <span class="muted">Akun sendiri</span>
                            @else
                                <form class="inline-form" method="POST" action="{{ route('admin.admins.update-status', $admin) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $admin->is_active ? '0' : '1' }}">
                                    <button type="submit">
                                        {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada administrator.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $admins->links() }}</div>
    </section>

    <section class="panel">
        <h2>Riwayat Aktivitas Administrator</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pelaku</th>
                    <th>Aksi</th>
                    <th>Akun sasaran</th>
                    <th>Detail</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($auditLogs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td>
                            {{ $log->actor?->name ?? 'Akun dihapus' }}
                            @if ($log->actor)
                                <div class="muted">{{ $log->actor->email }}</div>
                            @endif
                        </td>
                        <td>{{ $log->action }}</td>
                        <td>
                            {{ $log->target?->name ?? 'Akun tidak tersedia' }}
                            @if ($log->target)
                                <div class="muted">{{ $log->target->email }}</div>
                            @endif
                        </td>
                        <td>
                            <details>
                                <summary>Lihat detail</summary>
                                <pre>{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                <div class="muted">IP: {{ $log->ip_address ?? 'Tidak tersedia' }}</div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada aktivitas administrator.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $auditLogs->links() }}</div>
    </section>
</main>
</body>
</html>
