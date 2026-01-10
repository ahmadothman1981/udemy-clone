<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }

        body {
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            box-sizing: border-box;
        }

        .certificate {
            background: #fff;
            border: 3px solid #9333ea;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .certificate::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid #e9d5ff;
            border-radius: 15px;
            pointer-events: none;
        }

        .header {
            margin-bottom: 30px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #9333ea;
            letter-spacing: 2px;
        }

        .title {
            font-size: 42px;
            color: #1f2937;
            margin: 20px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .subtitle {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 40px;
        }

        .recipient-label {
            font-size: 14px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .recipient-name {
            font-size: 36px;
            color: #9333ea;
            margin: 15px 0;
            font-style: italic;
            border-bottom: 2px solid #e9d5ff;
            padding-bottom: 10px;
            display: inline-block;
        }

        .course-label {
            font-size: 14px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 30px;
        }

        .course-name {
            font-size: 24px;
            color: #1f2937;
            margin: 15px 0;
            font-weight: bold;
        }

        .details {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
            margin-top: 5px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .verify-text {
            font-size: 11px;
            color: #9ca3af;
        }

        .certificate-id {
            font-size: 12px;
            color: #6b7280;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="header">
            <div class="logo">UDEMY CLONE</div>
        </div>

        <div class="title">Certificate of Completion</div>
        <div class="subtitle">This is to certify that</div>

        <div class="recipient-label">Recipient</div>
        <div class="recipient-name">{{ $user->name }}</div>

        <div class="course-label">Has successfully completed</div>
        <div class="course-name">{{ $course->title }}</div>

        <div class="details">
            <div class="detail-item">
                <div class="detail-label">Date Issued</div>
                <div class="detail-value">{{ $certificate->issued_at->format('F j, Y') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Certificate ID</div>
                <div class="detail-value">{{ $certificate->certificate_number }}</div>
            </div>
        </div>

        <div class="footer">
            <p class="verify-text">
                Verify this certificate at: {{ config('app.url') }}/verify/{{ $certificate->certificate_number }}
            </p>
        </div>
    </div>
</body>

</html>