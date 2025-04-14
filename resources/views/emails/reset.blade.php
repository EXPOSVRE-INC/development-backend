<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reset Your Password - EXPOSVRE</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f2f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f2f4f6">
        <tr>
            <td style="text-align: -webkit-center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="margin-top: 30px; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr style="background-color: black;">
                        <td style="padding: 30px; text-align: -webkit-center">
                            <img src="https://240c3b.p3cdn1.secureserver.net/wp-content/uploads/2023/09/EXPOSURE-Logo-white-on-pink.png?time=1715872980"
                                alt="EXPOSVRE Logo" style="display: block; height: 50px;">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #333333;">Reset Your Password</h2>
                            <p style="color: #555555; font-size: 16px; line-height: 1.5;">
                                Hello {{ $user->name ?? $user->email }},
                                <br><br>
                                We received a request to reset your password. Click the button below to set a new
                                password for your EXPOSVRE account.
                            </p>

                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $link }}"
                                    style="background-color: #1E40AF; color: #ffffff; padding: 14px 28px; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 6px;">
                                    Reset Password
                                </a>
                            </div>

                            <p style="color: #777777; font-size: 14px; line-height: 1.6;">
                                If the button above doesn’t work, copy and paste this URL into your browser:
                            </p>

                            <p style="color: #1E40AF; word-break: break-all; font-size: 14px;">
                                {{ $link }}
                            </p>

                            <p style="color: #999999; font-size: 12px;">
                                If you didn’t request a password reset, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #999999;">
                            <p>&copy; {{ date('Y') }} EXPOSVRE. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #cccccc; margin-top: 20px;">
                    Having trouble? Contact <a href="mailto:support@exposvre.com"
                        style="color: #1E40AF;">support@exposvre.com</a>
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
