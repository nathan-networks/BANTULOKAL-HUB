<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Penerimaan Permintaan Layanan</title>
</head>
<body style="margin:0;padding:24px;background:#f6f2eb;font-family:Arial,Helvetica,sans-serif;color:#292820;line-height:1.7;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e6e0d6;border-radius:12px;overflow:hidden;">

        <!-- Header -->
        <div style="padding:24px 28px;background:#a64520;color:#ffffff;">
            <p style="margin:0;font-size:13px;letter-spacing:1px;">BANTULOKAL HUB</p>
            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.35;">
                Permintaan Layanan Telah Diterima
            </h1>
        </div>

        <!-- Email content -->
        <div style="padding:28px;">
            <p>Yth. {{ $serviceRequest->customer_name }},</p>

            <p>
                Terima kasih telah mengajukan permintaan layanan melalui
                <strong>BANTULOKAL HUB</strong>.
            </p>

            <p>
                Melalui email ini, kami mengonfirmasi bahwa permintaan Anda
                telah kami terima dan tercatat dalam sistem. Penerimaan ini
                belum merupakan persetujuan atas permintaan maupun konfirmasi
                ketersediaan penyedia jasa.
            </p>

            <!-- Request summary -->
            <h2 style="margin-top:28px;font-size:18px;">Ringkasan Permintaan</h2>

            <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;color:#716e65;width:42%;">
                        Nomor permintaan
                    </td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;font-weight:bold;">
                        #{{ $serviceRequest->id }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;color:#716e65;">
                        Kategori layanan
                    </td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;">
                        {{ $serviceRequest->category->name }}
                    </td>
                </tr>

                @if (in_array($serviceRequest->category->slug, ['bersih-bersih', 'pindah-kos']))
                    <tr>
                        <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;color:#716e65;">
                            Jumlah pekerja yang diminta
                        </td>
                        <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;">
                            {{ $serviceRequest->worker_count }} orang
                        </td>
                    </tr>
                @endif

                @if ($serviceRequest->estimated_total !== null)
                    <tr>
                        <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;color:#716e65;">
                            Estimasi awal biaya layanan
                        </td>
                        <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;font-weight:bold;">
                            Rp{{ number_format((float) $serviceRequest->estimated_total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;color:#716e65;">
                        Status
                    </td>
                    <td style="padding:10px 8px;border-bottom:1px solid #e6e0d6;">
                        <span style="display:inline-block;padding:4px 10px;border-radius:20px;background:#fff1df;color:#8a4b12;font-size:12px;font-weight:bold;">
                            Menunggu peninjauan
                        </span>
                    </td>
                </tr>
            </table>

            <!-- Next steps -->
            <h2 style="margin-top:28px;font-size:18px;">Tahapan Selanjutnya</h2>

            <p>
                Tim BANTULOKAL HUB akan meninjau kebutuhan yang Anda sampaikan
                dan memeriksa ketersediaan penyedia jasa yang sesuai. Apabila
                diperlukan, tim kami akan menghubungi Anda untuk mengklarifikasi
                detail permintaan.
            </p>

            <p>
                Apabila permintaan Anda dapat dilayani, kami akan mengonfirmasi
                ketersediaan penyedia jasa, ruang lingkup pekerjaan, jadwal,
                serta rincian biaya sebelum pekerjaan disepakati.
            </p>

            <!-- Pricing and payment notice -->
            <div style="margin:24px 0;padding:18px;border-left:4px solid #a64520;background:#faf5ef;">
                <h2 style="margin:0 0 8px;font-size:17px;">
                    Informasi Biaya dan Pembayaran
                </h2>

                <p style="margin:0 0 10px;">
                    Estimasi biaya yang tercantum, apabila tersedia, merupakan
                    perkiraan awal dan bukan harga final. Total biaya akan
                    dikonfirmasi setelah permintaan ditinjau dan rincian biaya
                    disepakati.
                </p>

                <p style="margin:0;">
                    <strong>Anda belum perlu melakukan pembayaran pada tahap ini.</strong>
                    Lakukan pembayaran hanya setelah menerima konfirmasi biaya
                    final dan instruksi pembayaran resmi dari BANTULOKAL HUB.
                    Pastikan instruksi tersebut dapat diverifikasi sebelum
                    melakukan transfer.
                </p>
            </div>

            <!-- Privacy notice -->
            <h2 style="margin-top:28px;font-size:18px;">
                Pelindungan Data Pribadi
            </h2>

            <p>
                Data yang Anda sampaikan, seperti nama, nomor WhatsApp, email,
                lokasi, dan detail pekerjaan, digunakan untuk meninjau dan
                menindaklanjuti permintaan serta menghubungi Anda terkait
                layanan yang diminta.
            </p>

            <p>
                Untuk informasi lebih lanjut mengenai pemrosesan data pribadi
                dan ketentuan penggunaan layanan, silakan kunjungi situs
                BANTULOKAL HUB atau hubungi kami melalui email di bawah ini.
            </p>
            <p>
                Jika Anda memiliki pertanyaan mengenai permintaan ini, silakan
                hubungi kami melalui email
                <a href="mailto:projectworksid@gmail.com" style="color:#a64520;">
                    projectworksid@gmail.com
                </a>.
            </p>

            <p>Terima kasih atas kepercayaan Anda kepada BANTULOKAL HUB.</p>

            <p style="margin-bottom:0;">
                Hormat kami,<br>
                <strong>Tim BANTULOKAL HUB</strong>
            </p>
        </div>

        <!-- Footer -->
        <div style="padding:18px 28px;background:#f6f2eb;border-top:1px solid #e6e0d6;color:#716e65;font-size:12px;">
            Email ini merupakan konfirmasi otomatis bahwa permintaan Anda
            telah diterima. Email ini bukan konfirmasi bahwa layanan telah
            disetujui, penyedia jasa telah ditetapkan, atau pekerjaan telah
            dijadwalkan.
            <p style="margin-bottom:0;">
                © BANTULOKAL HUB
            </p>
        </div>
    </div>
</body>
</html>
