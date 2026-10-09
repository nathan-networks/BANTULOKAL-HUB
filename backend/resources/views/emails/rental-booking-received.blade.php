<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengajuan Sewa Diterima</title>
</head>
<body style="margin:0;background:#f6f2eb;font-family:Arial,sans-serif;color:#292820;padding:24px;">
    <div style="max-width:600px;margin:auto;background:#ffffff;padding:28px;border-radius:12px;">
        <h2 style="color:#a64520;">BANTULOKAL HUB</h2>
        <h1>Pengajuan Sewa Berhasil Diterima</h1>

        <p>Halo {{ $booking->customer_name }},</p>

        <p>
            Pengajuan sewa Anda telah kami terima dan akan ditinjau oleh tim kami.
            Email ini bukan konfirmasi bahwa penyewaan sudah disetujui.
        </p>

        <p><strong>Nomor pengajuan:</strong> #{{ $booking->id }}</p>
        <p><strong>Alat:</strong> {{ $booking->equipment?->name ?? '-' }}</p>
        <p>
            <strong>Periode sewa:</strong>
            {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
            sampai {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
        </p>
        <p>
            <strong>Estimasi biaya sewa:</strong>
            Rp {{ number_format((float) $booking->total_amount, 0, ',', '.') }}
        </p>

        <p>
            Tim kami akan memeriksa ketersediaan alat dan menghubungi Anda
            mengenai keputusan, biaya final, deposit, serta langkah berikutnya.
        </p>

        <p>Terima kasih telah menggunakan BANTULOKAL HUB.</p>
    </div>
</body>
</html>
