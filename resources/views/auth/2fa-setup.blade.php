@php
    $layout = $isEnabled ? 'layouts.app' : 'layouts.minimal';
@endphp

@extends($layout)

@section('title', 'Security Settings — 2FA')
@section('subtitle', 'Manage Two-Factor Authentication to protect your account.')

@section('content')
<div class="table-container" style="max-width: 860px; margin: 0 auto; {{ !$isEnabled ? 'padding: 2.5rem;' : '' }}">

    {{-- ── Status Banner ── --}}
    @if(session('success'))
        <div class="alert-success mb-6">
            <i class="ph ph-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Recovery Codes Modal (shown once after activation) ── --}}
    @if(session('2fa_just_activated') && session('recovery_codes'))
    <div id="recovery-modal-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:20px;padding:2.5rem;max-width:480px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,0.3);">
            <div style="text-align:center;margin-bottom:1.5rem;">
                <div style="font-size:2.5rem;margin-bottom:0.75rem;">🎉</div>
                <h2 style="font-size:1.3rem;font-weight:800;color:#1e293b;">2FA Activated Successfully!</h2>
                <p style="font-size:0.85rem;color:#64748b;margin-top:0.5rem;line-height:1.5;">Save these recovery codes in a <strong>safe place</strong>. Each code can only be used once if you lose access to your phone.</p>
            </div>

            <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    @foreach(session('recovery_codes') as $code)
                    <div style="font-family:monospace;font-size:0.9rem;font-weight:700;color:#1e293b;background:#fff;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;text-align:center;">{{ $code }}</div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button onclick="copyRecovery()" style="flex:1;padding:0.75rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;font-weight:600;font-size:0.85rem;cursor:pointer;color:#475569;" id="copy-btn">
                    <i class="ph ph-copy"></i> Copy Codes
                </button>
                <button onclick="document.getElementById('recovery-modal-overlay').remove()" style="flex:1;padding:0.75rem;background:#741b1b;color:white;border:none;border-radius:10px;font-weight:700;font-size:0.85rem;cursor:pointer;">
                    <i class="ph ph-check"></i> I've Saved Them
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Header Card ── --}}
    <div class="stat-card" style="display:flex;align-items:center;gap:1.5rem;padding:2rem;margin-bottom:1.5rem;background:linear-gradient(135deg,#1e1b4b,#312e81);color:white;border:none;">
        <div style="width:64px;height:64px;background:rgba(255,255,255,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">
            🔐
        </div>
        <div>
            <h1 style="font-size:1.25rem;font-weight:800;margin-bottom:0.3rem;">Two-Factor Authentication (TOTP)</h1>
            <p style="font-size:0.85rem;opacity:0.8;line-height:1.5;">Add a second layer of security using Google Authenticator, Authy, or any TOTP app. Even if your password is stolen, your account stays safe.</p>
        </div>
        <div style="margin-left:auto;flex-shrink:0;">
            @if($isEnabled)
                <span style="background:rgba(16,185,129,0.2);color:#6ee7b7;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:700;border:1px solid rgba(16,185,129,0.3);">
                    <i class="ph ph-shield-check"></i> ENABLED
                </span>
            @else
                <span style="background:rgba(239,68,68,0.2);color:#fca5a5;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:700;border:1px solid rgba(239,68,68,0.3);">
                    <i class="ph ph-shield-slash"></i> DISABLED
                </span>
            @endif
        </div>
    </div>

    @if(!$isEnabled)
    {{-- ── SETUP FLOW ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        {{-- Step 1: Download App --}}
        <div class="stat-card" style="padding:1.75rem;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem;">
                <div style="width:32px;height:32px;background:#ede9fe;color:#7c3aed;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">1</div>
                <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;">Install an Authenticator App</h3>
            </div>
            <p style="font-size:0.82rem;color:#64748b;line-height:1.6;margin-bottom:1rem;">Download any TOTP-compatible authenticator on your phone:</p>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#1e293b;font-size:0.82rem;font-weight:600;transition:background 0.2s;">
                    <span style="font-size:1.2rem;">🤖</span> Google Authenticator (Android)
                </a>
                <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#1e293b;font-size:0.82rem;font-weight:600;transition:background 0.2s;">
                    <span style="font-size:1.2rem;">🍎</span> Google Authenticator (iOS)
                </a>
                <a href="https://authy.com/download/" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#1e293b;font-size:0.82rem;font-weight:600;transition:background 0.2s;">
                    <span style="font-size:1.2rem;">🔑</span> Authy (Multi-device backup)
                </a>
            </div>
        </div>

        {{-- Step 2: Scan QR --}}
        <div class="stat-card" style="padding:1.75rem;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem;">
                <div style="width:32px;height:32px;background:#fef3c7;color:#d97706;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">2</div>
                <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;">Scan this QR Code</h3>
            </div>
            <p style="font-size:0.82rem;color:#64748b;line-height:1.6;margin-bottom:1.25rem;">Open your authenticator app → tap <strong>+</strong> → <strong>Scan QR code</strong>.</p>
            <div style="display:flex;justify-content:center;margin-bottom:1rem;">
                <div style="padding:16px;background:white;border-radius:16px;border:2px solid #e2e8f0;display:inline-block;box-shadow:0 4px 15px rgba(0,0,0,0.06);">
                    {!! $qrSvg !!}
                </div>
            </div>
            <details style="font-size:0.78rem;color:#94a3b8;">
                <summary style="cursor:pointer;font-weight:600;color:#64748b;">Can't scan? Enter manually</summary>
                <div style="margin-top:8px;background:#f8fafc;border-radius:8px;padding:10px;font-family:monospace;font-size:0.8rem;color:#475569;word-break:break-all;border:1px solid #e2e8f0;">
                    {{ $tempSecret }}
                </div>
            </details>
        </div>
    </div>

    {{-- Step 3: Verify --}}
    <div class="stat-card" style="padding:1.75rem;margin-top:1.5rem;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem;">
            <div style="width:32px;height:32px;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.9rem;flex-shrink:0;">3</div>
            <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;">Verify & Activate</h3>
        </div>
        <p style="font-size:0.82rem;color:#64748b;line-height:1.6;margin-bottom:1.5rem;">Enter the 6-digit code shown in your authenticator app to confirm setup is correct and activate 2FA.</p>

        @if ($errors->has('code'))
            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:10px 14px;color:#b91c1c;font-size:0.82rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
                <i class="ph ph-warning-circle"></i> {{ $errors->first('code') }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.activate') }}" style="display:flex;gap:12px;align-items:flex-end;">
            @csrf
            <div style="flex:1;">
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">6-Digit Code from App</label>
                <input
                    type="text"
                    name="code"
                    id="activate-code"
                    placeholder="000000"
                    maxlength="6"
                    pattern="\d{6}"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    style="width:100%;padding:0.875rem 1rem;border:1.5px solid #e2e8f0;border-radius:12px;font-size:1.5rem;font-weight:700;letter-spacing:0.3em;text-align:center;outline:none;transition:border-color 0.2s;font-family:monospace;"
                    onfocus="this.style.borderColor='#741b1b'"
                    onblur="this.style.borderColor='#e2e8f0'"
                >
            </div>
            <button type="submit" style="padding:0.875rem 2rem;background:linear-gradient(135deg,#741b1b,#991b1b);color:white;border:none;border-radius:12px;font-weight:700;font-size:0.9rem;cursor:pointer;white-space:nowrap;box-shadow:0 6px 15px rgba(116,27,27,0.3);transition:transform 0.15s;">
                <i class="ph ph-shield-check"></i> Activate 2FA
            </button>
        </form>
    </div>

    @else
    {{-- ── ALREADY ENABLED STATE ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Status Card --}}
        <div class="stat-card" style="padding:1.75rem;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
                <div style="font-size:2rem;">🛡️</div>
                <div>
                    <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;">2FA is Active</h3>
                    <p style="font-size:0.8rem;color:#64748b;">Your account is protected with TOTP authentication.</p>
                </div>
            </div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:1rem;font-size:0.82rem;color:#15803d;line-height:1.6;">
                <i class="ph ph-check-circle"></i>
                Every login from an Admin or Office account requires your authenticator code — even if the password is compromised.
            </div>
        </div>

        {{-- Recovery Codes --}}
        <div class="stat-card" style="padding:1.75rem;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem;">
                <div style="font-size:1.5rem;">🗝️</div>
                <div>
                    <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;">Recovery Codes</h3>
                    <p style="font-size:0.8rem;color:#64748b;">{{ count($recoveryCodes) }} remaining</p>
                </div>
            </div>
            @if(count($recoveryCodes) > 0)
                <div style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px;padding:12px;display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:1rem;">
                    @foreach($recoveryCodes as $code)
                        <div style="font-family:monospace;font-size:0.8rem;font-weight:700;color:#374151;background:white;padding:6px 10px;border-radius:6px;border:1px solid #e2e8f0;text-align:center;">{{ $code }}</div>
                    @endforeach
                </div>
            @else
                <div style="background:#fef2f2;border-radius:10px;padding:10px 14px;color:#991b1b;font-size:0.82rem;margin-bottom:1rem;">
                    <i class="ph ph-warning"></i> No recovery codes left. Disable and re-enable 2FA to generate new ones.
                </div>
            @endif
        </div>
    </div>

    {{-- Disable 2FA --}}
    <div class="stat-card" style="padding:1.75rem;margin-top:1.5rem;border:2px solid #fef2f2;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem;">
            <div style="font-size:1.5rem;">⚠️</div>
            <div>
                <h3 style="font-size:0.95rem;font-weight:700;color:#991b1b;">Disable Two-Factor Authentication</h3>
                <p style="font-size:0.8rem;color:#64748b;">This will remove the TOTP protection from your account.</p>
            </div>
        </div>

        @if ($errors->has('password'))
            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:10px 14px;color:#b91c1c;font-size:0.82rem;margin-bottom:1rem;">
                <i class="ph ph-warning-circle"></i> {{ $errors->first('password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.deactivate') }}" style="display:flex;gap:12px;align-items:flex-end;">
            @csrf
            @method('DELETE')
            <div style="flex:1;">
                <label style="display:block;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Confirm with your password</label>
                <input type="password" name="password" placeholder="Your current password" style="width:100%;padding:0.75rem 1rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.875rem;outline:none;" required>
            </div>
            <button type="submit"
                onclick="return confirm('Are you sure? This will remove 2FA protection from your account.')"
                style="padding:0.75rem 1.5rem;background:#fef2f2;color:#991b1b;border:1.5px solid #fca5a5;border-radius:10px;font-weight:700;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                <i class="ph ph-shield-slash"></i> Disable 2FA
            </button>
        </form>
    </div>
    @endif

</div>

<style>
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
        border-radius: 12px;
        padding: 0.875rem 1.25rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 1.5rem;
    }
    .mb-6 { margin-bottom: 1.5rem; }
    details summary::-webkit-details-marker { display: none; }
</style>

<script>
    function copyRecovery() {
        const codes = @json(session('recovery_codes', []));
        navigator.clipboard.writeText(codes.join('\n')).then(() => {
            const btn = document.getElementById('copy-btn');
            btn.innerHTML = '<i class="ph ph-check"></i> Copied!';
            btn.style.color = '#16a34a';
        });
    }

    // Auto-submit activate form on 6 digits
    const activateInput = document.getElementById('activate-code');
    if (activateInput) {
        activateInput.addEventListener('input', function() {
            if (this.value.replace(/\D/g,'').length === 6) {
                this.form.submit();
            }
        });
    }
</script>
@if(!$isEnabled)
    <div style="text-align: center; margin-top: 1.5rem;">
        <a href="{{ route('logout') }}" style="color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem;">
            <i class="ph ph-arrow-left"></i> Cancel and sign out
        </a>
    </div>
@endif
@endsection
