<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Rejected</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fef2f2; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 30px; border-bottom: 2px solid #fee2e2; }
        .logo { height: 60px; }
        .content { padding: 40px; color: #334155; line-height: 1.6; }
        .status-badge { display: inline-block; background-color: #fef2f2; color: #991b1b; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; border: 1px solid #fee2e2; margin-bottom: 20px; }
        h1 { color: #1e293b; font-size: 1.5rem; margin-top: 0; margin-bottom: 20px; font-weight: 800; }
        .rejection-box { background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 16px; padding: 25px; margin: 30px 0; }
        .rejection-box h3 { margin-top: 0; font-size: 0.75rem; color: #991b1b; text-transform: uppercase; letter-spacing: 0.1em; }
        .rejection-reason { font-size: 1rem; font-weight: 800; color: #7f1d1d; }
        .btn-wrapper { margin-top: 30px; text-align: center; }
        .btn { display: inline-block; padding: 14px 34px; background-color: #1e293b; color: #ffffff !important; border-radius: 12px; text-decoration: none; font-weight: 800; }
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
            <div class="status-badge">Action Required</div>
            <h1>Action Required: Vehicle Registration Rejected.</h1>
            <p>Hello {{ $registration->full_name }}. We have reviewed your vehicle registration submitted via the SmartGate portal. Unfortunately, we cannot proceed with your registration at this time due to the following reason:</p>

            <div class="rejection-box">
                <h3>Reason for Rejection</h3>
                <div class="rejection-reason">
                    {{ $registration->rejection_reason }}
                </div>
            </div>

            <p>To continue with your application, please correct the issues mentioned above and re-upload the correct documents through your user dashboard. Our office team will re-review your submission immediately upon re-upload.</p>

            <div class="btn-wrapper">
                <a href="{{ route('online-registration') }}" class="btn">Re-upload Documents</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} EVSU SmartGate System • Team Chocobol<br>
            This is an automated notification. Please do not reply.
        </div>
    </div>
</body>
</html>
