
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BANTULOKAL HUB</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --background: #f6f2eb;
            --surface: #ffffff;
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

        header strong {
            color: var(--accent);
            letter-spacing: 0.3px;
        }

        button {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        button:hover {
            background: var(--accent-soft);
            border-color: var(--accent);
        }

        main {
            max-width: 1200px;
            margin: 42px auto;
            padding: 0 24px;
        }

        h1 {
            margin-bottom: 8px;
            font-size: 30px;
            letter-spacing: -0.6px;
        }

        .welcome {
            color: var(--muted);
            line-height: 1.7;
            margin-top: 0;
        }

        .section-title {
            margin: 38px 0 16px;
            font-size: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(185px, 1fr));
            gap: 16px;
            margin-top: 30px;
        }

        .stat-card,
        .module {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .stat-card {
            border-top: 3px solid var(--accent);
        }

        .stat-card p {
            margin: 0 0 14px;
            color: var(--muted);
            line-height: 1.5;
            font-size: 14px;
        }

        .stat-card strong {
            display: block;
            color: var(--accent);
            font-size: 32px;
            line-height: 1.2;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .module h2 {
            margin-top: 0;
            font-size: 18px;
        }

        .module p {
            color: var(--muted);
            line-height: 1.6;
            font-size: 14px;
        }

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

        .panel {
            margin-bottom: 22px;
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
        }

        .panel h2 {
            margin: 0 0 20px;
            font-size: 18px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        th {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fcfaf7;
        }

        .empty {
            color: var(--muted);
            line-height: 1.7;
        }

        .dashboard-note {
            margin-top: 30px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 600px) {
            main {
                margin: 28px auto;
                padding: 0 16px;
            }

            header {
                padding: 18px 5%;
            }

            h1 {
                font-size: 26px;
            }

            .panel {
                padding: 18px;
            }
        }

        .dashboard-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dashboard-heading h1 {
            margin-bottom: 8px;
        }

        .dashboard-button {
            display: inline-block;
            padding: 12px 16px;
            border: 1px solid #a64520;
            border-radius: 8px;
            background: #a64520;
            color: #fff;
            font: inherit;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dashboard-button:hover {
            background: #873719;
        }

    </style>
</head>
<body>
    <header>
        <strong>BANTULOKAL HUB · ADMIN</strong>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </header>

    <main>
        <div class="dashboard-heading">
            <div>
                <h1>Dashboard Admin</h1>
                <p class="welcome">
                    Selamat datang, {{ auth()->user()->name }}.
                    Kelola operasional BANTULOKAL HUB dari sini.
                </p>
            </div>

            <a href="{{ route('admin.service-requests.index') }}" class="dashboard-button">
                Kelola Permintaan Jasa
                ({{ $counts['pending_service_requests'] }})
            </a>
        </div>

        <section class="stats-grid">
            <div class="stat-card">
                <p>Permintaan Jasa Pending</p>
                <strong>{{ $counts['pending_service_requests'] }}</strong>
            </div>

            <div class="stat-card">
                <p>Pendaftaran Freelancer Pending</p>
                <strong>{{ $counts['pending_freelancers'] }}</strong>
            </div>

            <div class="stat-card">
                <p>Permintaan Sewa Pending</p>
                <strong>{{ $counts['pending_bookings'] }}</strong>
            </div>

            <div class="stat-card">
                <p>Pengajuan Alat Baru</p>
                <strong>{{ $counts['pending_equipment_submissions'] }}</strong>
            </div>

            <div class="stat-card">
                <p>Alat yang Dipublikasikan</p>
                <strong>{{ $counts['published_equipment'] }}</strong>
            </div>
        </section>

        <h2 class="section-title">Aktivitas Terbaru</h2>

        <div class="modules">
            <section class="module">
                <h2>Permintaan Jasa</h2>
                <p>Tinjau permintaan pelanggan dan kebutuhan pekerja mereka.</p>
                <span class="status">
                    {{ $counts['pending_service_requests'] }} menunggu
                </span>
            </section>

            <section class="module">
                <h2>Pendaftaran Freelancer</h2>
                <p>Tinjau keterampilan dan pengalaman calon freelancer.</p>
                <span class="status">
                    {{ $counts['pending_freelancers'] }} menunggu
                </span>
            </section>

            <section class="module">
                <h2>Penyewaan Alat</h2>
                <p>Periksa permintaan sewa dan pengajuan alat dari pemilik.</p>
                <span class="status">
                    {{ $counts['pending_bookings'] }} sewa menunggu
                </span>
            </section>
        </div>

        <section class="panel">
            <h2>Permintaan Jasa Terbaru</h2>

            @if ($recentServiceRequests->isEmpty())
                <p class="empty">Belum ada permintaan jasa.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Kategori</th>
                                <th>Pekerja</th>
                                <th>Status</th>
                                <th>Tanggal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentServiceRequests as $request)
                                <tr>
                                    <td>#{{ $request->id }}</td>
                                    <td>{{ $request->customer_name }}</td>
                                    <td>{{ $request->category?->name ?? '-' }}</td>
                                    <td>{{ $request->worker_count }}</td>
                                    <td><span class="status">{{ $request->status }}</span></td>
                                    <td>{{ $request->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel">
            <h2>Pendaftaran Freelancer Terbaru</h2>

            @if ($recentFreelancers->isEmpty())
                <p class="empty">Belum ada pendaftaran freelancer.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>WhatsApp</th>
                                <th>Keahlian</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentFreelancers as $freelancer)
                                <tr>
                                    <td>#{{ $freelancer->id }}</td>
                                    <td>{{ $freelancer->name }}</td>
                                    <td>{{ $freelancer->whatsapp }}</td>
                                    <td>{{ $freelancer->skills ?: '-' }}</td>
                                    <td><span class="status">{{ $freelancer->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel">
            <h2>Permintaan Sewa Alat Terbaru</h2>

            @if ($recentBookings->isEmpty())
                <p class="empty">Belum ada permintaan sewa alat.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Alat</th>
                                <th>Periode Sewa</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentBookings as $booking)
                                <tr>
                                    <td>#{{ $booking->id }}</td>
                                    <td>{{ $booking->customer_name }}</td>
                                    <td>{{ $booking->equipment?->name ?? '-' }}</td>
                                    <td>
                                        {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
                                        -
                                        {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td>Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}</td>
                                    <td><span class="status">{{ $booking->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel">
            <h2>Pengajuan Alat dari Pemilik</h2>

            @if ($recentEquipmentSubmissions->isEmpty())
                <p class="empty">Belum ada pengajuan alat baru.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pemilik</th>
                                <th>Nama Alat</th>
                                <th>Kategori</th>
                                <th>Harga Usulan / Hari</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEquipmentSubmissions as $submission)
                                <tr>
                                    <td>#{{ $submission->id }}</td>
                                    <td>{{ $submission->owner_name }}</td>
                                    <td>{{ $submission->equipment_name }}</td>
                                    <td>{{ $submission->category?->name ?? '-' }}</td>
                                    <td>
                                        @if ($submission->proposed_price_per_day !== null)
                                            Rp {{ number_format((float) $submission->proposed_price_per_day, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td><span class="status">{{ $submission->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <p class="dashboard-note">
            Data ditampilkan langsung dari database. Fitur untuk menyetujui,
            menolak, dan mengubah status permintaan akan dibuat pada tahap berikutnya.
        </p>
    </main>
</body>
</html>
