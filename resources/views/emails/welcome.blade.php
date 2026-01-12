<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1c1d1f;
            margin: 0;
            padding: 0;
            background: #f7f9fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #a435f0;
            margin: 0;
            font-size: 28px;
        }

        h2 {
            color: #1c1d1f;
            margin-top: 0;
        }

        .btn {
            display: inline-block;
            background: #a435f0;
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }

        .btn:hover {
            background: #8710d8;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6a6f73;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <h2>Welcome, {{ $user->name }}! 🎉</h2>

            <p>Thank you for joining our learning community! You've just taken the first step toward achieving your
                goals.</p>

            <p>With {{ config('app.name') }}, you can:</p>
            <ul>
                <li>Access thousands of courses from expert instructors</li>
                <li>Learn at your own pace, anytime, anywhere</li>
                <li>Earn certificates to showcase your skills</li>
                <li>Track your progress and achievements</li>
            </ul>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn">Start Learning</a>
            </p>

            <p>If you have any questions, our support team is always here to help.</p>

            <p>Happy learning!<br>The {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>