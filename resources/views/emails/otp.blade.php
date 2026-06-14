<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP - iSewa</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            display: inline-block;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .warning p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🔐 Kode Verifikasi OTP</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Anda menerima email ini karena ada permintaan verifikasi untuk akun iSewa Anda.</p>
            <p>Gunakan kode OTP berikut untuk melanjutkan:</p>
            
            <div class="otp-box">
                {{ $otp }}
            </div>
            
            <p style="color: #888; font-size: 14px;">Kode ini akan kedaluwarsa dalam <strong>5 menit</strong>.</p>
            
            <div class="warning">
                <p><strong>⚠️ Perhatian:</strong></p>
                <p>Jangan bagikan kode ini kepada siapa pun. Tim iSewa tidak akan pernah meminta kode OTP Anda.</p>
            </div>
            
            <p style="margin-top: 30px;">Jika Anda tidak melakukan permintaan ini, abaikan email ini.</p>
        </div>
        <div class="footer">
            <p><strong>iSewa</strong></p>
            <p>Sistem Informasi Sewa Alat Desa</p>
            <p style="color: #adb5bd; font-size: 12px;">Email ini dikirim secara otomatis, mohon tidak membalas.</p>
        </div>
    </div>
</body>
</html>
