<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — SmartGate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6366f1, #8b5cf6);
            top: -100px; left: -100px;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #741b1b, #dc2626);
            bottom: -100px; right: -100px;
            animation-delay: -4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 2.5rem;
        }
        .brand-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #741b1b, #991b1b);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 8px 20px rgba(116,27,27,0.4);
        }
        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .brand-name span { color: #818cf8; }

        .shield-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .shield-circle {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.2));
            border: 2px solid rgba(99,102,241,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            color: #818cf8;
            animation: pulse-ring 2s ease-in-out infinite;
        }
        @keyframes pulse-ring {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.3); }
            50%       { box-shadow: 0 0 0 12px rgba(99,102,241,0); }
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #f1f5f9;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            font-size: 0.85rem;
            color: #94a3b8;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .error-box {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: #fca5a5;
            font-size: 0.83rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-group {
            margin-bottom: 1.25rem;
        }
        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .code-input {
            width: 100%;
            padding: 1rem 1.25rem;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.3em;
            text-align: center;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', monospace;
        }
        .code-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }
        .code-input::placeholder { color: rgba(255,255,255,0.2); letter-spacing: 0.3em; }

        .btn-verify {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 1.25rem;
            box-shadow: 0 8px 20px rgba(99,102,241,0.3);
        }
        .btn-verify:hover  { transform: translateY(-1px); box-shadow: 0 12px 25px rgba(99,102,241,0.4); }
        .btn-verify:active { transform: translateY(0); }

        .divider {
            display: flex; align-items: center; gap: 12px;
            color: #475569; font-size: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.08);
        }

        .recovery-link {
            display: block;
            text-align: center;
            font-size: 0.82rem;
            color: #94a3b8;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 0.65rem;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
            background: transparent;
            width: 100%;
            font-family: 'Inter', sans-serif;
            margin-bottom: 1.5rem;
        }
        .recovery-link:hover { background: rgba(255,255,255,0.05); color: #cbd5e1; }

        .logout-link {
            display: block;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }
        .logout-link:hover { color: #94a3b8; }

        /* Recovery mode */
        #recovery-mode { display: none; }
        .recovery-input {
            width: 100%;
            padding: 0.85rem 1rem;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-align: center;
            outline: none;
            transition: border-color 0.2s;
            font-family: 'Inter', monospace;
            text-transform: uppercase;
        }
        .recovery-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.2); }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <div class="brand-icon">🔐</div>
            <div class="brand-name">Smart<span>Gate</span></div>
        </div>

        <div class="shield-wrap">
            <div class="shield-circle">
                <i class="ph ph-shield-check"></i>
            </div>
        </div>

        <h1>Two-Factor Authentication</h1>
        <p class="subtitle">Open your authenticator app and enter the 6-digit verification code for SmartGate.</p>

        @if ($errors->any())
            <div class="error-box">
                <i class="ph ph-warning-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- TOTP Mode --}}
        <div id="totp-mode">
            <form method="POST" action="{{ route('2fa.verify') }}" autocomplete="off">
                @csrf
                <div class="input-group">
                    <label for="code">Authenticator Code</label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        class="code-input"
                        placeholder="000000"
                        maxlength="6"
                        pattern="\d{6}"
                        autofocus
                        inputmode="numeric"
                        autocomplete="one-time-code"
                    >
                </div>

                <button type="submit" class="btn-verify">
                    <i class="ph ph-check-circle"></i>
                    Verify & Continue
                </button>
            </form>

            <div class="divider">or</div>

            <button class="recovery-link" onclick="switchMode('recovery')">
                <i class="ph ph-key"></i>
                Use a Recovery Code instead
            </button>
        </div>

        {{-- Recovery Mode --}}
        <div id="recovery-mode">
            <form method="POST" action="{{ route('2fa.verify') }}" autocomplete="off">
                @csrf
                <div class="input-group">
                    <label for="recovery-code">Recovery Code</label>
                    <input
                        type="text"
                        name="code"
                        id="recovery-code"
                        class="recovery-input"
                        placeholder="XXXXX-XXXXX"
                        maxlength="11"
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-verify" style="background: linear-gradient(135deg,#d97706,#f59e0b); box-shadow: 0 8px 20px rgba(217,119,6,0.3);">
                    <i class="ph ph-key"></i>
                    Submit Recovery Code
                </button>
            </form>

            <div class="divider">or</div>

            <button class="recovery-link" onclick="switchMode('totp')">
                <i class="ph ph-device-mobile"></i>
                Use Authenticator App instead
            </button>
        </div>

        <a href="{{ route('logout') }}" class="logout-link">
            <i class="ph ph-sign-out"></i> Sign out and return to login
        </a>
    </div>

    <script>
        function switchMode(mode) {
            document.getElementById('totp-mode').style.display     = mode === 'totp'     ? 'block' : 'none';
            document.getElementById('recovery-mode').style.display = mode === 'recovery' ? 'block' : 'none';

            if (mode === 'totp')     document.getElementById('code').focus();
            if (mode === 'recovery') document.getElementById('recovery-code').focus();
        }

        // Auto-submit on 6 digits
        document.getElementById('code').addEventListener('input', function () {
            if (this.value.replace(/\D/g, '').length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
