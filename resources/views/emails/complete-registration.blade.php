{{-- resources/views/emails/complete-registration.blade.php - FINAL VERSION --}}
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #00BFFF;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px dashed #00BFFF;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            border-radius: 4px;
        }
        .instructions {
            background-color: #e8f4f8;
            padding: 20px;
            border-left: 4px solid #00BFFF;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        .instructions li {
            margin: 8px 0;
        }
        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 13px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .divider {
            height: 1px;
            background-color: #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $appName }}</h1>
            <p style="margin: 10px 0 0 0;">Complete Your Registration</p>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>Your patient account has been created at {{ $appName }}. Welcome aboard! 🎉</p>
            
            <div class="instructions">
                <strong>📱 Complete your registration in 4 easy steps:</strong>
                <ol>
                    <li>Open the <strong>MediLink mobile app</strong></li>
                    <li>Tap <strong>"Complete Registration"</strong> on the login screen</li>
                    <li>Enter your email and the token below</li>
                    <li>Set your password and complete your profile</li>
                </ol>
            </div>
            
            <p><strong>Your Email:</strong></p>
            <div class="code-box" style="background-color: #e8f4f8; border-color: #00BFFF;">
                {{ $user->email }}
            </div>
            
            <p><strong>Your Registration Token:</strong></p>
            <div class="code-box">
                {{ $token }}
            </div>
            
            <p style="text-align: center; color: #666; font-size: 14px; margin-top: 30px;">
                <strong>💡 Tip:</strong> Copy the token above and paste it in the app
            </p>
            
            <div class="note">
                <strong>⏰ Important:</strong> This registration link will expire in <strong>7 days</strong>. Please complete your registration before then.
            </div>
            
            <div class="divider"></div>
            
            <p style="color: #666; font-size: 14px;">
                If you did not request this account, please ignore this email or contact us if you have concerns.
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 5px 0;"><strong>{{ $appName }}</strong></p>
            <p style="margin: 5px 0;">Your Health, Our Priority</p>
            <p style="margin: 15px 0 5px 0; color: #999;">© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>