<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }

        .code-box {
            background: #fff;
            border: 2px dashed #667eea;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .code {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 3px;
        }

        .message-box {
            background: #fff;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
            font-style: italic;
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎁 You've Received a Gift!</h1>
        </div>
        <div class="content">
            <p>Hello{{ $gift->recipient_name ? ' ' . $gift->recipient_name : '' }}!</p>

            <p><strong>{{ $buyerName }}</strong> has sent you a special gift: <strong>{{ $itemTitle }}</strong></p>

            @if($message)
                <div class="message-box">
                    <p>"{{ $message }}"</p>
                    <p style="text-align: right; margin-bottom: 0;">- {{ $buyerName }}</p>
                </div>
            @endif

            <p>To redeem your gift, use the following code:</p>

            <div class="code-box">
                <div class="code">{{ $redemptionCode }}</div>
            </div>

            <p>Visit our website and enter this code on the gift redemption page to unlock your course!</p>

            <center>
                <a href="{{ config('app.url') }}/gifts/redeem" class="btn">Redeem Your Gift</a>
            </center>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                This code is valid for one-time use only. If you have any questions, please contact our support team.
            </p>
        </div>
    </div>
</body>

</html>