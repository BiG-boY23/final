<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartGate | EVSU - Ormoc Campus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #741b1b;
            --primary-light: #8b2d2d;
            --secondary: #fdb913;
            --secondary-dark: #e5a711;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --radius: 8px;
            --transition: all 0.3s ease;
        }

        html {
            scroll-behavior: smooth;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            padding-left: 0 !important;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Navigation */
        nav {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--primary);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo img {
            height: 40px;
        }

        .btn-nav-login {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .btn-nav-login:hover {
            background: #f1f5f9;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
            opacity: 0.8;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-link:hover {
            opacity: 1;
            color: var(--primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Responsive Nav */
        @media (max-width: 640px) {
            .nav-links .nav-link {
                display: none; /* Hide detailed links on mobile for simplicity, or handle with a menu */
            }
        }

        /* Hero Section */
        .hero {
            padding: 4rem 0;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            line-height: 1.2;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .hero-content .subtitle {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .hero-content .tagline {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .hero-image img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        /* General Section Styling */
        section {
            padding: 5rem 0;
            scroll-margin-top: 80px;
        }

        .section-title {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-align: center;
        }

        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            height: 100%;
        }

        /* Features & Overview Grid */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: flex-start;
        }

        .feature-item i {
            color: var(--secondary);
            font-size: 1.25rem;
            margin-top: 0.25rem;
        }

        /* How it Works */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .step {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Policy Section */
        .policy-list {
            list-style: none;
        }

        .policy-list li {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            gap: 1rem;
        }

        .policy-list li:last-child {
            border-bottom: none;
        }

        .policy-list i {
            color: var(--primary);
            margin-top: 0.25rem;
        }

        /* Footer */
        footer {
            background: #1e293b;
            color: white;
            padding: 4rem 0 2rem;
            text-align: center;
        }

        .footer-logo {
            height: 60px;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .data-privacy {
            background: #f1f5f9;
            padding: 2rem;
            border-radius: var(--radius);
            margin-top: 3rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-grid, .grid-3 {
                grid-template-columns: 1fr;
            }
            .hero-content {
                text-align: center;
            }
            .hero-content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
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

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#741b1b'
                });
            });
        </script>
    @endif
    <nav>
        <div class="container">
            <a href="/" class="logo">
                <img src="{{ asset('images/evsu-logo.png') }}" alt="EVSU Logo">
                SmartGate
            </a>
            <div class="nav-links">
                <a href="#home" class="nav-link">Home</a>
                <a href="#overview" class="nav-link">Overview</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">Process</a>
                <a href="#policy" class="nav-link">Policy</a>
                <a href="#instructions" class="nav-link">Instructions</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-nav-login">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">Login Portal</a>
                @endauth
            </div>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h1>SmartGate</h1>
                    <p class="subtitle">RFID-Enabled Vehicle Entry and Gate Pass Automation System</p>
                    <p class="tagline">Automating Campus Vehicle Access for Enhanced Security and Efficiency</p>
                    <a href="{{ route('online-registration') }}" class="btn btn-primary">
                        <i class="fas fa-car"></i>&nbsp; Register Vehicle Online
                    </a>
                </div>
                <div class="hero-image">
                    <img src="{{ asset('images/hero_final.png') }}" alt="EVSU Ormoc Campus Gate">
                </div>
            </div>
        </div>
    </section>

    <section id="overview" style="background: white;">
        <div class="container">
            <h2 class="section-title">System Overview</h2>
            <div class="card" style="max-width: 800px; margin: 0 auto; text-align: center;">
                <p style="font-size: 1.1rem; margin-bottom: 2rem;">
                    SmartGate is an RFID-based system designed to automate vehicle entry and exit at Eastern Visayas State University. 
                    The system aims to streamline campus access management through cutting-edge automation technology.
                </p>
                <div class="grid-3" style="text-align: left;">
                    <div class="feature-item"><i class="fas fa-check-circle"></i> <span>Automatic gate pass validation</span></div>
                    <div class="feature-item"><i class="fas fa-check-circle"></i> <span>Real-time time logging</span></div>
                    <div class="feature-item"><i class="fas fa-check-circle"></i> <span>Reduced traffic congestion</span></div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" style="background: #f8fafc;">
        <div class="container">
            <h2 class="section-title">Key Features</h2>
            <div class="grid-3">
                <div class="card">
                    <div style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-id-card"></i></div>
                    <h3 style="margin-bottom: 1rem;">RFID Identification</h3>
                    <p>Uses high-frequency RFID tags for instant and accurate vehicle identification.</p>
                </div>
                <div class="card">
                    <div style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-door-open"></i></div>
                    <h3 style="margin-bottom: 1rem;">Automated Access</h3>
                    <p>Seamlessly controls gate mechanisms based on validated RFID credentials.</p>
                </div>
                <div class="card">
                    <div style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-history"></i></div>
                    <h3 style="margin-bottom: 1rem;">Real-time Logs</h3>
                    <p>Maintains precise records of all vehicle movements for security auditing.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works">
        <div class="container">
            <h2 class="section-title">How the System Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <p><strong>Online Registration:</strong> Users submit their vehicle and personal details via the online portal.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <p><strong>Verification:</strong> The system administrator reviews and approves the registration request.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <p><strong>Tag Issuance:</strong> Once approved, the official RFID tag is issued to the vehicle owner.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <p><strong>Gate Scanning:</strong> The RFID scanner at the gate detects the tag as the vehicle approaches.</p>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <p><strong>Auto Logging:</strong> entry and exit times are automatically recorded in the database.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="policy" style="background: #f8fafc;">
        <div class="container">
            <h2 class="section-title">Campus Vehicle Registration Policy</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem;">
                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem;">1. Coverage</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-check-circle"></i> <span>Open to: Students, Teaching Staff, Non-Teaching Personnel, and Authorized EVSU Officials.</span></li>
                        <li><i class="fas fa-check-circle"></i> <span>Only officially registered vehicles are allowed campus entry.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">2. No Valid Registration – No Entry</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-times-circle"></i> <span>No valid RFID tag or expired registration means no campus entry.</span></li>
                        <li><i class="fas fa-id-badge"></i> <span>RFID tags serve as the digital equivalent of official stickers.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">3. Registration Validity</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-calendar-alt"></i> <span>Each approved tag is valid for one (1) year.</span></li>
                        <li><i class="fas fa-sync-alt"></i> <span>Annual renewal and revalidation is required.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">4. Online Registration Requirements</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-file-upload"></i> <span>Certificate of Registration (CR) and Official Receipt (OR).</span></li>
                        <li><i class="fas fa-file-upload"></i> <span>COM/Student ID (Students) or Employee ID (Staff).</span></li>
                        <li><i class="fas fa-file-upload"></i> <span>Valid Driver's License.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">5. RFID Tag Usage Policy</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-info-circle"></i> <span>One RFID tag per vehicle; non-transferable.</span></li>
                        <li><i class="fas fa-lock"></i> <span>Tampering or unauthorized use is grounds for deactivation.</span></li>
                        <li><i class="fas fa-exclamation-triangle"></i> <span>Lost, damaged (e.g., water damage), or defective tags must be replaced immediately. A replacement fee of 50 Pesos shall apply.</span></li>
                    </ul>
                </div>

                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem;">6. Security Inspection</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-shield-alt"></i> <span>Users must comply with security inspection procedures.</span></li>
                        <li><i class="fas fa-user-shield"></i> <span>Automation does not remove security staff authority.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">7. Parking & Traffic Regulations</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-tachometer-alt"></i> <span>10 kph speed limit inside the campus.</span></li>
                        <li><i class="fas fa-parking"></i> <span>Park only in designated parking areas.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">8. Vehicle Transfer and Updates</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-exchange-alt"></i> <span>Report vehicle ownership changes immediately.</span></li>
                        <li><i class="fas fa-edit"></i> <span>Failure to update records may result in deactivation.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">9. Liability and Responsibility</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-car-crash"></i> <span>EVSU is not liable for loss or damage to vehicles.</span></li>
                        <li><i class="fas fa-user-injured"></i> <span>Vehicle owners are responsible for campus incidents.</span></li>
                    </ul>

                    <h3 style="color: var(--primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 0.5rem; margin-top: 2rem;">10. Data Privacy</h3>
                    <ul class="policy-list">
                        <li><i class="fas fa-user-lock"></i> <span>Data used solely for access control and security.</span></li>
                        <li><i class="fas fa-balance-scale"></i> <span>Compliant with Data Privacy Act of 2012 (RA 10173).</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="instructions" style="background: white;">
        <div class="container">
            <h2 class="section-title">Registration Instructions</h2>
            <div class="steps" style="max-width: 800px; margin: 0 auto;">
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <p><strong>Complete the Online Form:</strong> Fill out all required personal and vehicle details in the <a href="{{ route('online-registration') }}" style="color: var(--primary); font-weight: 700;">Online Registration Portal</a>.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <p><strong>Upload Documents:</strong> Provide clear and readable photos/scans of your CR, OR, License, and EVSU ID (School or Employee).</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <p><strong>Submit for Verification:</strong> Our administrators will review your application and documents for accuracy.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div>
                        <p><strong>Approval Notification:</strong> Wait for notification regarding your application status via your provided contact details.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <div>
                        <p><strong>Claim RFID Tag:</strong> Upon approval, visit the designated EVSU office to claim and install your official RFID tag.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer style="padding: 2rem 0; background: #1b1b18; color: white; text-align: center;">
        <div class="container" style="display: flex; align-items: center; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <p style="margin: 0; font-size: 1.1rem;">&copy; 2026 SmartGate System Developed by</p>
            <img src="{{ asset('images/chocobol-logo.png') }}" alt="Chocobol Logo" style="height: 120px; width: auto;">
        </div>
    </footer>
</body>
</html>
