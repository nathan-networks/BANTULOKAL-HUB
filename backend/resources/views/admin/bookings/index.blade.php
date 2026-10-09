<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Sewa - BANTULOKAL HUB</title>
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
        button, .button {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
            cursor: pointer;
            text-decoration: none;
        }
        button:hover, .button:hover {
            background: var(--accent-soft);
            border-color: var(--accent);
        }
        main {
            max-width: 1200px;
            margin: 42px auto;
            padding: 0 24px;
        }
        .muted { color: var(--muted); line-height: 1.7; }
        .panel {
            margin-top: 24px;
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .filter {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        select {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            font: inherit;
        }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px 12px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }
        th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fcfaf7; }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }
        .pagination { margin-top: 20px; }
        .pagination nav > div:first-child {
            color: var(--muted);
            margin-bottom: 12px;
        }
        .pagination nav > div:last-child {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: white;
            color: var(--text);
            text-decoration: none;
        }
        .pagination [aria-current="page"] span {
            background: var(--accent);
            color: white;
        }
        @media (max-width: 600px) {
            main { margin: 28px auto; padding: 0 16px; }
            .panel { padding: 16px; }
        }
    </style>
</head>
<body>
<header>
    <strong>BANTULOKAL HUB · ADMIN</strong>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="button" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </div>
</header>

<main>
    <h1>Permintaan Sewa Alat</h1>
    <p class="muted">
        Tinjau permintaan pelanggan, konfirmasi ketersediaan alat,
        dan tetapkan biaya sebelum mengirim instruksi pembayaran.
    </p>

    <section class="panel">
        <div class="toolbar">
            <div>
                <strong>{{ $bookings->total() }}</strong>
                <span class="muted"> total permintaan</span>
            </div>
        </div>

        <div class="table-wrap" style="margin-top:22px;">
            <table>
                <thead>
                    <tr>
                        <th>ID / Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Alat</th>
                        <th>Periode Sewa</th>
                        <th>Estimasi Sewa</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                <strong>#{{ $booking->id }}</strong><br>
                                <span class="muted">
                                    {{ $booking->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                                </span>
                            </td>
                            <td>
                                {{ $booking->customer_name }}<br>
                                <span class="muted">{{ $booking->customer_email }}</span>
                            </td>
                            <td>{{ $booking->equipment?->name ?? '-' }}</td>
                            <td>
                                {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
                                – {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td>
                                Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}
                            </td>
                            <td><span class="status">{{ $booking->status }}</span></td>
                            <td><span class="status">{{ $booking->payment_status }}</span></td>
                            <td>
                                <a class="button"
                                   href="{{ route('admin.bookings.show', $booking) }}">
                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="muted">
                                Belum ada permintaan sewa alat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $bookings->links() }}
        </div>
    </section>
</main>
</body>
</html>
