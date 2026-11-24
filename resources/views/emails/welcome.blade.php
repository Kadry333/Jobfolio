<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to jobfolio</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #18181b;
            line-height: 1.6;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e4e4e7;
        }
        .header {
            padding: 32px 32px 24px;
            background: #ffffff;
            border-bottom: 1px solid #f4f4f5;
            text-align: center;
        }
        .brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #18181b;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .badge-pill {
            display: inline-block;
            background-color: #f4f4f5;
            color: #71717a;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            margin-top: 6px;
        }
        .content {
            padding: 32px;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #18181b;
        }
        p {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 15px;
            color: #3f3f46;
        }
        .features-list {
            margin: 24px 0;
            padding: 0;
            list-style: none;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 14px;
            font-size: 14px;
            color: #3f3f46;
        }
        .feature-icon {
            display: inline-block;
            width: 22px;
            margin-right: 12px;
            font-size: 16px;
            text-align: center;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 16px;
        }
        .btn-primary {
            display: inline-block;
            background-color: #18181b;
            color: #ffffff !important;
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #27272a;
        }
        .footer {
            background-color: #fafafa;
            border-top: 1px solid #f4f4f5;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #a1a1aa;
        }
        .footer a {
            color: #71717a;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">🎯 jobfolio</div>
            <div>
                <span class="badge-pill">Job Application Tracker</span>
            </div>
        </div>

        <div class="content">
            <h1>Welcome aboard, {{ $user->firstName }}! 👋</h1>
            <p>
                Thanks for signing up for jobfolio. Your account is ready, and you can jump straight into organizing your job search.
            </p>

            <p>Here is what you can do with jobfolio right away:</p>

            <ul class="features-list">
                <li class="feature-item">
                    <span class="feature-icon">📋</span>
                    <div>
                        <strong>Full Pipeline Tracking:</strong> Keep track of every job opening through stages from <em>Will Apply</em>, <em>Applied</em>, <em>Interview</em>, to <em>Offer</em>.
                    </div>
                </li>
                <li class="feature-item">
                    <span class="feature-icon">👥</span>
                    <div>
                        <strong>Recruiter & Contact Directory:</strong> Store key recruiters, hiring managers, and referral contacts with outreach notes.
                    </div>
                </li>
                <li class="feature-item">
                    <span class="feature-icon">📄</span>
                    <div>
                        <strong>Resume & CV Hub:</strong> Upload multiple resumes and automatically attach your default CV to applications with one click.
                    </div>
                </li>
                <li class="feature-item">
                    <span class="feature-icon">⏰</span>
                    <div>
                        <strong>Follow-up Reminders:</strong> Set scheduled reminders so you never miss an interview prep or email deadline.
                    </div>
                </li>
            </ul>

            <div class="cta-container">
                <a href="{{ url('/dashboard') }}" class="btn-primary" target="_blank">
                    Go to My Dashboard &rarr;
                </a>
            </div>

            <p style="font-size: 13px; color: #71717a; margin-top: 24px;">
                Have questions or feedback? Simply reply to this email or visit your account settings at any time.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">
                Sent with ❤️ by <strong>jobfolio</strong> &bull;
                <a href="{{ url('/dashboard') }}">Dashboard</a> &bull;
                <a href="{{ url('/settings/profile') }}">Account Settings</a>
            </p>
        </div>
    </div>
</body>
</html>
