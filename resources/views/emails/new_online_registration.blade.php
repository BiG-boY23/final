<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Online Registration</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 30px; border-bottom: 2px solid #f8fafc; text-align: center; }
        .logo { height: 60px; }
        .content { padding: 40px; color: #334155; line-height: 1.6; }
        .status-badge { display: inline-block; background-color: #eff6ff; color: #1e40af; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; }
        h1 { color: #1e293b; font-size: 1.5rem; margin-top: 0; margin-bottom: 20px; font-weight: 800; }
        .summary-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 25px; margin: 30px 0; }
        .summary-card h3 { margin-top: 0; font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #94a3b8; font-weight: 600; font-size: 0.9rem; }
        .value { color: #1e293b; font-weight: 800; font-size: 0.9rem; }
        .btn-wrapper { margin-top: 30px; text-align: center; }
        .btn { display: inline-block; padding: 14px 34px; background-color: #1e293b; color: #ffffff !important; border-radius: 12px; text-decoration: none; font-weight: 800; }
        .footer { background-color: #f8fafc; padding: 30px; text-align: center; font-size: 0.8rem; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('images/chocobol-logo.png')) }}" alt="Chocobol Logo" class="logo">
        </div>
        
        <div class="content">
            <div class="status-badge">New Submission</div>
            <h1>A new vehicle registration has been submitted online.</h1>
            <p>Hello Admin, a new applicant has just submitted their details and documents through the online portal.</p>

            <div class="summary-card">
                <h3>Application Details</h3>
                <div class="detail-row">
                    <span class="label">Applicant</span>
                    <span class="value">{{ $registration->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Role</span>
                    <span class="value">{{ ucfirst($registration->role) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Plate Number</span>
                    <span class="value" style="font-family: monospace;">{{ $registration->plate_number }}</span>
                </div>
            </div>

            <p>Please review the uploaded documents in the administrative dashboard to verify or reject this application.</p>

            <div class="btn-wrapper">
                <a href="{{ route('office.users') }}" class="btn">View in Dashboard</a>
            </div>

            <p style="margin-top: 40px;">Regards,<br><strong>SmartGate - Team Chocobol</strong></p>
        </div>

        <div class="footer">
            &copy; 2026 EVSU SmartGate System • Team Chocobol<br>
            This is an automated administrative notification.
        </div>
    </div>
</body>
</html>
