<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran Sewa - BANTULOKAL HUB</title>
</head>
<body style="margin:0;padding:0;background:#f6f2eb;font-family:Arial,sans-serif;color:#292820;">
    <div style="max-width:600px;margin:24px auto;background:#ffffff;border:1px solid #e6e0d6;border-radius:12px;overflow:hidden;">
        <div style="background:#a64520;padding:24px 28px;color:#ffffff;">
            <h1 style="margin:0;font-size:23px;">BANTULOKAL HUB</h1>
            <p style="margin:8px 0 0;font-size:14px;">Instruksi Pembayaran Sewa Alat</p>
        </div>

        <div style="padding:28px;">
            <p>Halo {{ $booking->customer_name }},</p>

            <p>
                Pengajuan sewa alat Anda telah disetujui. Silakan periksa
                rincian biaya dan rekening tujuan berikut sebelum melakukan transfer.
            </p>

            <div style="background:#f6f2eb;padding:18px;border-radius:8px;margin:20px 0;">
                <p style="margin:0 0 8px;">
                    <strong>Nomor Pengajuan:</strong> #{{ $booking->id }}
                </p>
                <p style="margin:0 0 8px;">
                    <strong>Nama Alat:</strong> {{ $booking->equipment?->name ?? '-' }}
                </p>
                <p style="margin:0 0 8px;">
                    <strong>Periode Sewa:</strong>
                    {{ $booking->rental_start_date?->format('d M Y') ?? '-' }}
                    sampai {{ $booking->rental_end_date?->format('d M Y') ?? '-' }}
                </p>
                <p style="margin:0 0 8px;">
                    <strong>Biaya Sewa:</strong>
                    Rp{{ number_format((float) $booking->final_rental_amount, 0, ',', '.') }}
                </p>
                <p style="margin:0 0 8px;">
                    <strong>Deposit:</strong>
                    Rp{{ number_format((float) $booking->final_deposit_amount, 0, ',', '.') }}
                </p>
                <p style="margin:14px 0 0;font-size:20px;">
                    <strong>Total Pembayaran:</strong><br>
                    <span style="color:#a64520;">
                        Rp{{ number_format(
                            (float) $booking->final_rental_amount
                            + (float) $booking->final_deposit_amount,
                            0, ',', '.'
                        ) }}
                    </span>
                </p>
                <p style="margin:12px 0 0;font-size:12px;color:#716e65;">
                    Deposit dicatat terpisah dari biaya sewa dan mengikuti
                    ketentuan pengembalian deposit yang telah disepakati.
                </p>
            </div>

            <h2 style="font-size:18px;">Pilih salah satu rekening tujuan</h2>

            <div style="border:1px solid #e6e0d6;border-radius:8px;padding:16px;margin:12px 0;">
                <p style="margin:0 0 8px;"><strong>Bank Jago</strong></p>
                <p style="margin:0 0 6px;">
                    Nomor rekening: <strong>103374578188</strong>
                </p>
                <p style="margin:0;">
                    Atas nama: <strong>NATHAN ANDIRA RADITYA</strong>
                </p>
            </div>

            <div style="border:1px solid #e6e0d6;border-radius:8px;padding:16px;margin:12px 0;">
                <p style="margin:0 0 8px;"><strong>BCA</strong></p>
                <p style="margin:0 0 6px;">
                    Nomor rekening: <strong>5063020647</strong>
                </p>
                <p style="margin:0;">
                    Atas nama: <strong>NATHAN ANDIRA RADITYA</strong>
                </p>
            </div>

            <p>
                <strong>Penting:</strong> Transfer sesuai total pembayaran di atas
                dan pastikan nama penerima sesuai dengan informasi rekening.
                Cantumkan nomor pengajuan jika memungkinkan.
            </p>

            <p>
                Pembayaran baru dianggap terverifikasi setelah tim BANTULOKAL HUB
                memeriksa dana masuk pada rekening tujuan. Bukti transfer saja
                bukan konfirmasi bahwa pembayaran telah diterima.
            </p>

            <p>
                Penyerahan alat hanya dapat dijadwalkan setelah pembayaran
                terverifikasi dan prosedur serah terima dikonfirmasi oleh tim.
            </p>

            <p>
                Jika ada pertanyaan, hubungi
                <a href="mailto:projectworksid@gmail.com" style="color:#a64520;">
                    projectworksid@gmail.com
                </a>.
            </p>

            <p>Terima kasih,<br><strong>Tim BANTULOKAL HUB</strong></p>
        </div>

        <div style="padding:16px 28px;background:#f6f2eb;border-top:1px solid #e6e0d6;font-size:12px;color:#716e65;">
            Email ini berisi instruksi pembayaran untuk pengajuan sewa alat yang telah disetujui.
        </div>
    </div>
</body>
</html>
