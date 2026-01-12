<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Course Completed!</title>
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
            text-align: center;
        }

        .celebration {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
        }

        .course-card {
            background: linear-gradient(135deg, #a435f0 0%, #8710d8 100%);
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            color: white;
            text-align: center;
        }

        .course-card h3 {
            margin: 0 0 10px 0;
            font-size: 22px;
        }

        .course-card p {
            margin: 0;
            opacity: 0.9;
        }

        .btn {
            display: inline-block;
            background: #a435f0;
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 5px;
        }

        .btn-secondary {
            background: white;
            color: #a435f0 !important;
            border: 2px solid #a435f0;
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

        .stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            text-align: center;
        }

        .stat h4 {
            margin: 0;
            font-size: 28px;
            color: #a435f0;
        }

        .stat p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #6a6f73;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <div class="celebration">🎉🏆🎉</div>

            <h2>Congratulations, {{ $user->name }}!</h2>

            <p style="text-align: center; font-size: 18px;">You've successfully completed:</p>

            <div class="course-card">
                <h3>{{ $course->title }}</h3>
                <p>by {{ $course->instructor->name ?? 'Instructor' }}</p>
            </div>

            <p style="text-align: center;">This is a huge achievement! You've put in the time and effort to learn
                something new, and that dedication will take you far.</p>

            @if($certificate)
                <p style="text-align: center;">
                    <strong>Your certificate is ready!</strong>
                </p>
                <p style="text-align: center;">
                    <a href="{{ config('app.url') }}/dashboard" class="btn">View Certificate</a>
                </p>
            @endif

            <hr style="border: none; border-top: 1px solid #e8e8e8; margin: 30px 0;">

            <p style="text-align: center;"><strong>What's next?</strong></p>
            <ul>
                <li>Share your achievement on LinkedIn or social media</li>
                <li>Apply what you've learned to real projects</li>
                <li>Explore more courses to continue growing</li>
            </ul>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn btn-secondary">Browse More Courses</a>
            </p>

            <p>Keep up the great work!<br>The {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>