<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Permintaan Jasa - BANTULOKAL HUB</title>

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
        }

        main {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
        }

        .muted {
            color: var(--muted);
            line-height: 1.7;
        }

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
            margin-bottom: 8px;
            font-weight: 600;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            resize: vertical;
        }

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

        .approve:hover {
            background: #873719;
            color: white;
        }

        .reject {
            border-color: #b42318;
            color: #b42318;
        }

        .message {
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
        }

        .error {
            color: #b42318;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            main { padding: 0 16px; }
            .panel { padding: 18px; }
        }
    </style>
</head>
<body>
    <header>
        <strong>BANTULOKAL HUB · ADMIN</strong>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a class="button" href="{{ route('admin.service-requests.index') }}">
                Kembali ke Daftar
            </a>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Keluar</button>
            </form>
        </div>
    </header>

    <main>
        <h1>Permintaan Jasa #{{ $serviceRequest->id }}</h1>
        <p class="muted">
            Tinjau kebutuhan pelanggan sebelum menentukan keputusan.
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
                    <div class="field-value">{{ $serviceRequest->customer_name }}</div>
                </div>

                <div>
                    <span class="field-label">Email</span>
                    <div class="field-value">{{ $serviceRequest->customer_email }}</div>
                </div>

                <div>
                    <span class="field-label">WhatsApp</span>
                    <div class="field-value">{{ $serviceRequest->customer_whatsapp }}</div>
                </div>

                <div>
                    <span class="field-label">Lokasi</span>
                    <div class="field-value">{{ $serviceRequest->location }}</div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Detail Layanan</h2>

            <div class="details">
                <div>
                    <span class="field-label">Kategori</span>
                    <div class="field-value">{{ $serviceRequest->category?->name ?? '-' }}</div>
                </div>

                <div>
                    <span class="field-label">Jumlah Pekerja</span>
                    <div class="field-value">{{ $serviceRequest->worker_count }} orang</div>
                </div>

                <div>
                    <span class="field-label">Tanggal Pilihan</span>
                    <div class="field-value">
                        {{ $serviceRequest->preferred_date?->format('d M Y') ?? 'Belum ditentukan' }}
                    </div>
                </div>

                <div>
                    <span class="field-label">Tanggal Pengajuan</span>
                    <div class="field-value">
                        {{ $serviceRequest->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            <div style="margin-top:22px;">
                <span class="field-label">Deskripsi Kebutuhan</span>
                <div class="field-value" style="white-space:pre-wrap;">{{ $serviceRequest->description }}</div>
            </div>
        </section>

        <section class="panel">
            <h2>Status dan Pembayaran</h2>

            <div class="details">
                <div>
                    <span class="field-label">Status Permintaan</span>
                    <span class="status">{{ $serviceRequest->status }}</span>
                </div>

                <div>
                    <span class="field-label">Status Pembayaran</span>
                    <span class="status">{{ $serviceRequest->payment_status }}</span>
                </div>

                <div>
                    <span class="field-label">Estimasi Biaya</span>
                    <div class="field-value">
                        @if ($serviceRequest->estimated_total !== null)
                            Rp {{ number_format((float) $serviceRequest->estimated_total, 0, ',', '.') }}
                        @else
                            Belum ditentukan
                        @endif
                    </div>
                </div>

                <div>
                    <span class="field-label">Biaya Final</span>
                    <div class="field-value">
                        @if ($serviceRequest->final_total !== null)
                            Rp {{ number_format((float) $serviceRequest->final_total, 0, ',', '.') }}
                        @else
                            Belum ditentukan
                        @endif
                    </div>
                </div>
            </div>


        <div class="details" style="margin-top:22px;">
            <div>
                <span class="field-label">Biaya Layanan Final</span>
                <div class="field-value">
                    @if ($serviceRequest->service_fee !== null)
                        Rp {{ number_format((float) $serviceRequest->service_fee, 0, ',', '.') }}
                    @else
                        Belum ditentukan
                    @endif
                </div>
            </div>

            <div>
                <span class="field-label">Biaya Tambahan Disetujui</span>
                <div class="field-value">
                    Rp {{ number_format((float) ($serviceRequest->approved_expenses ?? 0), 0, ',', '.') }}
                </div>
            </div>

            <div>
                <span class="field-label">Bagian Freelancer (80%)</span>
                <div class="field-value">
                    @if ($serviceRequest->worker_pool_amount !== null)
                        Rp {{ number_format((float) $serviceRequest->worker_pool_amount, 0, ',', '.') }}
                    @else
                        Belum dihitung
                    @endif
                </div>
            </div>

            <div>
                <span class="field-label">Bagian BANTULOKAL HUB (20%)</span>
                <div class="field-value">
                    @if ($serviceRequest->platform_amount !== null)
                        Rp {{ number_format((float) $serviceRequest->platform_amount, 0, ',', '.') }}
                    @else
                        Belum dihitung
                    @endif
                </div>
            </div>
        </div>

        @if ($serviceRequest->expense_notes)
            <div style="margin-top:20px;">
                <span class="field-label">Rincian Biaya Tambahan</span>
                <div class="field-value" style="white-space:pre-wrap;">{{ $serviceRequest->expense_notes }}</div>
            </div>
        @endif

        <p class="muted">
            Biaya tambahan tidak termasuk dalam pembagian 80/20.
            Harga final ditetapkan oleh admin sebelum instruksi pembayaran diberikan.
        </p>

            @if ($serviceRequest->reviewer)
                <p class="muted">
                    Ditinjau oleh {{ $serviceRequest->reviewer->name }}
                    @if ($serviceRequest->approved_at)
                        pada {{ $serviceRequest->approved_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                    @endif
                </p>
            @endif

            @if ($serviceRequest->admin_notes)
                <div style="margin-top:20px;">
                    <span class="field-label">Catatan Admin Sebelumnya</span>
                    <div style="white-space:pre-wrap;">{{ $serviceRequest->admin_notes }}</div>
                </div>
            @endif

        @if ($serviceRequest->status === 'approved' && $serviceRequest->payment_status === 'unpaid')
            <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
                <h2>Verifikasi Pembayaran</h2>

                <p class="muted">
                    Periksa dana masuk pada rekening Bank Jago atau BCA sebelum
                    menandai pembayaran sebagai lunas. Jangan hanya mengandalkan
                    screenshot bukti transfer.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.service-requests.verify-payment', $serviceRequest) }}"
                    onsubmit="return confirm('Pastikan dana sudah masuk ke rekening. Tandai pembayaran ini sebagai lunas?')"
                >
                    @csrf
                    @method('PATCH')

                    <div style="margin-top:20px;">
                        <label for="payment_reference">
                            Referensi transfer (opsional)
                        </label>

                        <input
                            type="text"
                            id="payment_reference"
                            name="payment_reference"
                            maxlength="255"
                            value="{{ old('payment_reference') }}"
                            placeholder="Nomor referensi transaksi"
                            style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px;font:inherit;"
                        />

                        @error('payment_reference')
                            <p class="error">{{ $message }}</p>
                        @enderror

                        <p class="muted">
                            Opsional: masukkan nomor referensi transaksi dari mutasi bank.
                        </p>
                    </div>

                    <div class="actions">
                        <button class="approve" type="submit">
                            Verifikasi dan Tandai Lunas
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if ($serviceRequest->payment_status === 'paid')
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
            <h2>Pembayaran Terverifikasi</h2>

            <div class="details">
                <div>
                    <span class="field-label">Nomor Invoice</span>
                    <div class="field-value">
                        {{ $serviceRequest->invoice_number ?? 'Belum tersedia' }}
                    </div>
                </div>

                <div>
                    <span class="field-label">Waktu Verifikasi</span>
                    <div class="field-value">
                        {{ $serviceRequest->paid_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                    </div>
                </div>

                <div>
                    <span class="field-label">Diverifikasi Oleh</span>
                    <div class="field-value">
                        {{ $serviceRequest->paymentVerifier?->name ?? 'Admin tidak tersedia' }}
                    </div>
                </div>

                <div>
                    <span class="field-label">Referensi Transfer</span>
                    <div class="field-value">
                        {{ $serviceRequest->payment_reference ?: '-' }}
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.service-requests.resend-payment-confirmation', $serviceRequest) }}"
                onsubmit="return confirm('Kirim ulang email konfirmasi pembayaran kepada pelanggan?')"
                style="margin-top:20px;"
            >
                @csrf

                <button
                    type="submit"
                    style="padding:12px 18px;background:#a95136;color:#ffffff;border:0;border-radius:8px;font:inherit;cursor:pointer;"
                >
                    Kirim Ulang Email Konfirmasi
                </button>
            </form>
        </div>
        @endif
        </section>

        @if ($serviceRequest->status === 'pending')
            <section class="panel">
                <h2>Tinjau Permintaan</h2>
                <p class="muted">
                    Periksa kebutuhan pelanggan dan kesesuaian freelancer
                    sebelum menyetujui permintaan.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.service-requests.review', $serviceRequest) }}"
                >
                    @csrf
                    @method('PATCH')

        <div style="margin-top:20px;">
            <label for="service_fee">Biaya layanan final (Rp)</label>
            <input
                type="number"
                id="service_fee"
                name="service_fee"
                min="1"
                max="9999999999.99"
                step="0.01"
                value="{{ old('service_fee', $serviceRequest->service_fee ?? $serviceRequest->estimated_total) }}"
                style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px;font:inherit;"
            >
            @error('service_fee')
                <p class="error">{{ $message }}</p>
            @enderror
            <p class="muted">
                Masukkan harga layanan yang telah disepakati dengan pelanggan.
                Estimasi awal hanya menjadi referensi.
            </p>
        </div>

        <div style="margin-top:20px;">
            <label for="approved_expenses">
                Biaya tambahan yang disetujui (Rp)
            </label>
            <input
                type="number"
                id="approved_expenses"
                name="approved_expenses"
                min="0"
                max="9999999999.99"
                step="0.01"
                value="{{ old('approved_expenses', $serviceRequest->approved_expenses ?? 0) }}"
                style="width:100%;padding:12px;border:1px solid var(--border);border-radius:8px;font:inherit;"
            >
            @error('approved_expenses')
                <p class="error">{{ $message }}</p>
            @enderror
            <p class="muted">
                Contoh: sewa kendaraan atau biaya transportasi yang telah
                disepakati. Isi 0 jika tidak ada.
            </p>
        </div>

        <div style="margin-top:20px;">
            <label for="expense_notes">Rincian biaya tambahan</label>
            <textarea
                name="expense_notes"
                id="expense_notes"
                maxlength="5000"
                placeholder="Contoh: sewa kendaraan Rp50.000"
            >{{ old('expense_notes', $serviceRequest->expense_notes) }}</textarea>
            @error('expense_notes')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

                    <label for="admin_notes">Catatan Admin</label>
                    <textarea
                        name="admin_notes"
                        id="admin_notes"
                        maxlength="5000"
                        placeholder="Catatan internal tentang hasil peninjauan..."
                    >{{ old('admin_notes', $serviceRequest->admin_notes) }}</textarea>

                    <div class="actions">
                        <button
                            class="approve"
                            type="submit"
                            name="decision"
                            value="approved"
                            onclick="return confirm('Setujui permintaan jasa ini?')"
                        >
                            Setujui Permintaan
                        </button>

                        <button
                            class="reject"
                            type="submit"
                            name="decision"
                            value="rejected"
                            onclick="return confirm('Tolak permintaan jasa ini?')"
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
            </section>
        @endif
    </main>
</body>
</html>
