<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
        }

        .header {
            background: linear-gradient(135deg, #9333ea 0%, #6366f1 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 28px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            margin-top: 10px;
        }

        .content {
            padding: 30px;
        }

        .tips {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }

        .tip {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .tip:last-child {
            margin-bottom: 0;
        }

        .tip-icon {
            font-size: 24px;
            margin-right: 15px;
        }

        .tip-text {
            flex: 1;
        }

        .tip-title {
            font-weight: bold;
            color: #1f2937;
        }

        .tip-desc {
            color: #6b7280;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            background: #9333ea;
            color: #fff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}! 👋</h1>
            <p>Your learning journey begins now</p>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $user->name }}</strong>,</p>
            <p>Thanks for joining {{ config('app.name') }}! We're excited to have you as part of our learning community.
            </p>

            <div class="tips">
                <div class="tip">
                    <span class="tip-icon">📚</span>
                    <div class="tip-text">
                        <div class="tip-title">Browse Courses</div>
                        <div class="tip-desc">Explore thousands of courses taught by expert instructors</div>
                    </div>
                </div>
                <div class="tip">
                    <span class="tip-icon">🎯</span>
                    <div class="tip-text">
                        <div class="tip-title">Learn at Your Pace</div>
                        <div class="tip-desc">Access courses anytime, anywhere, on any device</div>
                    </div>
                </div>
                <div class="tip">
                    <span class="tip-icon">🏆</span>
                    <div class="tip-text">
                        <div class="tip-title">Earn Certificates</div>
                        <div class="tip-desc">Complete courses and showcase your achievements</div>
                    </div>
                </div>
            </div>

            <center>
                <a href="{{ config('app.url') }}" class="btn">
                    Start Exploring →
                </a>
            </center>

            <p style="margin-top: 30px;">Happy learning,<br>The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>