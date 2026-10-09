<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Jasa - BANTULOKAL HUB</title>

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
            gap: 16px;
            flex-wrap: wrap;
            padding: 20px 6%;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        header strong { color: var(--accent); }

        button, select, a {
            font: inherit;
        }

        button, .button {
            display: inline-block;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
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
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-top: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            color: var(--text);
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 750px;
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
        }

        .pagination {
            margin-top: 20px;
        }

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
            border-color: var(--accent);
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

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a class="button" href="{{ route('admin.dashboard') }}">Dashboard</a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </header>

    <main>
        <h1>Permintaan Jasa</h1>
        <p class="muted">
            Tinjau permintaan pelanggan sebelum menyetujui atau menolaknya.
        </p>

        <section class="panel">
            <div class="toolbar">
                <div>
                    <strong>{{ $requests->total() }}</strong>
                    <span class="muted"> total permintaan</span>
                </div>

                <form class="filter" method="GET" action="{{ route('admin.service-requests.index') }}">
                    <label for="status">Filter status:</label>

                    <select name="status" id="status">
                        <option value="">Semua status</option>
                        @foreach (['pending', 'approved', 'rejected', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit">Filter</button>
                </form>
            </div>

            <div class="table-wrap" style="margin-top:22px;">
                <table>
                    <thead>
                        <tr>
                            <th>ID / Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Pekerja</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Detail</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($requests as $serviceRequest)
                            <tr>
                                <td>
                                    <strong>#{{ $serviceRequest->id }}</strong>
                                    <br>
                                    <span class="muted">
                                        {{ $serviceRequest->created_at->format('d M Y') }}
                                    </span>
                                </td>

                                <td>
                                    {{ $serviceRequest->customer_name }}
                                    <br>
                                    <span class="muted">{{ $serviceRequest->customer_email }}</span>
                                </td>

                                <td>{{ $serviceRequest->category?->name ?? '-' }}</td>

                                <td>{{ $serviceRequest->worker_count }}</td>

                                <td><span class="status">{{ $serviceRequest->status }}</span></td>

                                <td>{{ $serviceRequest->payment_status }}</td>

                                <td>
                                    <a class="button" href="{{ route('admin.service-requests.show', $serviceRequest) }}">
                                        Buka
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="muted">
                                    Belum ada permintaan dengan status ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $requests->links() }}
            </div>
        </section>
    </main>
</body>
</html>
