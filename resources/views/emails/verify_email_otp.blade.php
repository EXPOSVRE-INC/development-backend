<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Your Verification Code</title>
    <style>
        body {
            background-color: #f4f4f7;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .email-container {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 30px auto;
            background-color: #f4f4f7;
            border-radius: 10px;
            overflow: hidden;
        }

        .header {
            background-color: #000;
            text-align: center;
            padding: 30px 0;
        }

        .header img {
            height: 40px;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .content h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .otp-box {
            display: inline-block;
            background-color: #EC008C;
            color: white;
            font-size: 25px;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 12px;
            letter-spacing: 10px;
            margin: 20px 0;
        }

        .info {
            font-size: 14px;
            color: #666;
            margin-top: 30px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #888;
        }

        .footer a {
            color: #ff2cb4;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container" style="box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1)">
        <div class="header">
            <img src="https://240c3b.p3cdn1.secureserver.net/wp-content/uploads/2023/09/EXPOSURE-Logo-white-on-pink.png?time=1715872980"
                alt="EXPOSVRE Logo"> <!-- Replace with real logo URL -->
        </div>
        <div class="content">
            <h1>Your Verification Code</h1>
            <p>Use the following one-time password (OTP) to verify your email address on <strong>EXPOSVRE</strong>.</p>
            <div class="otp-box">{{ $otp }}</div>
            <p>This code will expire in 10 minutes. Please do not share it with anyone.</p>

            <div class="info">
                If you didn’t request this, you can safely ignore this email.
            </div>
        </div>
        <div class="footer">
            © {{ date('Y') }} <strong>EXPOSVRE</strong>. All rights reserved.<br>
            Need help? Contact <a href="mailto:support@exposvre.com">support@exposvre.com</a>
        </div>
    </div>
</body>

</html>
