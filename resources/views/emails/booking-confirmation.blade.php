<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isAdminMail ? 'New Booking Received' : 'Booking Confirmation' }}</title>
</head>
<body style="margin:0;padding:24px;background:#f5f0e4;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    @php($currencyIcon = rtrim(config('app.url'), '/') . '/images/durham.png')
    <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #ead7a1;border-radius:24px;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,0.10);">
        <div style="padding:30px 34px;background:linear-gradient(135deg,#fff4d6 0%,#fffaf0 60%,#fffdf8 100%);border-bottom:1px solid #edd9a6;">
            <div style="display:inline-block;padding:8px 14px;border-radius:999px;background:#f6e1a2;color:#8a6513;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">
                Falcon Drive
            </div>
            <h1 style="margin:18px 0 10px;font-size:30px;line-height:1.2;color:#2d2105;">
                {{ $isAdminMail ? 'New booking received' : 'Your booking has been received' }}
            </h1>
            <p style="margin:0;font-size:15px;line-height:1.8;color:#5f4b1c;">
                @if($isAdminMail)
                    A new booking has been submitted through the website. The booking summary is listed below section by section.
                @else
                    Hi {{ $booking->name }}, thank you for booking with Falcon Drive. Our team will review your reservation and contact you shortly.
                @endif
            </p>
        </div>

        <div style="padding:32px 28px;background:#fffdf8;">
            @foreach($sections as $section)
                <div style="margin:0 0 20px;padding:22px;background:#ffffff;border:1px solid #efdfb9;border-radius:18px;">
                    <div style="margin:0 0 14px;font-size:13px;font-weight:700;color:#9a741c;text-transform:uppercase;letter-spacing:0.08em;">
                        {{ $section['title'] }}
                    </div>

                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                        @foreach($section['fields'] as $field)
                            <tr>
                                <td style="padding:10px 0;border-top:1px solid #f5ead0;width:220px;font-size:13px;font-weight:700;line-height:1.6;color:#6b7280;vertical-align:top;">
                                    {{ $field['label'] }}
                                </td>
                                <td style="padding:10px 0;border-top:1px solid #f5ead0;font-size:14px;line-height:1.7;color:#1f2937;vertical-align:top;">
                                    @if($field['is_currency'])
                                        <img src="{{ $currencyIcon }}" width="12" alt="AED" style="vertical-align:middle;margin-right:4px;"> {{ $field['value'] }}
                                    @else
                                        {{ $field['value'] }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endforeach

            <div style="padding:18px 20px;background:#fff7e3;border-radius:16px;border:1px solid #efdfb9;">
                <p style="margin:0;font-size:13px;line-height:1.8;color:#7a5d18;">
                    @if($isAdminMail)
                        This email was generated automatically from the Falcon Drive booking form.
                    @else
                        This is an automated booking confirmation from Falcon Drive. Please keep it for your reference.
                    @endif
                </p>
            </div>
        </div>

        <div style="padding:20px 32px;background:#fff5dc;border-top:1px solid #edd9a6;">
            <p style="margin:0;font-size:13px;line-height:1.7;color:#7a5d18;">Falcon Drive</p>
        </div>
    </div>
</body>
</html>
