<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Sewa #{{ $booking->id }} - BANTULOKAL HUB</title>
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
        button:hover, .button:hover { background: var(--accent-soft); }
        main {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
        }
        .muted { color: var(--muted); line-height: 1.7; }
        .panel {
            margin-top: 22px;
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
        }
        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
        }
        .field-label {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 13px;
        }
        .field-value {
            overflow-wrap: anywhere;
            line-height: 1.6;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
        }
        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: 600;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
        }
        textarea { min-height: 110px; resize: vertical; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }
        .approve {
            border-color: var(--accent);
            background: var(--accent);
            color: white;
        }
        .approve:hover { background: #873719; color: white; }
        .reject { color: #b42318; border-color: #b42318; }
        .message {
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }
        .error { color: #b42318; font-size: 14px; }
        @media (max-width: 600px) {
            main { padding: 0 16px; }
            .panel { padding: 18px; }
        }
    </style>
</head>
<body>
<header>
    <strong>BANTULOKAL HUB · ADMIN</strong>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a class="button" href="{{ route('admin.bookings.index') }}">Daftar Sewa</a>
        <a class="button" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    </div>
</header>

<main>
    <h1>Permintaan Sewa #{{ $booking->id }}</h1>
    <p class="muted">
        Periksa detail permintaan dan koordinasikan ketersediaan alat dengan pemilik
        sebelum menyetujui biaya dan pembayaran.
    </p>

    @if (session('success'))
        <div class="message">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="message error">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel">
        <h2>Informasi Pelanggan</h2>
        <div class="details">
            <div>
                <span class="field-label">Nama</span>
                <div class="field-value">{{ $booking->customer_name }}</div>
            </div>
            <div>
                <span class="field-label">Email</span>
                <div class="field-value">{{ $booking->customer_email }}</div>
            </div>
            <div>
                <span class="field-label">WhatsApp</span>
                <div class="field-value">{{ $booking->customer_whatsapp }}</div>
            </div>
            <div>
                <span class="field-label">Tanggal Pengajuan</span>
                <div class="field-value">
                    {{ $booking->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Informasi Alat dan Periode Sewa</h2>
        <div class="details">
            <div>
                <span class="field-label">Nama Alat</span>
                <div class="field-value">{{ $booking->equipment?->name ?? '-' }}</div>
            </div>
            <div>
                <span class="field-label">Lokasi Alat</span>
                <div class="field-value">{{ $booking->equipment?->location ?? '-' }}</div>
            </div>
            <div>
                <span class="field-label">Tanggal Mulai</span>
                <div class="field-value">
                    {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
                </div>
            </div>
            <div>
                <span class="field-label">Tanggal Selesai</span>
                <div class="field-value">
                    {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
                </div>
            </div>
            <div>
                <span class="field-label">Harga per Hari (Snapshot)</span>
                <div class="field-value">
                    Rp {{ number_format((float) $booking->price_per_day, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <span class="field-label">Estimasi Biaya Sewa</span>
                <div class="field-value">
                    Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <span class="field-label">Deposit Alat Saat Ini</span>
                <div class="field-value">
                    Rp {{ number_format((float) ($booking->equipment?->deposit_amount ?? 0), 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div style="margin-top:22px;">
            <span class="field-label">Catatan Pelanggan</span>
            <div class="field-value" style="white-space:pre-wrap;">
                {{ $booking->customer_notes ?: 'Tidak ada catatan.' }}
            </div>
        </div>
    </section>

    <section class="panel">
        <h2>Status dan Pembayaran</h2>
        <div class="details">
            <div>
                <span class="field-label">Status Permintaan</span>
                <span class="status">{{ $booking->status }}</span>
            </div>
            <div>
                <span class="field-label">Status Pembayaran</span>
                <span class="status">{{ $booking->payment_status }}</span>
            </div>
            <div>
                <span class="field-label">Biaya Sewa Final</span>
                <div class="field-value">
                    @if ($booking->final_rental_amount !== null)
                        Rp {{ number_format((float) $booking->final_rental_amount, 0, ',', '.') }}
                    @else
                        Belum ditentukan
                    @endif
                </div>
            </div>
            <div>
                <span class="field-label">Deposit Final</span>
                <div class="field-value">
                    @if ($booking->final_deposit_amount !== null)
                        Rp {{ number_format((float) $booking->final_deposit_amount, 0, ',', '.') }}
                    @else
                        Belum ditentukan
                    @endif
                </div>
            </div>
        </div>

        @if ($booking->admin_notes)
            <div style="margin-top:22px;">
                <span class="field-label">Catatan Admin</span>
                <div class="field-value" style="white-space:pre-wrap;">{{ $booking->admin_notes }}</div>
            </div>
        @endif

        <p class="muted">
            Persetujuan ini belum berarti pembayaran telah diterima.
            Verifikasi dana secara manual sebelum menyerahkan alat.
            Deposit dicatat terpisah dari biaya sewa.
        </p>
    </section>

        {{-- Payment verification and confirmation email --}}
        @if ($booking->status === 'approved' && $booking->payment_status !== 'paid')
            <section class="panel">
                <h2>Verifikasi Pembayaran</h2>
                <p class="muted">
                    Pastikan transfer sudah masuk ke rekening sebelum memverifikasi.
                    Setelah pembayaran terverifikasi, email konfirmasi akan dikirim
                    kepada pelanggan.
                </p>

                <form method="POST"
                    action="{{ route('admin.bookings.verify-payment', $booking) }}">
                    @csrf
                    @method('PATCH')

                    <label for="payment_reference">Referensi transfer (opsional)</label>
                    <input
                        id="payment_reference"
                        name="payment_reference"
                        type="text"
                        maxlength="255"
                        value="{{ old('payment_reference') }}"
                        placeholder="Nomor referensi atau berita transfer"
                    >

                    @error('payment_reference')
                        <p class="error">{{ $message }}</p>
                    @enderror

                    @error('payment')
                        <p class="error">{{ $message }}</p>
                    @enderror

                    <div class="actions">
                        <button
                            class="approve"
                            type="submit"
                            onclick="return confirm('Pastikan dana sudah diterima. Verifikasi pembayaran sekarang?')"
                        >
                            Verifikasi Pembayaran
                        </button>
                    </div>
                </form>
            </section>
        @endif

        @if ($booking->payment_status === 'paid')
            <section class="panel">
                <h2>Pembayaran Terverifikasi</h2>

                <div class="details">
                    <div>
                        <span class="field-label">Nomor Invoice</span>
                        <div class="field-value">
                            {{ $booking->invoice_number ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <span class="field-label">Waktu Verifikasi</span>
                        <div class="field-value">
                            {{ $booking->paid_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <span class="field-label">Diverifikasi oleh</span>
                        <div class="field-value">
                            {{ $booking->paymentVerifier?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <span class="field-label">Referensi Transfer</span>
                        <div class="field-value">
                            {{ $booking->payment_reference ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <span class="field-label">Email Konfirmasi Terkirim</span>
                        <div class="field-value">
                            {{ $booking->payment_confirmation_sent_at
                                ? $booking->payment_confirmation_sent_at->timezone('Asia/Jakarta')->format('d M Y H:i')
                                : 'Belum berhasil dikirim' }}
                        </div>
                    </div>
                </div>

                @if (!$booking->payment_confirmation_sent_at)
                    <p class="muted">
                        Pembayaran sudah tercatat, tetapi email konfirmasi belum
                        berhasil dikirim. Kamu bisa mencoba mengirimnya kembali.
                    </p>
                @endif

                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror

                <div class="actions">
                    <form method="POST"
                        action="{{ route('admin.bookings.resend-payment-confirmation', $booking) }}">
                        @csrf

                        <button type="submit">
                            Kirim Ulang Email Konfirmasi
                        </button>
                    </form>
                </div>
            </section>
        @endif

        @if (session('warning'))
            <div class="message">{{ session('warning') }}</div>
        @endif

        @if ($booking->status === 'pending')
            <section class="panel">
                <h2>Tinjau Permintaan</h2>

    @if ($booking->status === 'pending')
        <section class="panel">
            <h2>Tinjau Permintaan</h2>
            <p class="muted">
                Pastikan tanggal tersedia dan biaya sudah disepakati dengan pemilik
                serta pelanggan sebelum menyetujui permintaan.
            </p>

            <form method="POST"
                  action="{{ route('admin.bookings.review', $booking) }}">
                @csrf
                @method('PATCH')

                <label for="final_rental_amount">Biaya sewa final (Rp)</label>
                <input
                    type="number"
                    id="final_rental_amount"
                    name="final_rental_amount"
                    min="0"
                    max="9999999999.99"
                    step="0.01"
                    value="{{ old('final_rental_amount', $booking->total_amount) }}"
                >
                @error('final_rental_amount')
                    <p class="error">{{ $message }}</p>
                @enderror

                <label for="final_deposit_amount">Deposit final (Rp)</label>
                <input
                    type="number"
                    id="final_deposit_amount"
                    name="final_deposit_amount"
                    min="0"
                    max="9999999999.99"
                    step="0.01"
                    value="{{ old('final_deposit_amount', $booking->equipment?->deposit_amount ?? 0) }}"
                >
                @error('final_deposit_amount')
                    <p class="error">{{ $message }}</p>
                @enderror

                <label for="admin_notes">Catatan admin</label>
                <textarea
                    id="admin_notes"
                    name="admin_notes"
                    maxlength="5000"
                    placeholder="Catatan koordinasi dengan pemilik atau pelanggan..."
                >{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                @error('admin_notes')
                    <p class="error">{{ $message }}</p>
                @enderror

                <div class="actions">
                    <button
                        class="approve"
                        type="submit"
                        name="decision"
                        value="approved"
                        onclick="return confirm('Setujui permintaan sewa ini dengan biaya dan deposit tersebut?')"
                    >
                        Setujui Permintaan
                    </button>

                    <button
                        class="reject"
                        type="submit"
                        name="decision"
                        value="rejected"
                        onclick="return confirm('Tolak permintaan sewa ini?')"
                    >
                        Tolak Permintaan
                    </button>
                </div>
            </form>
        </section>
    @else
        <section class="panel">
            <h2>Keputusan Admin</h2>
            <p class="muted">
                Permintaan ini sudah ditinjau. Form keputusan tidak lagi tersedia.
            </p>
            @if ($booking->handler)
                <p class="muted">Ditinjau oleh: {{ $booking->handler->name }}</p>
            @endif
            @if ($booking->approved_at)
                <p class="muted">
                    Disetujui pada:
                    {{ $booking->approved_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                </p>
            @endif
            @if ($booking->rejected_at)
                <p class="muted">
                    Ditolak pada:
                    {{ $booking->rejected_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                </p>
            @endif
        </section>
    @endif
</main>
</body>
</html>
