<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Confirmed</title>
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

        .course-card {
            background: #f7f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .course-card h3 {
            margin: 0 0 10px 0;
            color: #1c1d1f;
        }

        .course-card p {
            margin: 0;
            color: #6a6f73;
            font-size: 14px;
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

            <h2>You're enrolled! 🎓</h2>

            <p>Hi {{ $user->name }},</p>

            <p>Great news! You've successfully enrolled in:</p>

            <div class="course-card">
                <h3>{{ $course->title }}</h3>
                <p>by {{ $course->instructor->name ?? 'Instructor' }}</p>
            </div>

            <p>Your learning journey begins now. Jump right in and start making progress toward your goals!</p>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/learn/course/{{ $course->slug }}" class="btn">Start Learning Now</a>
            </p>

            <p><strong>Tips for success:</strong></p>
            <ul>
                <li>Set aside dedicated time for learning each day</li>
                <li>Take notes and practice what you learn</li>
                <li>Complete quizzes to test your understanding</li>
                <li>Don't hesitate to ask questions in the Q&A section</li>
            </ul>

            <p>Happy learning!<br>The {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>