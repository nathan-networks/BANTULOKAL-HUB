<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Penerimaan Pembayaran</title>
</head>
<body style="margin:0;padding:24px;background-color:#f4f1ec;font-family:Arial,Helvetica,sans-serif;color:#302a26;line-height:1.6;">
    <div style="max-width:600px;margin:0 auto;background-color:#ffffff;border:1px solid #e5ded6;border-radius:8px;overflow:hidden;">

        <div style="padding:28px 32px;background-color:#a95136;color:#ffffff;">
            <h1 style="margin:0;font-size:23px;letter-spacing:0.3px;">
                BANTULOKAL HUB
            </h1>
            <p style="margin:8px 0 0;font-size:14px;">
                Konfirmasi Penerimaan Pembayaran
            </p>
        </div>

        <div style="padding:32px;">
            <p style="margin-top:0;">
                Yth. {{ $serviceRequest->customer_name }},
            </p>

            <p>
                Dengan hormat,
            </p>

            <p>
                Melalui email ini, BANTULOKAL HUB mengonfirmasi bahwa
                pembayaran atas permintaan jasa Anda telah diverifikasi
                dan dicatat dalam sistem administrasi kami berdasarkan
                pemeriksaan pembayaran yang telah dilakukan.
            </p>

            <p>
                Rincian konfirmasi pembayaran adalah sebagai berikut:
            </p>

            <div style="margin:24px 0;padding:20px;background-color:#f8f6f2;border:1px solid #e8e0d7;border-radius:6px;">

                <table role="presentation" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <tr>
                        <td style="padding:9px 0;color:#71665e;vertical-align:top;width:45%;">
                            Nomor Invoice
                        </td>
                        <td style="padding:9px 0;font-weight:bold;vertical-align:top;">
                            {{ $serviceRequest->invoice_number }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:9px 0;color:#71665e;vertical-align:top;">
                            Nomor Permintaan
                        </td>
                        <td style="padding:9px 0;vertical-align:top;">
                            #{{ $serviceRequest->id }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:9px 0;color:#71665e;vertical-align:top;">
                            Kategori Jasa
                        </td>
                        <td style="padding:9px 0;vertical-align:top;">
                            {{ $serviceRequest->category?->name ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:9px 0;color:#71665e;vertical-align:top;">
                            Status Pembayaran
                        </td>
                        <td style="padding:9px 0;font-weight:bold;color:#28734a;vertical-align:top;">
                            TERKONFIRMASI
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding-top:16px;border-top:1px solid #ded6cc;">
                            <div style="font-size:13px;color:#71665e;">
                                Total Pembayaran Terverifikasi
                            </div>
                            <div style="margin-top:4px;font-size:25px;font-weight:bold;color:#a95136;">
                                Rp{{ number_format((float) $serviceRequest->final_total, 0, ',', '.') }}
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 0 0;color:#71665e;vertical-align:top;">
                            Waktu Verifikasi
                        </td>
                        <td style="padding:16px 0 0;vertical-align:top;">
                            {{ $serviceRequest->paid_at?->timezone('Asia/Jakarta')->format('d F Y, H:i') ?? '-' }} WIB
                        </td>
                    </tr>
                </table>
            </div>

            <p>
                Setelah pembayaran terkonfirmasi, tim BANTULOKAL HUB akan
                melanjutkan koordinasi dan proses penanganan permintaan jasa
                sesuai dengan kesepakatan yang telah disetujui.
            </p>

            <p>
                Mohon simpan email ini sebagai arsip konfirmasi pembayaran.
                Untuk pertanyaan, klarifikasi, atau korespondensi terkait
                permintaan jasa tersebut, Anda dapat menghubungi kami melalui
                alamat email berikut:
                <a href="mailto:projectworksid@gmail.com" style="color:#a95136;text-decoration:none;font-weight:bold;">
                    projectworksid@gmail.com
                </a>.
            </p>

            <p>
                Demikian konfirmasi ini kami sampaikan. Terima kasih atas
                kepercayaan Anda kepada BANTULOKAL HUB.
            </p>

            <p style="margin-bottom:0;">
                Hormat kami,<br>
                <strong>Tim Administrasi</strong><br>
                <strong>BANTULOKAL HUB</strong>
            </p>
        </div>

        <div style="padding:18px 32px;background-color:#f4f1ec;border-top:1px solid #e5ded6;color:#71665e;font-size:11px;line-height:1.5;">
            Email ini diterbitkan sebagai konfirmasi administratif atas
            pembayaran yang telah diverifikasi oleh BANTULOKAL HUB.
            Harap simpan email ini untuk keperluan dokumentasi dan korespondensi.
        </div>
    </div>
</body>
</html>