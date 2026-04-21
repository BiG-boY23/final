<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartGate</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: #f1f5f9;
            color: #334155;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            min-height: 100vh;
            margin: 0;
            padding-left: 0; /* Override global sidebar padding */
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #741b1b;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #741b1b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .brand-logo {
            width: 80px;
            height: auto;
            margin-bottom: 0.5rem;
        }
        .brand span { color: #f59e0b; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: #64748b; font-weight: 500; }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #334155;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 0.8rem;
            background: #741b1b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .btn-submit:hover { background: #5a1515; }
        .error-msg {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
            background: #fef2f2;
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #fee2e2;
        }
        .login-footer {
            margin-top: 1rem;
            text-align: center;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            color: #64748b;
            font-size: 0.8rem;
        }
        .login-footer img {
            height: 150px;
            margin: 0;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="brand">
                <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo" class="brand-logo" style="width: 80px; height: auto;">
                <div>Smart<span>Gate</span></div>
            </div>
            <p style="color: #64748b; font-size: 0.9rem;">Sign in to your account</p>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: "{{ session('success') }}",
                        confirmButtonColor: '#741b1b'
                    });
                });
            </script>
        @endif

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: "{{ $errors->first() }}",
                        confirmButtonColor: '#741b1b'
                    });
                });
            </script>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>


    </div>

    <div class="login-footer">
        <p>&copy; {{ date('Y') }} SmartGate System</p>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span>Developed by</span>
            <img src="{{ asset('images/chocobol-logo.png') }}" alt="Chocobol Logo">
        </div>
    </div>
</body>
</html>
