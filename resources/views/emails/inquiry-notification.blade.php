<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Inquiry Received</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 20px 24px; }
        .header h1 { margin: 0; font-size: 18px; font-weight: 600; }
        .content { padding: 24px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px; }
        .value { font-size: 15px; color: #0f172a; }
        .message-box { background: #f1f5f9; border-radius: 6px; padding: 16px; white-space: pre-wrap; font-size: 14px; line-height: 1.6; }
        .button { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 14px; margin-top: 20px; }
        .footer { font-size: 12px; color: #94a3b8; text-align: center; padding: 16px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Inquiry Received</h1>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">Name</div>
                <div class="value">{{ $inquiry->name }}</div>
            </div>

            @if ($inquiry->email)
                <div class="field">
                    <div class="label">Email</div>
                    <div class="value"><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
                </div>
            @endif

            @if ($inquiry->phone)
                <div class="field">
                    <div class="label">Phone</div>
                    <div class="value"><a href="tel:{{ $inquiry->phone }}">{{ $inquiry->phone }}</a></div>
                </div>
            @endif

            @if ($inquiry->subject)
                <div class="field">
                    <div class="label">Subject</div>
                    <div class="value">{{ $inquiry->subject }}</div>
                </div>
            @endif

            <div class="field">
                <div class="label">Message</div>
                <div class="message-box">{{ $inquiry->message }}</div>
            </div>

            @if ($inquiry->source_url)
                <div class="field">
                    <div class="label">Submitted from</div>
                    <div class="value" style="font-size: 13px; color: #64748b;">{{ $inquiry->source_url }}</div>
                </div>
            @endif

            <div>
                <a href="{{ url('/admin/inquiries/'.$inquiry->id) }}" class="button" style="color: #ffffff;">View in Admin Panel</a>
            </div>
        </div>
        <div class="footer">
            Received via {{ site('site_name') ?? config('app.name') }} &bull; {{ $inquiry->created_at->format('M j, Y g:i A') }}
        </div>
    </div>
</body>
</html>
