<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Persetujuan Kemitraan SilaDesBeng</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; padding: 20px; color: #374151; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #115789; margin: 0; font-size: 24px; }
        .content { font-size: 16px; line-height: 1.6; }
        .credentials-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .credentials-box p { margin: 10px 0; font-size: 18px; }
        .credentials-box strong { color: #0f172a; }
        .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #6b7280; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SilaDesBeng</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Selamat! Pengajuan kemitraan untuk <strong>{{ $regionName }}</strong> telah <strong>disetujui</strong> oleh Admin Pusat SilaDesBeng.</p>
            <p>Berikut adalah informasi akun Admin Anda yang dapat digunakan untuk masuk ke dalam sistem:</p>
            
            <div class="credentials-box">
                <p>Username: <strong>{{ $username }}</strong></p>
                <p>Password: <strong>{{ $password }}</strong></p>
            </div>
            
            <p>Harap segera login ke sistem dan ubah password Anda demi keamanan.</p>
            <p>Terima kasih telah bergabung dengan SilaDesBeng!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SilaDesBeng. Kabupaten Bengkalis.</p>
        </div>
    </div>
</body>
</html>
