<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vehicle Access Registration | SmartGate</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            --primary: #741b1b;
            --primary-dark: #4d0a0a;
            --primary-glass: rgba(116, 27, 27, 0.08);
            --accent: #fdb913;
            --accent-dark: #b45309;
            --bg-body: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --white: #ffffff;
            --radius-xl: 32px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-premium: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(116, 27, 27, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(253, 185, 19, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }

        /* Hero Background with Geometry */
        .page-header-bg {
            position: fixed;
            top: 0; left: 0; right: 0; height: 450px;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            z-index: -1;
            clip-path: polygon(0 0, 100% 0, 100% 75%, 0 100%);
            overflow: hidden;
        }

        .page-header-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.05;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
            padding: 1.5rem;
            position: relative;
        }

        /* Navigation */
        .nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0 2.5rem;
            color: white;
        }

        .logo-area { display: flex; align-items: center; gap: 12px; }
        .logo-icon {
            width: 48px; height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
        }
        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .exit-btn {
            text-decoration: none;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.7rem 1.4rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            display: flex; align-items: center; gap: 8px;
        }
        .exit-btn:hover { background: rgba(0, 0, 0, 0.3); transform: translateX(-4px); }

        /* Progress Stepper */
        .stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
            padding: 0 1rem;
        }

        .stepper::before {
            content: '';
            position: absolute;
            top: 20px; left: 10%; right: 10%;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
            z-index: 0;
        }

        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 80px;
        }

        .step-circle {
            width: 42px; height: 42px;
            background: #4d0a0a;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 700;
            transition: var(--transition);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: center;
        }

        .step.active .step-circle {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--primary-dark);
            box-shadow: 0 0 20px rgba(253, 185, 19, 0.3);
            transform: scale(1.1);
        }

        .step.active .step-label { color: white; opacity: 1; }

        .step.completed .step-circle {
            background: white;
            border-color: white;
            color: var(--primary);
        }

        /* Glass Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-premium);
            border: 1px solid rgba(255, 255, 255, 0.4);
            overflow: hidden;
            animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            padding: 3rem 3rem 1.5rem;
            text-align: center;
        }

        .form-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -1px;
        }

        .form-header p {
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-weight: 500;
        }

        /* Slide Transition System */
        .slides-wrapper {
            position: relative;
            padding: 0 3rem 3rem;
            min-height: 400px;
        }

        .form-slide {
            display: none;
            animation: fadeIn 0.5s ease-out;
        }

        .form-slide.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Input Controls */
        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .full-width { grid-column: span 2; }

        label {
            display: block;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0.6rem;
            padding-left: 4px;
        }

        input, select {
            width: 100%;
            padding: 0.9rem 1.2rem;
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            outline: none;
        }

        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glass);
            background: white;
        }

        /* Navigation Buttons */
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 3rem 3rem;
            background: rgba(248, 250, 252, 0.5);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 0.9rem;
            padding: 1rem 2rem;
            border-radius: 14px;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex; align-items: center; gap: 10px;
            border: none;
        }

        .btn-next {
            background: var(--primary);
            color: white;
            margin-left: auto;
            box-shadow: 0 10px 20px rgba(116, 27, 27, 0.2);
        }

        .btn-next:hover { transform: translateY(-3px); filter: brightness(1.1); }

        .btn-back {
            background: white;
            color: var(--text-muted);
            border: 2px solid #e2e8f0;
        }
        .btn-back:hover { background: #f8fafc; color: var(--text-main); }

        /* File Upload */
        .upload-card {
            background: white;
            border: 2px dashed #cbd5e1;
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .upload-card:hover { border-color: var(--primary); background: var(--primary-glass); }
        .upload-card i { font-size: 2rem; color: var(--text-muted); margin-bottom: 0.5rem; }
        .upload-card.verified { border-color: #10b981; background: rgba(16, 185, 129, 0.05); }
        .upload-card input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

        /* Summary View */
        .summary-box {
            background: #f8fafc;
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid #e2e8f0;
        }
        .summary-group { margin-bottom: 1.5rem; }
        .summary-label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; }
        .summary-value { font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); }

        /* Chained dropdown helpers */
        select:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
        .chain-hint { font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
        .step-tag {
            display: inline-flex; align-items: center; justify-content: center;
            width: 20px; height: 20px; background: #741b1b; color: white;
            border-radius: 50%; font-size: 0.7rem; font-weight: 800; margin-right: 4px;
        }
        .dd-loader {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        }
        .dd-spinner {
            width: 16px; height: 16px; border: 2px solid #741b1b;
            border-top-color: transparent; border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Privacy */
        .privacy-box {
            margin-top: 2rem;
            background: #fff9eb;
            border: 1px solid #fde68a;
            padding: 1.2rem;
            border-radius: 14px;
            display: flex; gap: 12px;
            align-items: flex-start;
        }

        .privacy-checkbox { width: 22px; height: 22px; margin-top: 3px; cursor: pointer; accent-color: var(--primary); }
        .privacy-text { font-size: 0.85rem; font-weight: 600; color: #92400e; line-height: 1.4; }

        /* Loader */
        .loader-overlay {
            position: absolute; inset: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none; align-items: center; justify-content: center;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .input-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: auto; }
            .slides-wrapper, .form-header, .form-footer { padding: 1.5rem; }
            .stepper { display: none; }
        }
    </style>
</head>
<body>
    <div class="page-header-bg"></div>
    
    <div class="container" id="registration-wizard">
        <nav class="nav-header">
            <div class="logo-area">
                <div class="logo-icon"><i class="ph-bold ph-shield-check" style="color: white;"></i></div>
                <div class="logo-text">SMARTGATE</div>
            </div>
            <a href="{{ route('landing') }}" class="exit-btn">
                <i class="ph-bold ph-sign-out"></i> EXIT
            </a>
        </nav>

        <!-- Dynamic Stepper -->
        <div class="stepper">
            <div class="step active" id="step-1">
                <div class="step-circle">1</div>
                <span class="step-label">Basic Info</span>
            </div>
            <div class="step" id="step-2">
                <div class="step-circle">2</div>
                <span class="step-label">Vehicle</span>
            </div>
            <div class="step" id="step-3">
                <div class="step-circle">3</div>
                <span class="step-label">Uploads</span>
            </div>
            <div class="step" id="step-4">
                <div class="step-circle">4</div>
                <span class="step-label">Review</span>
            </div>
        </div>

        <form action="{{ route('online-registration.submit') }}" method="POST" enctype="multipart/form-data" id="main-form">
            @csrf
            <div class="form-container">
                <div class="form-header">
                    <h2 id="slide-title">Institutional Access</h2>
                    <p id="slide-subtitle">Please identify your role and provide your personal details.</p>
                </div>

                <div class="slides-wrapper">
                    <!-- Slide 1: Applicant Category -->
                    <div class="form-slide active" id="slide-1-content">
                        <div class="form-group">
                            <label>Designation <span style="color:red">*</span></label>
                            <select name="role" id="role-selector" required>
                                <option value="" disabled selected>Select your institutional role...</option>
                                <option value="student">Student Enrollee</option>
                                <option value="faculty">Personnel</option>
                                <option value="staff">Vendor</option>
                            </select>
                        </div>

                        <div class="input-grid">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" placeholder="Juan" required data-summary="Name">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" placeholder="Dela Cruz" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Middle Name (Optional)</label>
                            <input type="text" name="middle_name" placeholder="Protacio">
                        </div>

                        <!-- Dynamic Specifics based on role -->
                        <div id="dynamic-applicant-fields"></div>

                        <div class="input-grid">
                            <div class="form-group">
                                <label id="contact-label">Contact Number <span style="color:red">*</span></label>
                                <input type="text" name="contact_number" id="contact-input" placeholder="09XXXXXXXXX" required data-summary="Phone">
                            </div>
                            <div class="form-group">
                                <label id="email-label">Email Address</label>
                                <input type="email" name="email_address" id="email-input" placeholder="juan@evsu.edu.ph" required data-summary="Email">
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2: Vehicle Identity (Chained Dropdowns) -->
                    <div class="form-slide" id="slide-2-content">

                        <!-- Step 1: Category -->
                        <div class="form-group">
                            <label for="vehicle-category-selector">
                                <span class="step-tag">1</span> Vehicle Category <span style="color:red">*</span>
                            </label>
                            <select name="vehicle_type" id="vehicle-category-selector" required data-summary="Vehicle Type">
                                <option value="" disabled selected>Select Category…</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" data-id="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <div class="chain-hint"><i class="ph ph-arrow-down"></i> Brand list updates automatically</div>
                        </div>

                        <!-- Step 2: Brand (disabled until category chosen) -->
                        <div class="form-group">
                            <label for="brand-selector">
                                <span class="step-tag">2</span> Manufacturer / Brand <span style="color:red">*</span>
                            </label>
                            <div style="position:relative">
                                <select name="make_brand" id="brand-selector" required data-summary="Brand" disabled>
                                    <option value="" disabled selected>Select Category First…</option>
                                </select>
                                <div id="brand-loader" class="dd-loader" style="display:none"><div class="dd-spinner"></div></div>
                            </div>
                            <input type="text" id="brand-other-input" placeholder="Type Brand Name..." style="display: none; width: 100%; padding: 0.9rem 1.2rem; background: white; border: 2px solid #f1f5f9; border-radius: 8px; margin-top: 10px;">
                            <div class="chain-hint"><i class="ph ph-arrow-down"></i> Model list updates automatically</div>
                        </div>

                        <!-- Step 3: Model (disabled until brand chosen) -->
                        <div class="form-group">
                            <label for="model-selector">
                                <span class="step-tag">3</span> Specific Model <span style="color:red">*</span>
                            </label>
                            <div style="position:relative">
                                <select name="model_name" id="model-selector" required data-summary="Model" disabled>
                                    <option value="" disabled selected>Select Brand First…</option>
                                </select>
                                <div id="model-loader" class="dd-loader" style="display:none"><div class="dd-spinner"></div></div>
                            </div>
                            <input type="text" id="model-other-input" placeholder="Type Model Name..." style="display: none; width: 100%; padding: 0.9rem 1.2rem; background: white; border: 2px solid #f1f5f9; border-radius: 8px; margin-top: 10px;">
                        </div>

                        <div class="form-group">
                            <label>License Plate Number <span style="color:red">*</span></label>
                            <input type="text" name="plate_number" placeholder="ABC 1234" required style="text-transform:uppercase" data-summary="Plate No.">
                        </div>
                    </div>

                    <!-- Slide 3: Digital Verification -->
                    <div class="form-slide" id="slide-3-content">
                        <div class="input-grid">
                            <div class="form-group">
                                <label>Vehicle CR (Cert. of Registration)</label>
                                <div class="upload-card" data-field="cr_file">
                                    <i class="ph ph-file-pdf"></i>
                                    <p>Select File</p>
                                    <input type="file" name="cr_file" accept="image/*" required class="doc-upload">
                                    <div class="loader-overlay"><div style="width:20px;height:20px;border:2px solid #741b1b;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;"></div></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Vehicle OR (Official Receipt)</label>
                                <div class="upload-card" data-field="or_file">
                                    <i class="ph ph-receipt"></i>
                                    <p>Select File</p>
                                    <input type="file" name="or_file" accept="image/*" required class="doc-upload">
                                    <div class="loader-overlay"><div style="width:20px;height:20px;border:2px solid #741b1b;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;"></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Valid Driver's License</label>
                            <div class="upload-card" data-field="license_file">
                                <i class="ph ph-identification-card"></i>
                                <p>Select File</p>
                                <input type="file" name="license_file" accept="image/*" required class="doc-upload">
                                <div class="loader-overlay"><div style="width:20px;height:20px;border:2px solid #741b1b;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;"></div></div>
                            </div>
                        </div>

                        <div id="role-uploads-container" class="input-grid"></div>
                    </div>

                    <!-- Slide 4: Review -->
                    <div class="form-slide" id="slide-4-content">
                        <div class="summary-box">
                            <div class="input-grid">
                                <div class="summary-group"><div class="summary-label">Full Name</div><div class="summary-value" id="sum-name">---</div></div>
                                <div class="summary-group"><div class="summary-label">Institutional Role</div><div class="summary-value" id="sum-role">---</div></div>
                                <div class="summary-group"><div class="summary-label" id="sum-dept-label">Official Station</div><div class="summary-value" id="sum-dept">---</div></div>
                                <div class="summary-group"><div class="summary-label">Contact Details</div><div class="summary-value" id="sum-contact">---</div></div>
                                <div class="summary-group"><div class="summary-label">Vehicle Info</div><div class="summary-value" id="sum-vehicle">---</div></div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div class="summary-label">Attached Documents</div>
                                <div id="sum-files" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:5px;"></div>
                            </div>
                        </div>

                        <div class="privacy-box">
                            <input type="checkbox" id="privacy-consent" class="privacy-checkbox">
                            <label for="privacy-consent" class="privacy-text">
                                I hereby authorize the collection and processing of my data in accordance with the Data Privacy Act. 
                                I confirm that all information provided is true and correct.
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="button" class="btn btn-back" id="btn-back" style="visibility:hidden">
                        <i class="ph ph-arrow-left"></i> BACK
                    </button>
                    <button type="button" class="btn btn-next" id="btn-next">
                        NEXT <i class="ph ph-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-next" id="btn-submit" style="display:none" disabled>
                        EXECUTE FINAL REGISTRATION <i class="ph-bold ph-paper-plane-tilt"></i>
                    </button>
                </div>
            </div>
        </form>
        
        <footer style="text-align: center; margin-top: 3rem; color: rgba(255,255,255,0.6); font-weight: 600; font-size: 0.8rem; padding-bottom: 2rem;">
            &copy; 2026 EVSU SmartGate Intelligence. Automated Campus Transit Verification.
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 1;
            const totalSlides = 4;
            
            const btnNext = document.getElementById('btn-next');
            const btnBack = document.getElementById('btn-back');
            const btnSubmit = document.getElementById('btn-submit');
            const privacyConsent = document.getElementById('privacy-consent');
            
            const slideTitle = document.getElementById('slide-title');
            const slideSubtitle = document.getElementById('slide-subtitle');
            
            const titles = {
                1: ["Institutional Access", "Please identify your role and provide your personal details."],
                2: ["Vehicle Identity", "Define the vehicle you'll be using for campus transit."],
                3: ["Digital Verification", "Upload scanned photos of your official documents for AI verification."],
                4: ["Review & Consent", "One last look. Ensure all details are correct before submission."]
            };

            function updateUI() {
                // Update Visibility
                document.querySelectorAll('.form-slide').forEach(s => s.classList.remove('active'));
                document.getElementById(`slide-${currentSlide}-content`).classList.add('active');
                
                // Stepper Update
                document.querySelectorAll('.step').forEach((s, idx) => {
                    const stepNum = idx + 1;
                    s.classList.remove('active', 'completed');
                    if (stepNum === currentSlide) {
                        s.classList.add('active');
                    } else if (stepNum < currentSlide) {
                        s.classList.add('completed');
                        s.querySelector('.step-circle').innerHTML = '<i class="ph-bold ph-check"></i>';
                    } else {
                        s.querySelector('.step-circle').innerText = stepNum;
                    }
                });

                // Header Update
                slideTitle.innerText = titles[currentSlide][0];
                slideSubtitle.innerText = titles[currentSlide][1];

                // Footer Update
                btnBack.style.visibility = (currentSlide === 1) ? 'hidden' : 'visible';
                
                if (currentSlide === totalSlides) {
                    btnNext.style.display = 'none';
                    btnSubmit.style.display = 'flex';
                    populateSummary();
                } else {
                    btnNext.style.display = 'flex';
                    btnSubmit.style.display = 'none';
                }
            }

            btnNext.onclick = () => {
                if(validateSlide(currentSlide)) {
                    currentSlide++;
                    updateUI();
                }
            };

            btnBack.onclick = () => {
                currentSlide--;
                updateUI();
            };

            privacyConsent.onchange = () => {
                btnSubmit.disabled = !privacyConsent.checked;
                btnSubmit.style.opacity = !privacyConsent.checked ? '0.5' : '1';
            };

            function validateSlide(n) {
                const inputs = document.getElementById(`slide-${n}-content`).querySelectorAll('input[required], select[required]');
                let valid = true;
                inputs.forEach(i => {
                    if(!i.value) {
                        valid = false;
                        i.style.borderColor = '#ef4444';
                    } else {
                        i.style.borderColor = '#f1f5f9';
                    }
                });
                if(!valid) {
                    Swal.fire({ icon: 'warning', title: 'Action Required', text: 'Fill all required fields to continue.', toast:true, position:'top-end', showConfirmButton:false, timer:2000 });
                }
                return valid;
            }

            function populateSummary() {
                const fName = document.getElementsByName('first_name')[0].value;
                const lName = document.getElementsByName('last_name')[0].value;
                const role  = document.getElementById('role-selector').selectedOptions[0].text;
                const phone = document.getElementsByName('contact_number')[0].value;
                const email = document.getElementsByName('email_address')[0].value;
                const plate = document.getElementsByName('plate_number')[0].value;

                const catSel   = document.getElementById('vehicle-category-selector');
                const brandSel = document.getElementById('brand-selector');
                const modelSel = document.getElementById('model-selector');

                const catName   = catSel   && catSel.value   ? catSel.selectedOptions[0].text   : '—';
                const brandName = brandSel && brandSel.value ? brandSel.selectedOptions[0].text : '—';
                const modelName = modelSel && modelSel.value ? modelSel.selectedOptions[0].text : '—';

                document.getElementById('sum-name').innerText    = `${fName} ${lName}`;
                document.getElementById('sum-role').innerText    = role;
                
                // Dept/Office logic
                const roleKey = document.getElementById('role-selector').value;
                let deptVal = '—';
                let deptLabel = 'Department / Office';

                if(roleKey === 'student') {
                    deptVal = document.getElementsByName('college_dept')[0].value + ' (' + document.getElementsByName('course')[0].value + ')';
                    deptLabel = 'College / Course';
                } else if(roleKey === 'faculty') {
                    deptVal = document.getElementsByName('college_dept_faculty')[0].value;
                    deptLabel = 'Assigned Office / Dept';
                } else if(roleKey === 'staff') {
                    deptVal = document.getElementsByName('business_stall_name')[0].value;
                    deptLabel = 'Business / Stall';
                }

                document.getElementById('sum-dept-label').innerText = deptLabel;
                document.getElementById('sum-dept').innerText = deptVal;

                document.getElementById('sum-contact').innerText = `${phone || 'No Contact'} | ${email || 'No Email'}`;
                document.getElementById('sum-vehicle').innerText = `${catName} › ${brandName} ${modelName} (${plate.toUpperCase()})`;

                // Files
                const fileContainer = document.getElementById('sum-files');
                fileContainer.innerHTML = '';
                document.querySelectorAll('input[type="file"]').forEach(f => {
                    if(f.files[0]) {
                        const span = document.createElement('span');
                        span.style = "background:#e2e8f0; padding:4px 10px; border-radius:30px; font-size:0.7rem; font-weight:700; color:#475569;";
                        span.innerText = f.files[0].name;
                        fileContainer.appendChild(span);
                    }
                });
            }


            // ─── AJAX Chained Dropdowns: Category → Brand → Model ─────────────────
            const catSel   = document.getElementById('vehicle-category-selector');
            const brandSel = document.getElementById('brand-selector');
            const modelSel = document.getElementById('model-selector');
            const brandSpinner = document.getElementById('brand-loader');
            const modelSpinner = document.getElementById('model-loader');

            function resetSelect(sel, placeholder) {
                sel.innerHTML = `<option value="" disabled selected>${placeholder}</option>`;
                sel.disabled = true;
            }

            catSel.addEventListener('change', async function () {
                const categoryId = this.selectedOptions[0].dataset.id;
                if (!categoryId) return;
                
                resetSelect(brandSel, 'Loading brands…');
                resetSelect(modelSel, 'Select Brand First…');
                brandSpinner.style.display = 'block';

                // Reset "Other" states
                document.getElementById('brand-other-input').style.display = 'none';
                document.getElementById('brand-other-input').value = '';
                document.getElementById('brand-other-input').required = false;
                brandSel.name = 'make_brand';

                try {
                    const res    = await fetch(`/api/brands/${categoryId}`);
                    const brands = await res.json();

                    resetSelect(brandSel, brands.length ? 'Select Brand…' : 'Select Brand (Generic/Others Available)');
                    
                    brands.forEach(b => {
                        brandSel.innerHTML += `<option value="${b.name}" data-id="${b.id}">${b.name}</option>`;
                    });
                    
                    // Always ensure a fallback option is available
                    brandSel.innerHTML += `<option value="Other" data-id="">Other / Brand Not Listed</option>`;
                    brandSel.disabled = false;
                } catch(e) {
                    resetSelect(brandSel, 'Selection Error');
                    brandSel.innerHTML += `<option value="Other" data-id="">Other / Manual Entry</option>`;
                    brandSel.disabled = false;
                    console.error(e);
                } finally {
                    brandSpinner.style.display = 'none';
                }
            });

            brandSel.addEventListener('change', async function () {
                const brandId = this.selectedOptions[0].dataset.id;
                const isOther = this.value === "Other";

                const brandOtherInput = document.getElementById('brand-other-input');
                if (isOther) {
                    brandOtherInput.style.display = 'block';
                    brandOtherInput.required = true;
                    brandOtherInput.name = 'make_brand';
                    brandSel.name = 'make_brand_select';
                } else {
                    brandOtherInput.style.display = 'none';
                    brandOtherInput.required = false;
                    brandOtherInput.name = '';
                    brandSel.name = 'make_brand';
                }

                resetSelect(modelSel, 'Loading models…');
                modelSpinner.style.display = 'block';

                // Reset "Other" Model state
                document.getElementById('model-other-input').style.display = 'none';
                document.getElementById('model-other-input').value = '';
                document.getElementById('model-other-input').required = false;
                modelSel.name = 'model_name';

                if (!brandId) {
                    modelSel.innerHTML = `<option value="Other" selected>Other / Not Listed</option>`;
                    modelSel.disabled = false;
                    modelSpinner.style.display = 'none';
                    // Trigger "Other" Model logic
                    modelSel.dispatchEvent(new Event('change'));
                    return;
                }

                try {
                    const res    = await fetch(`/api/models/${brandId}`);
                    const models = await res.json();

                    resetSelect(modelSel, models.length ? 'Select Model…' : 'Select Model (Generic/Others Available)');
                    models.forEach(m => {
                        modelSel.innerHTML += `<option value="${m.name}">${m.name}</option>`;
                    });
                    modelSel.innerHTML += `<option value="Other">Other / Brand Not Listed</option>`;
                    modelSel.disabled = false;
                } catch(e) {
                    resetSelect(modelSel, 'Selection Error');
                    modelSel.innerHTML += `<option value="Other">Other / Manual Entry</option>`;
                    modelSel.disabled = false;
                    console.error(e);
                } finally {
                    modelSpinner.style.display = 'none';
                }
            });

            modelSel.addEventListener('change', function() {
                const isOther = this.value === "Other";
                const modelOtherInput = document.getElementById('model-other-input');
                if (isOther) {
                    modelOtherInput.style.display = 'block';
                    modelOtherInput.required = true;
                    modelOtherInput.name = 'model_name';
                    modelSel.name = 'model_name_select';
                } else {
                    modelOtherInput.style.display = 'none';
                    modelOtherInput.required = false;
                    modelOtherInput.name = '';
                    modelSel.name = 'model_name';
                }
            });



            // Role Dynamic Logic
            const roleSelector = document.getElementById('role-selector');
            const dynamicFields = document.getElementById('dynamic-applicant-fields');
            const roleUploads = document.getElementById('role-uploads-container');

            roleSelector.onchange = () => {
                const role = roleSelector.value;
                const roleLabel = document.querySelector('label[for="role-selector"]') || document.querySelector('#slide-1-content label');
                let html = '';
                let uploads = '';

                // Dynamic Label Update
                if (role === 'staff') {
                    roleLabel.innerHTML = 'Vendor / Business Category <span style="color:red">*</span>';
                } else {
                    roleLabel.innerHTML = 'Institutional Designation <span style="color:red">*</span>';
                }

                if (role === 'student') {
                    html = `
                        <div class="input-grid">
                            <div class="form-group"><label>Student ID Number</label><input type="text" name="student_id" placeholder="e.g. 2024-10234" required></div>
                            <div class="form-group">
                                <label>College / Department</label>
                                <select name="college_dept" id="college-selector" required>
                                    <option value="" disabled selected>Select College...</option>
                                    @foreach($colleges as $c)
                                        <option value="{{ $c->name }}" data-courses="{{ json_encode($c->courses->pluck('name')) }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Course / Program</label>
                                <select name="course" id="course-selector" required>
                                    <option value="" disabled selected>Select College First...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Year Level</label>
                                <select name="year_level" required>
                                    <option value="" disabled selected>Select year...</option>
                                    <option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option><option>5th Year</option>
                                </select>
                            </div>
                        </div><input type="hidden" name="access_classification" value="student">`;
                    uploads = `
                        <div class="form-group">
                            <label>COR (Cert. of Registration)</label>
                            <div class="upload-card" data-field="cor_file">
                                <i class="ph ph-certificate"></i><p>Select COR Image</p>
                                <input type="file" name="cor_file" accept="image/*" required class="doc-upload">
                                <div class="loader-overlay"><div class="spinner"></div></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Student ID (Front)</label>
                            <div class="upload-card" data-field="student_id_file">
                                <i class="ph ph-address-book"></i><p>Select ID Image</p>
                                <input type="file" name="student_id_file" accept="image/*" required class="doc-upload">
                                <div class="loader-overlay"><div class="spinner"></div></div>
                            </div>
                        </div>`;
                } else if (role === 'faculty') {
                    html = `
                        <div class="input-grid">
                             <div class="form-group"><label id="faculty-id-label">Personnel ID Number (Optional)</label><input type="text" name="faculty_id" placeholder="F-XXXXX"></div>
                             <div class="form-group">
                                <label>Department / Official Station</label>
                                <select name="college_dept_faculty" required>
                                    <option value="" disabled selected>Select Department/Office...</option>
                                    <optgroup label="Academic Departments">
                                        @foreach($colleges as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Key Administrative Offices">
                                        @foreach($offices as $o)
                                            <option value="{{ $o->name }}">{{ $o->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                             </div>
                        </div>
                        <div class="form-group"><label>Home/Local Address</label><input type="text" name="address" placeholder="Complete address for records" required></div>
                        <input type="hidden" name="access_classification_faculty" value="faculty">`;
                   uploads = `
                        <div class="form-group full-width">
                            <label>Personnel Identity Card</label>
                            <div class="upload-card" data-field="employee_id_file">
                                <i class="ph ph-briefcase"></i><p>Select ID Image</p>
                                <input type="file" name="employee_id_file" accept="image/*" required class="doc-upload">
                                <div class="loader-overlay"><div class="spinner"></div></div>
                            </div>
                        </div>`;
                } else if (role === 'staff') {
                   html = `
                        <div class="input-grid">
                             <div class="form-group"><label>Business / Stall Name</label><input type="text" name="business_stall_name" placeholder="e.g. Aling Nena's Canteen" required></div>
                             <div class="form-group"><label>Stall / Unit Number</label><input type="text" name="vendor_address" placeholder="e.g. Canteen Stall #3, Ground Floor" required></div>
                        </div>
                        <input type="hidden" name="access_classification_staff" value="staff">`;
                   uploads = `
                        <div class="form-group full-width">
                            <label>Proof of Identity / Business Permit</label>
                            <div class="upload-card" data-field="employee_id_file">
                                <i class="ph ph-storefront"></i><p>Select ID or Permit Image</p>
                                <input type="file" name="employee_id_file" accept="image/*" required class="doc-upload">
                                <div class="loader-overlay"><div class="spinner"></div></div>
                            </div>
                        </div>`;
                }
                
                // Email Guidance Update
                const emailLabel = document.getElementById('email-label');
                const emailInput = document.getElementById('email-input');
                const contactLabel = document.getElementById('contact-label');
                const contactInput = document.getElementById('contact-input');

                if (role === 'student') {
                    emailLabel.innerHTML = 'Institutional Email (@evsu.edu.ph) <span style="color:red">*</span>';
                    emailInput.placeholder = 'juan@evsu.edu.ph';
                    emailInput.required = true;
                    contactLabel.innerHTML = 'Contact Number <span style="color:red">*</span>';
                    contactInput.required = true;
                } else if (role === 'faculty') {
                    emailLabel.innerHTML = 'Personal / Work Email (Optional)';
                    emailInput.placeholder = 'personal@email.com';
                    emailInput.required = false;
                    contactLabel.innerHTML = 'Contact Number (Optional)';
                    contactInput.required = false;
                } else {
                    emailLabel.innerHTML = 'Personal / Work Email <span style="color:red">*</span>';
                    emailInput.placeholder = 'personal@email.com';
                    emailInput.required = true;
                    contactLabel.innerHTML = 'Contact Number <span style="color:red">*</span>';
                    contactInput.required = true;
                }

                dynamicFields.innerHTML = html;
                roleUploads.innerHTML = uploads;
                attachDocListeners();
            };

            function attachDocListeners() {
                document.querySelectorAll('.doc-upload').forEach(input => {
                    input.onchange = async function() {
                        const file = this.files[0];
                        const card = this.closest('.upload-card');
                        const type = card.dataset.field; // Wait, field name is cr_file, cor_file etc
                        
                        // Map type to controller expectation
                        let docType = type;
                        if(type === 'cor_file') docType = 'cor_file'; 

                        if(!file) { card.classList.remove('verified'); return; }

                        const loader = card.querySelector('.loader-overlay');
                        loader.style.display = 'flex';

                        const fd = new FormData();
                        fd.append('file', file);
                        fd.append('type', docType);
                        fd.append('_token', '{{ csrf_token() }}');

                        try {
                            const res = await fetch('{{ route("online-registration.validate") }}', { method:'POST', body:fd });
                            const data = await res.json();
                            if(data.success) {
                                card.classList.add('verified');
                                card.querySelector('p').innerText = "VERIFIED: " + file.name;
                                Swal.fire({ toast:true, position:'top-end', icon:'success', title:'File match confirmed.', showConfirmButton:false, timer:1500 });
                            } else {
                                this.value = ''; card.classList.remove('verified'); card.querySelector('p').innerText = "Select File";
                                Swal.fire({ icon:'error', title:'Scan Failed', text: data.message });
                            }
                        } catch(e) { console.error(e); } finally { loader.style.display = 'none'; }
                    };
                });
            }

            attachDocListeners();

            // College → Course chained dropdown (inline data-courses attribute)
            document.body.addEventListener('change', (e) => {
                if(e.target.id === 'college-selector') {
                    const selector = e.target;
                    const courseSelector = document.getElementById('course-selector');
                    const courses = JSON.parse(selector.selectedOptions[0].dataset.courses || '[]');
                    
                    courseSelector.innerHTML = '<option value="" disabled selected>Select Course...</option>';
                    courses.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c; opt.innerText = c;
                        courseSelector.appendChild(opt);
                    });
                }
            });


            document.getElementById('main-form').onsubmit = () => {
                Swal.fire({ title:'SECURE SUBMISSION...', text:'Please wait while we encrypt and primary-save your registration.', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            };
        });
    </script>

    <style> @keyframes spin { to { transform: rotate(360deg); } } </style>
</body>
</html>
