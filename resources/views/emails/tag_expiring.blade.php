<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Tag Validity Warning</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 30px; border-bottom: 2px solid #f8fafc; }
        .logo { height: 60px; }
        .content { padding: 40px; color: #334155; line-height: 1.6; }
        .status-badge { display: inline-block; background-color: #fee2e2; color: #991b1b; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; }
        h1 { color: #1e293b; font-size: 1.5rem; margin-top: 0; margin-bottom: 20px; font-weight: 800; }
        .summary-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; margin: 30px 0; }
        .summary-card h3 { margin-top: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #94a3b8; font-weight: 600; font-size: 0.9rem; }
        .value { color: #1e293b; font-weight: 800; font-size: 0.9rem; }
        .important-note { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin-top: 30px; font-size: 0.9rem; color: #92400e; }
        .footer { background-color: #f8fafc; padding: 30px; text-align: center; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="left">
                        <img src="{{ $message->embed(public_path('images/evsu-logo.png')) }}" alt="EVSU Logo" class="logo">
                    </td>
                    <td align="right">
                        <img src="{{ $message->embed(public_path('images/chocobol-logo.png')) }}" alt="Chocobol Logo" class="logo">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="content">
            <div class="status-badge">Action Required: Expiry Warning</div>
            <h1>Your RFID Tag validity is about to expire.</h1>
            <p>Hello, {{ $registration->full_name }}. This is an official notice that your vehicle's RFID tag validity for the EVSU SmartGate system is nearing its expiration date.</p>

            <div class="summary-card">
                <h3>Validity Details</h3>
                <div class="detail-row">
                    <span class="label">Vehicle</span>
                    <span class="value">{{ $registration->make_brand }} ({{ $registration->plate_number }})</span>
                </div>
                <div class="detail-row">
                    <span class="label">Tag ID</span>
                    <span class="value" style="font-family: monospace;">{{ $registration->rfid_tag_id }}</span>
                </div>
                <div class="detail-row" style="background: #fff1f2; margin: 0 -25px; padding: 15px 25px;">
                    <span class="label" style="color: #be123c;">EXPIRATION DATE</span>
                    <span class="value" style="color: #be123c; font-size: 1rem;">{{ \Carbon\Carbon::parse($registration->validity_to)->format('F d, Y') }}</span>
                </div>
            </div>

            <p><strong>To maintain uninterrupted access to the campus, please visit the MESO office to renew your registration and settle the necessary fees.</strong></p>

            <div class="important-note">
                <strong>NOTE:</strong> Failure to renew before the expiration date will result in the automatic deactivation of your RFID tag at all campus gates.
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} EVSU SmartGate System • Team Chocobol<br>
            This is an automated notification. Please do not reply.
        </div>
    </div>
</body>
</html>
