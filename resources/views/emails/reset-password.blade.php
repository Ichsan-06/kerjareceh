<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - KerjaReceh</title>
    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f5f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f5f5f9;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            font-family: sans-serif;
            color: #566a7f;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 40px;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        }
        .header {
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #a1acb8;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #FFC107;
            color: #2b2b2b !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(255, 193, 7, 0.4);
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #FFC107;
            text-decoration: none;
        }
        h1 {
            font-size: 22px;
            margin-top: 0;
            color: #566a7f;
        }
        p {
            margin-bottom: 20px;
        }
        .divider {
            border-top: 1px solid #d9dee3;
            margin: 30px 0;
        }
        .small-text {
            font-size: 13px;
            color: #a1acb8;
        }
        @media only screen and (max-width: 600px) {
            .main {
                width: 95% !important;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" width="100%">
            <tr>
                <td class="header">
                    <a href="{{ config('app.url') }}" class="logo">
                        KerjaReceh
                    </a>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h1>Halo!</h1>
                    <p>Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $url }}" class="button">Reset Kata Sandi</a>
                    </div>
                    
                    <p style="margin-top: 30px;">Tautan pengaturan ulang kata sandi ini akan kedaluwarsa dalam 60 menit.</p>
                    <p>Jika Anda tidak meminta pengaturan ulang kata sandi, tidak ada tindakan lebih lanjut yang diperlukan.</p>
                    
                    <p>Salam,<br>Tim KerjaReceh</p>
                    
                    <div class="divider"></div>
                    
                    <p class="small-text"> Jika Anda kesulitan mengeklik tombol "Reset Kata Sandi", salin dan tempel URL di bawah ini ke browser web Anda: <br>
                        <a href="{{ $url }}" style="color: #FFC107; word-break: break-all;">{{ $url }}</a>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} KerjaReceh. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
