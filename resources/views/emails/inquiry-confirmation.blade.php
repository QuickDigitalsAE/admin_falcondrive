<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isAdminMail ? 'New Inquiry Received' : 'Thank You For Your Inquiry' }}</title>
</head>
<body style="margin:0;padding:24px;background:#f3f0e8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:700px;margin:0 auto;background:#ffffff;border:1px solid #eadfbe;border-radius:24px;overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,0.08);">
        <div style="padding:28px 32px;background:linear-gradient(135deg,#fff7e6 0%,#fffdf8 100%);border-bottom:1px solid #f0e6ca;">
            <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:#f8e8b2;color:#8b6717;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">
                Falcon Drive
            </div>
            <h1 style="margin:18px 0 10px;font-size:30px;line-height:1.2;color:#0f172a;">
                {{ $isAdminMail ? 'New Inquiry Received' : 'Thank you for your inquiry' }}
            </h1>
            <p style="margin:0;font-size:15px;line-height:1.8;color:#475569;">
                @if($isAdminMail)
                    A new inquiry has been submitted through the website. Client details are below.
                @else
                    Hi {{ $inquiry->name }}, we have received your inquiry successfully. Our team will review your request and contact you soon.
                @endif
            </p>
        </div>

        <div style="padding:32px;">
            <div style="margin:0 0 24px;padding:22px;background:#fffdf8;border:1px solid #eadfbe;border-radius:18px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding:0 0 14px;font-size:13px;font-weight:700;color:#8b6717;text-transform:uppercase;letter-spacing:0.08em;">Inquiry Details</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 12px;font-size:14px;line-height:1.7;color:#334155;"><strong style="color:#0f172a;">Name:</strong> {{ $inquiry->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:0 0 12px;font-size:14px;line-height:1.7;color:#334155;"><strong style="color:#0f172a;">Number:</strong> {{ $inquiry->number }}</td>
                    </tr>
                    @if($inquiry->email)
                        <tr>
                            <td style="padding:0 0 12px;font-size:14px;line-height:1.7;color:#334155;"><strong style="color:#0f172a;">Email:</strong> {{ $inquiry->email }}</td>
                        </tr>
                    @endif
                    @if($inquiry->promo_code)
                        <tr>
                            <td style="padding:0 0 12px;font-size:14px;line-height:1.7;color:#334155;"><strong style="color:#0f172a;">Promo Code:</strong> {{ $inquiry->promo_code }}</td>
                        </tr>
                    @endif
                    @if($inquiry->car_name)
                        <tr>
                            <td style="padding:0 0 12px;font-size:14px;line-height:1.7;color:#334155;"><strong style="color:#0f172a;">Car Name:</strong> {{ $inquiry->car_name }}</td>
                        </tr>
                    @endif
                    @if($inquiry->message)
                        <tr>
                            <td style="padding:0;font-size:14px;line-height:1.8;color:#334155;"><strong style="color:#0f172a;">Message:</strong><br>{{ $inquiry->message }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div style="padding:18px 20px;background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0;">
                <p style="margin:0;font-size:13px;line-height:1.8;color:#64748b;">
                    @if($isAdminMail)
                        This email was generated automatically from the Falcon Drive inquiry form.
                    @else
                        This is an automated confirmation email from Falcon Drive. Please keep it for your reference.
                    @endif
                </p>
            </div>
        </div>

        <div style="padding:20px 32px;background:#fffaf0;border-top:1px solid #f0e6ca;">
            <p style="margin:0;font-size:13px;line-height:1.7;color:#64748b;">Falcon Drive</p>
        </div>
    </div>
</body>
</html>
