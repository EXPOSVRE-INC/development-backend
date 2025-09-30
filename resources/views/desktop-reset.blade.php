<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open in EXPOSVRE App</title>
    <style>
        .desktop-body{
            background: linear-gradient(88deg, #0f0f0f 0%, #df0ea0 100%);
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
            margin: 0 auto;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .logo svg {
            width: 45px;
            height: 45px;
            fill: white;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .app-icon {
            width: 100px;
            height: 100px;
            margin: 20px auto;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .app-icon svg {
            width: 100%;
            height: 100%;
        }

        .instructions {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }

        .instruction-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .instruction-item:last-child {
            margin-bottom: 0;
        }

        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .instruction-text {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
            padding-top: 3px;
        }

        .download-section {
            margin-top: 35px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .download-text {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .store-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .store-button {
            display: inline-flex;
            align-items: center;
            background: #000;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 14px;
        }

        .store-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .store-button svg {
            width: 24px;
            height: 24px;
            margin-right: 10px;
        }

        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 13px;
        }

    </style>
</head>
<body class="desktop-body">
    <div class="container">
        <img src="https://240c3b.p3cdn1.secureserver.net/wp-content/uploads/2023/09/EXPOSURE-Logo-white-on-pink.png?time=1715872980"
                                alt="EXPOSVRE Logo" style="display: block; height: 50px; margin: 0 auto; margin-bottom: 30px;">

        <h1>Open in EXPOSVRE App</h1>
        <p class="subtitle">This password reset link needs to be opened in the EXPOSVRE mobile app</p>

        <div class="instructions">
            <div class="instruction-item">
                <div class="step-number">1</div>
                <div class="instruction-text">Open the EXPOSVRE app on your mobile device</div>
            </div>
            <div class="instruction-item">
                <div class="step-number">2</div>
                <div class="instruction-text">Tap on this link again from your mobile device</div>
            </div>
            <div class="instruction-item">
                <div class="step-number">3</div>
                <div class="instruction-text">Complete your password reset securely within the app</div>
            </div>
        </div>

        <div class="download-section">
            <p class="download-text">Don't have the app yet?</p>
            <div class="store-buttons">
                <a href="#" class="store-button">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4483-.9993.9993-.9993c.5511 0 .9993.4483.9993.9993.0001.5511-.4482.9997-.9993.9997zm-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4483.9993.9993 0 .5511-.4483.9997-.9993.9997zm11.4045-6.02l1.9973-3.4592c.1638-.2839.0668-.6484-.2171-.8122-.2839-.1638-.6484-.0668-.8122.2171l-2.0223 3.5033c-1.5235-.6821-3.2329-1.0655-5.0417-1.0655-1.8088 0-3.5182.3834-5.0417 1.0655L4.7527 5.2876c-.1638-.2839-.5283-.3809-.8122-.2171-.2839.1638-.3809.5283-.2171.8122l1.9973 3.4592C2.6191 10.8492.5 13.5954.5 16.8485c0 .1111.0088.2198.0259.3272H23.474c.0171-.1074.0259-.2161.0259-.3272 0-3.2531-2.1191-5.9993-5.226-7.5271z"/>
                    </svg>
                    Google Play
                </a>
                <a href="#" class="store-button">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    App Store
                </a>
            </div>
        </div>

        <div class="footer">
            Need help? <a href="mailto:support@exposvre.com">Contact Support</a>
        </div>
    </div>
</body>
</html>