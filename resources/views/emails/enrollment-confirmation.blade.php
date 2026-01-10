<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Confirmation</title>
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
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 30px;
        }

        .course-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #9333ea;
        }

        .course-title {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .course-instructor {
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
            margin: 20px 0;
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
            <h1>🎉 You're Enrolled!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{{ $user->name }}</strong>,</p>
            <p>Congratulations! You've successfully enrolled in a new course.</p>

            <div class="course-card">
                <div class="course-title">{{ $course->title }}</div>
                <div class="course-instructor">by {{ $course->instructor->name ?? 'Instructor' }}</div>
            </div>

            <p>You now have full access to all course content. Start learning today!</p>

            <center>
                <a href="{{ config('app.url') }}/learn/course/{{ $course->slug }}" class="btn">
                    Start Learning →
                </a>
            </center>

            <p>Happy learning,<br>The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>