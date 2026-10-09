<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran Sewa</title>
</head>
<body style="margin:0;background:#f6f2eb;font-family:Arial,sans-serif;color:#292820;padding:24px;">
    <div style="max-width:600px;margin:auto;background:#ffffff;padding:28px;border-radius:12px;">
        <h2 style="color:#a64520;">BANTULOKAL HUB</h2>
        <h1>Pembayaran Terverifikasi</h1>

        <p>Halo {{ $booking->customer_name }},</p>

        <p>
            Pembayaran untuk pengajuan sewa Anda telah diverifikasi oleh tim kami.
        </p>

        <p><strong>Nomor invoice:</strong> {{ $booking->invoice_number }}</p>
        <p><strong>Nomor pengajuan:</strong> #{{ $booking->id }}</p>
        <p><strong>Alat:</strong> {{ $booking->equipment?->name ?? '-' }}</p>

        <p>
            <strong>Periode sewa:</strong>
            {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
            sampai {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
        </p>

        <hr style="border:0;border-top:1px solid #e6e0d6;">

        <p>
            <strong>Biaya sewa:</strong>
            Rp {{ number_format((float) $booking->final_rental_amount, 0, ',', '.') }}
        </p>
        <p>
            <strong>Deposit:</strong>
            Rp {{ number_format((float) $booking->final_deposit_amount, 0, ',', '.') }}
        </p>
        <p>
            <strong>Total pembayaran terverifikasi:</strong>
            Rp {{ number_format(
                (float) $booking->final_rental_amount +
                (float) $booking->final_deposit_amount,
                0, ',', '.'
            ) }}
        </p>

        <p>
            Tim kami akan menghubungi Anda untuk mengatur jadwal dan prosedur
            penyerahan alat. Mohon siapkan KTP asli untuk verifikasi saat
            serah terima sesuai ketentuan sewa.
        </p>

        <p>
            Foto KTP hanya boleh diambil dan ditangani sesuai kebijakan
            penggunaan, akses, penyimpanan, dan penghapusan data yang
            diinformasikan kepada Anda.
        </p>

        <p>Simpan email ini sebagai bukti konfirmasi pembayaran.</p>
        <p>Terima kasih,<br>BANTULOKAL HUB</p>
    </div>
</body>
</html>
