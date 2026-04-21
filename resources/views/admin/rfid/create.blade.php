@extends('layouts.app')

@section('title', 'Admin Tag Registration')
@section('subtitle', 'Register new vehicle owners and assign RFID tags with full administrative override.')

@section('content')
<div class="table-container">
    <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: flex-end;">
        <a href="{{ route('admin.rfid') }}" class="btn btn-outline" style="font-size: 0.85rem; font-weight: 700;">
            <i class="ph ph-arrow-left"></i> Back to Directory
        </a>
    </div>

    <form id="registerForm" class="registration-form">
        @csrf
        
        <!-- ================= INSTITUTIONAL ROLE SELECTION ================= -->
        <section class="form-section">
            <h2 class="section-title">
                <i class="ph ph-identification-card"></i> Institutional Role
            </h2>

            <div class="form-group mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select the applicant's role to proceed <span style="color:red">*</span></label>
                <div class="role-options">
                    <label class="role-option">
                        <input type="radio" name="role" value="student">
                        <div class="radio-custom"></div>
                        <span>Student</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="faculty">
                        <div class="radio-custom"></div>
                        <span>Personnel</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="staff">
                        <div class="radio-custom"></div>
                        <span>Vendor</span>
                    </label>
                </div>
            </div>
        </section>

        <!-- Hidden container that appears only after role selection -->
        <div id="registration-details-container" style="display: none;">
            
            <hr class="section-divider">

            <!-- ================= APPLICANT INFORMATION ================= -->
            <section class="form-section">
                <h2 class="section-title">
                    <i class="ph ph-user"></i> Applicant Details
                </h2>

                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label">FIRST NAME</label>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">LAST NAME</label>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">MIDDLE NAME (OPTIONAL)</label>
                        <input type="text" name="middle_name" placeholder="Middle Name">
                    </div>
                    <div class="form-field">
                        <label id="contact-label" class="field-label">CONTACT NUMBER <span style="color:red">*</span></label>
                        <input type="text" name="contact_number" id="contact-input" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="form-field md-col-2">
                        <label id="email-label" class="field-label">EMAIL ADDRESS <span style="color:red">*</span></label>
                        <input type="email" name="email_address" id="email-input" placeholder="email@example.com" required>
                    </div>
                </div>

                <!-- Role-Specific Fields Area -->
                <div id="dynamic-applicant-fields" class="mt-4">
                    <!-- Injected via JavaScript -->
                </div>
            </section>

            <hr class="section-divider">

            <!-- ================= VEHICLE INFORMATION ================= -->
            <section class="form-section">
                <h2 class="section-title">
                    <i class="ph ph-car"></i> Vehicle Identity
                </h2>

                <div class="form-grid">
                    <div class="form-field">
                        <label class="field-label">VEHICLE CATEGORY <span style="color:red">*</span></label>
                        <select name="vehicle_type" id="office-category-selector" required>
                            <option value="" disabled selected>Select Category...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}" data-id="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label class="field-label">BRAND <span style="color:red">*</span></label>
                        <div style="position: relative;">
                            <select name="make_brand" id="office-brand-selector" required disabled>
                                <option value="" disabled selected>Select Category First...</option>
                            </select>
                        </div>
                        <input type="text" id="brand-other-input" placeholder="Type Brand Name..." class="mt-2" style="display: none; width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 8px;">
                    </div>

                    <div class="form-field">
                        <label class="field-label">SPECIFIC MODEL <span style="color:red">*</span></label>
                        <div style="position: relative;">
                            <select name="model_name" id="office-model-selector" required disabled>
                                <option value="" disabled selected>Select Brand First...</option>
                            </select>
                        </div>
                        <input type="text" id="model-other-input" placeholder="Type Model Name..." class="mt-2" style="display: none; width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 8px;">
                    </div>

                    <div class="form-field">
                        <label class="field-label">PLATE NUMBER <span style="color:red">*</span></label>
                        <input type="text" name="plate_number" placeholder="ABC 1234" required style="text-transform: uppercase;">
                    </div>
                </div>
            </section>

            <hr class="section-divider">

            <!-- ================= DIGITAL VERIFICATION CHECKLIST ================= -->
            <section class="form-section">
                <h2 class="section-title">
                    <i class="ph ph-shield-check"></i> Physical Verification Checklist
                </h2>
                <p style="font-size: 0.8rem; color: #64748b; margin-top: -10px; margin-bottom: 20px;">Staff must manually verify these documents. Scans are not stored to comply with Data Privacy regulations.</p>

                <div class="verification-grid">
                    <label class="v-card">
                        <input type="checkbox" name="verified_cr" required>
                        <div class="v-content">
                            <i class="ph ph-file-text"></i>
                            <span>VEHICLE CR VERIFIED</span>
                        </div>
                    </label>
                    <label class="v-card">
                        <input type="checkbox" name="verified_or" required>
                        <div class="v-content">
                            <i class="ph ph-receipt"></i>
                            <span>VEHICLE OR VERIFIED</span>
                        </div>
                    </label>
                    <label class="v-card">
                        <input type="checkbox" name="verified_license" required>
                        <div class="v-content">
                            <i class="ph ph-identification-card"></i>
                            <span>DRIVER'S LICENSE VERIFIED</span>
                        </div>
                    </label>
                    <label class="v-card" id="role-verification-item">
                        <input type="checkbox" name="verified_institutional" required>
                        <div class="v-content">
                            <i class="ph ph-student"></i>
                            <span id="role-v-label">INSTITUTIONAL ID VERIFIED</span>
                        </div>
                    </label>
                </div>
            </section>

            <!-- ================= VALIDITY (Automatically set to 1 year) ================= -->
            <div style="display: none;">
                <input type="date" name="validity_from" value="{{ date('Y-m-d') }}">
                <input type="date" name="validity_to" value="{{ date('Y-m-d', strtotime('+1 year')) }}">
            </div>

            <hr class="section-divider">

            <!-- ================= RFID TAG ASSIGNMENT ================= -->
            <section class="form-section">
                <h2 class="section-title">
                    <i class="ph ph-broadcast"></i> RFID Tag Assignment
                </h2>
                
                <div class="tag-assignment-container" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                        <button type="button" id="modeAuto" class="btn btn-outline active" style="flex:1">Automatic Mode</button>
                        <button type="button" id="modeManual" class="btn btn-outline" style="flex:1">Manual Mode</button>
                    </div>

                    <div id="autoModeContainer">
                        <div class="form-field">
                            <label class="field-label">RFID TAG ID (SCANNED)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="rfid_tag_id" id="rfidTagId" placeholder="Waiting for tag scan..." readonly style="flex-grow:1; background:#f1f5f9; padding: 1.2rem;">
                                <button type="button" id="scanBtn" class="btn btn-primary" style="padding: 0 1.5rem; background: #741b1b; border: none;">
                                    <i class="ph ph-scan"></i> <span id="scanBtnText">Scan Tag</span>
                                </button>
                            </div>
                            <p id="scannerStatus" style="font-size: 0.75rem; color: #64748b; margin-top: 8px;">Hardware scanner ready.</p>
                        </div>
                    </div>

                    <div id="manualModeContainer" style="display:none">
                        <div class="form-grid">
                            <div class="form-field">
                                <label class="field-label">MANUAL TAG ID</label>
                                <input type="text" id="manualRfidTagId" placeholder="Enter ID">
                            </div>
                            <div class="form-field">
                                <label class="field-label">CONFIRM TAG ID</label>
                                <input type="text" id="confirmRfidTagId" placeholder="Repeat ID">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="form-actions mt-8">
                <button type="submit" class="btn btn-primary w-full justify-center" style="height: 54px; font-size: 1.1rem; font-weight: 700; background: #741b1b; border: none; border-radius: 12px;">
                    <i class="ph ph-check-circle"></i> Complete Owner Registration
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .registration-form { max-width: 100%; margin: 0 auto; }
    .form-section { margin-bottom: 2rem; background: white; border-radius: 12px; padding: 0.5rem; }
    .section-title { font-size: 1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.2rem; }
    .section-title i { font-size: 1.1rem; }
    
    .role-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .role-option { display: flex; align-items: center; gap: 0.8rem; padding: 1rem 1.2rem; background: white; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: 0.2s; font-weight: 600; color: #64748b; }
    .role-option input { position: absolute; opacity: 0; width: 0; height: 0; }
    .radio-custom { width: 18px; height: 18px; border: 2px solid #cbd5e1; border-radius: 50%; position: relative; }
    .role-option input:checked + .radio-custom { border-color: #741b1b; }
    .role-option input:checked + .radio-custom::after { content: ''; position: absolute; top: 4px; left: 4px; width: 6px; height: 6px; background: #741b1b; border-radius: 50%; }
    .role-option:has(input:checked) { border-color: #741b1b; color: #1e293b; }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
    .form-field { margin-bottom: 0.5rem; }
    .field-label { display: block; font-size: 0.7rem; font-weight: 700; color: #64748b; margin-bottom: 8px; letter-spacing: 0.3px; }
    .md-col-2 { grid-column: span 2; }
    .section-divider { border: 0; border-top: 1px solid #f1f5f9; margin: 2rem 0; }
    
    input, select { width: 100%; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; font-weight: 500; font-size: 0.9rem; background: #fdfdfd; }
    input:focus, select:focus { border-color: #cbd5e1; }

    /* Verification Cards */
    .verification-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .v-card { position: relative; cursor: pointer; }
    .v-card input { position: absolute; opacity: 0; width: 0; height: 0; }
    .v-content { background: white; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.2rem 0.5rem; display: flex; flex-direction: column; align-items: center; gap: 12px; transition: 0.2s; text-align: center; }
    .v-content i { font-size: 1.2rem; color: #94a3b8; }
    .v-content span { font-size: 0.65rem; font-weight: 700; color: #64748b; line-height: 1.2; }
    .v-card input:checked + .v-content { border-color: #cbd5e1; background: #fff; }
    /* No special checked colors for icons in screenshot unless active */

    .btn-outline.active { background: #1e293b !important; color: white !important; border-color: #1e293b !important; }
</style>

@endsection

@section('scripts')
<script>
    // Robust Data Injection
    const collegesData = @json($colleges);
    
    document.addEventListener('DOMContentLoaded', function() {
        const roleRadios = document.querySelectorAll('input[name="role"]');
        const dynamicFields = document.getElementById('dynamic-applicant-fields');
        const regContainer = document.getElementById('registration-details-container');
        const roleVLabel = document.getElementById('role-v-label');
        const roleVIcon = document.querySelector('#role-verification-item i');
        const mainForm = document.getElementById('registerForm');

        // Role Dynamics
        const populateForm = (data) => {
            // Sync Institutional Role (Radio buttons) FIRST
            if (data.role) {
                const roleInput = mainForm.querySelector(`input[name="role"][value="${data.role}"]`);
                if (roleInput) {
                    roleInput.checked = true;
                    roleInput.dispatchEvent(new Event('change')); // Trigger dynamic field creation
                }
            }

            // After role change, populate dynamic fields and standard fields
            setTimeout(() => {
                // Formatting
                let fN = data.first_name || '';
                let lN = data.last_name || '';
                let mN = data.middle_name || '';
                
                // Fallback for older records stored before first_name/last_name columns
                if (!fN && data.full_name) {
                    const parts = data.full_name.trim().split(' ');
                    lN = parts.pop(); 
                    fN = parts.shift() || ''; 
                    if(parts.length > 0) mN = parts.join(' ');
                }

                // Date field correction for <input type="date"> which requires strict YYYY-MM-DD
                let vFrom = data.validity_from ? data.validity_from.split('T')[0] : '';
                let vTo = data.validity_to ? data.validity_to.split('T')[0] : '';

                // Mapping standard fields (including university_id generated by role)
                const fieldsMap = {
                    'first_name': fN,
                    'last_name': lN,
                    'middle_name': mN,
                    'contact_number': data.contact_number,
                    'email_address': data.email_address,
                    'university_id': data.university_id,
                    'plate_number': data.plate_number,
                    'validity_from': vFrom,
                    'validity_to': vTo,
                };

                for (const [name, val] of Object.entries(fieldsMap)) {
                    const input = mainForm.querySelector(`[name="${name}"]`);
                    if (input) input.value = val || '';
                }

                if (data.role === 'student') {
                    if (data.college_dept) {
                        const sel = document.getElementById('college-selector');
                        if (sel) {
                            sel.value = data.college_dept;
                            sel.dispatchEvent(new Event('change'));
                        }
                    }
                    setTimeout(() => {
                        const courseSel = document.getElementById('course-selector');
                        if (courseSel) courseSel.value = data.course || '';
                    }, 500);
                    
                    const yearSel = mainForm.querySelector('select[name="year_level"]');
                    if (yearSel) yearSel.value = data.year_level || '';
                } else if (data.role === 'faculty') {
                    const deptSel = mainForm.querySelector('select[name="college_dept"]');
                    if (deptSel) deptSel.value = data.college_dept || '';
                } else if (data.role === 'staff') {
                    const biz = mainForm.querySelector('input[name="business_stall_name"]');
                    if (biz) biz.value = data.business_stall_name || '';
                    const loc = mainForm.querySelector('input[name="vendor_address"]');
                    if (loc) loc.value = data.vendor_address || '';
                }

                // Vehicle details population
                if (data.vehicle_type) {
                    const catSel = document.getElementById('office-category-selector');
                    if (catSel) {
                        catSel.value = data.vehicle_type;
                        catSel.dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            const brandSel = document.getElementById('office-brand-selector');
                            if (brandSel) {
                                brandSel.value = data.make_brand;
                                brandSel.dispatchEvent(new Event('change'));
                            }
                            
                            setTimeout(() => {
                                const modelSel = document.getElementById('office-model-selector');
                                if (modelSel) {
                                    modelSel.value = data.model_name;
                                }
                            }, 500);
                        }, 500);
                    }
                }

                // Populate RFID tag using manual mode layout
                if (data.rfid_tag_id) {
                    const manualModeBtn = document.getElementById('modeManual');
                    if (manualModeBtn) manualModeBtn.click();
                    
                    setTimeout(() => {
                        const mRfid = document.getElementById('manualRfidTagId');
                        const cRfid = document.getElementById('confirmRfidTagId');
                        if (mRfid && cRfid) {
                            mRfid.value = data.rfid_tag_id;
                            cRfid.value = data.rfid_tag_id;
                            mRfid.dispatchEvent(new Event('input'));
                        }
                    }, 100);
                }

                // Auto-check all document verification checkboxes 
                // Since this user was already previously evaluated, skip re-evaluating papers on edit
                if (data.id || data.rfid_tag_id) {
                    const verifyChecks = mainForm.querySelectorAll('.v-card input[type="checkbox"]');
                    verifyChecks.forEach(chk => {
                        chk.checked = true;
                    });
                }

            }, 200);

            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Data Populated', showConfirmButton: false, timer: 2000 });
        };

        const fetchExistingData = async (idValue) => {
            if (!idValue) return;
            Swal.fire({ title: 'Searching...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch(`/admin/rfid/fetch-user/${encodeURIComponent(idValue)}`);
                const result = await res.json();
                if (result.success) {
                    populateForm(result.data);
                } else {
                    Swal.fire('No Records', 'This ID is not yet in our registration database.', 'info');
                }
            } catch (err) {
                Swal.fire('Error', 'Communication error while fetching data.', 'error');
            }
        };

        roleRadios.forEach(radio => {
            radio.onchange = () => {
                const role = radio.value;
                regContainer.style.display = 'block';
                regContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Toggle standard field requirements
                const contactInput = document.getElementById('contact-input');
                const contactLabel = document.getElementById('contact-label');
                const emailInput = document.getElementById('email-input');
                const emailLabel = document.getElementById('email-label');

                if (role === 'faculty') {
                    contactInput.required = false;
                    if(contactLabel) contactLabel.innerHTML = 'CONTACT NUMBER <span style="color:gray">(Optional)</span>';
                    emailInput.required = false;
                    if(emailLabel) emailLabel.innerHTML = 'EMAIL ADDRESS <span style="color:gray">(Optional)</span>';
                } else {
                    contactInput.required = true;
                    if(contactLabel) contactLabel.innerHTML = 'CONTACT NUMBER <span style="color:red">*</span>';
                    emailInput.required = true;
                    if(emailLabel) emailLabel.innerHTML = 'EMAIL ADDRESS <span style="color:red">*</span>';
                }

                let html = '';
                if (role === 'student' || role === 'faculty') {
                    const idLabel = role === 'student' ? 'Student ID Number' : 'Personnel ID Number';
                    html = `<div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">${idLabel.toUpperCase()}</label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" name="university_id" id="search-id-input" placeholder="Search ID or Plate..." ${role === 'student' ? 'required' : ''} style="flex-grow:1">
                                <button type="button" id="fetch-search-btn" class="btn" style="background: #1e293b; color: white; padding: 0 1rem; border-radius: 10px;" title="Fetch Records">
                                    <i class="ph ph-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>`;
                    
                    if (role === 'student') {
                        html += `<div class="form-field">
                             <label class="field-label">College / Department</label>
                             <select name="college_dept" id="college-selector" required>
                                <option value="" disabled selected>Select College...</option>
                                ${collegesData.map(c => `<option value="${c.name}">${c.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="form-field"><label class="field-label">Course</label><select name="course" id="course-selector" required><option value="" disabled selected>Select College First...</option></select></div>
                        <div class="form-field"><label class="field-label">Year Level</label><select name="year_level" required><option value="" disabled selected>Select Year...</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option><option>5th Year</option></select></div>`;
                        roleVLabel.innerText = "COR / ENROLLMENT VERIFIED";
                        roleVIcon.className = "ph ph-certificate";
                    } else {
                        const keyOffices = [
                            "Office of the Campus Director",
                            "Registrar Office",
                            "Administrative and Finance Services",
                            "Human Resource Management Office (HRMO)",
                            "Guidance Office",
                            "Student Affairs and Services Offices (SASO)",
                            "Alumni Relations and Affairs Office",
                            "Maintenance and Engineering Office",
                            "Library",
                            "Campus Clinic",
                            "Supply Office"
                        ];
                        html += `<div class="form-field md-col-2">
                             <label class="field-label">ACADEMIC DEPARTMENT / KEY OFFICE</label>
                             <select name="college_dept" required>
                                 <option value="" disabled selected>Select Department/Office...</option>
                                 <optgroup label="Academic Departments">
                                    ${collegesData.map(c => `<option value="${c.name}">${c.name}</option>`).join('')}
                                 </optgroup>
                                 <optgroup label="Key Administrative Offices">
                                    ${keyOffices.map(o => `<option value="${o}">${o}</option>`).join('')}
                                 </optgroup>
                             </select>
                        </div>`;
                        roleVLabel.innerText = "EMPLOYEE ID VERIFIED";
                        roleVIcon.className = "ph ph-briefcase";
                    }
                    html += `</div>`;
                } else {
                    html = `<div class="form-grid">
                        <div class="form-field"><label class="field-label">Business / Stall Name</label><input type="text" name="business_stall_name" required></div>
                        <div class="form-field"><label class="field-label">Stall Location</label><input type="text" name="vendor_address" required></div>
                    </div>`;
                    roleVLabel.innerText = "BUSINESS PERMIT VERIFIED";
                    roleVIcon.className = "ph ph-storefront";
                }

                dynamicFields.innerHTML = html;

                // Re-bind listeners for dynamic elements
                const searchBtn = document.getElementById('fetch-search-btn');
                const searchInput = document.getElementById('search-id-input');
                if (searchBtn && searchInput) {
                    searchBtn.onclick = (e) => { e.preventDefault(); e.stopPropagation(); fetchExistingData(searchInput.value.trim()); };
                    searchInput.onkeypress = (e) => { if(e.key === 'Enter') { e.preventDefault(); fetchExistingData(searchInput.value.trim()); } };
                }
            };
        });

        // Trigger auto-population if editing an existing registration (Admin Panel)
        @if(isset($registration) && $registration)
            const editData = @json($registration);
            setTimeout(() => {
                // Populate the form with the existing data
                populateForm(editData);
            }, 500); // Small delay to ensure DOM and listeners are ready
        @endif

        // College Chained Dropdown - Refactored for pure JS data
        document.body.addEventListener('change', (e) => {
            if (e.target.id === 'college-selector') {
                const collegeName = e.target.value;
                const college = collegesData.find(c => c.name === collegeName);
                const courses = college ? college.courses : [];
                
                const courseSelector = document.getElementById('course-selector');
                if (courseSelector) {
                    courseSelector.innerHTML = '<option value="" disabled selected>Select Course...</option>';
                    courses.forEach(c => {
                        const opt = document.createElement('option'); opt.value = c.name; opt.innerText = c.name;
                        courseSelector.appendChild(opt);
                    });
                }
            }
        });

        // Vehicle Chained Dropdowns
        const catSel = document.getElementById('office-category-selector');
        const brandSel = document.getElementById('office-brand-selector');
        const modelSel = document.getElementById('office-model-selector');
        
        catSel.onchange = async function() {
            const catId = this.selectedOptions[0].dataset.id;
            brandSel.innerHTML = '<option value="" disabled selected>Loading...</option>';
            brandSel.disabled = true;

            // Reset "Other" states
            document.getElementById('brand-other-input').style.display = 'none';
            document.getElementById('brand-other-input').value = '';
            document.getElementById('brand-other-input').required = false;
            brandSel.name = 'make_brand';

            document.getElementById('office-brand-loader').style.display = 'block';
            try {
                const res = await fetch(`/api/brands/${catId}`);
                const brands = await res.json();
                brandSel.innerHTML = '<option value="" disabled selected>Select Brand...</option>';
                brands.forEach(b => { brandSel.innerHTML += `<option value="${b.name}" data-id="${b.id}">${b.name}</option>`; });
                brandSel.innerHTML += `<option value="Other">Other</option>`;
                brandSel.disabled = false;
            } finally { document.getElementById('office-brand-loader').style.display = 'none'; }
        };

        brandSel.onchange = async function() {
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

            modelSel.innerHTML = '<option value="" disabled selected>Loading...</option>';
            modelSel.disabled = true;

            // Reset "Other" Model state
            document.getElementById('model-other-input').style.display = 'none';
            document.getElementById('model-other-input').value = '';
            document.getElementById('model-other-input').required = false;
            modelSel.name = 'model_name';

            document.getElementById('office-model-loader').style.display = 'block';
            if(!brandId) { 
                modelSel.innerHTML = '<option value="Other">Other</option>'; 
                modelSel.disabled = false; 
                document.getElementById('office-model-loader').style.display = 'none'; 
                // Auto trigger if Brand is Other
                if(isOther) modelSel.onchange();
                return; 
            }
            try {
                const res = await fetch(`/api/models/${brandId}`);
                const models = await res.json();
                modelSel.innerHTML = '<option value="" disabled selected>Select Model...</option>';
                models.forEach(m => { modelSel.innerHTML += `<option value="${m.name}">${m.name}</option>`; });
                modelSel.innerHTML += `<option value="Other">Other</option>`;
                modelSel.disabled = false;
            } finally { document.getElementById('office-model-loader').style.display = 'none'; }
        };

        modelSel.onchange = function() {
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
        };

        // RFID Scanner Logic (Multi-scan implementation)
        const modeAuto = document.getElementById('modeAuto');
        const modeManual = document.getElementById('modeManual');
        const scanBtn = document.getElementById('scanBtn');
        const rfidInput = document.getElementById('rfidTagId');
        const statusText = document.getElementById('scannerStatus');
        let bridgeSocket = null;
        let scanStep = 0; 
        let firstScanId = '';

        modeAuto.onclick = () => { modeAuto.classList.add('active'); modeManual.classList.remove('active'); document.getElementById('autoModeContainer').style.display='block'; document.getElementById('manualModeContainer').style.display='none'; resetScanner(); };
        modeManual.onclick = () => { modeManual.classList.add('active'); modeAuto.classList.remove('active'); document.getElementById('manualModeContainer').style.display='block'; document.getElementById('autoModeContainer').style.display='none'; resetScanner(); };

        function resetScanner() {
            scanStep = 0;
            firstScanId = '';
            rfidInput.value = '';
            rfidInput.style.background = '#f1f5f9';
            if(!bridgeSocket) statusText.innerText = "Connect to scanner sequence.";
        }

        scanBtn.onclick = () => {
            if(bridgeSocket) { bridgeSocket.close(); bridgeSocket = null; document.getElementById('scanBtnText').innerText = "Connect Scanner"; statusText.innerText = "Connection closed."; return; }
            
            bridgeSocket = new WebSocket('ws://localhost:8080'); // Common port for bridge_service.py
            document.getElementById('scanBtnText').innerText = "Listening...";
            statusText.innerText = "Double scan sequence active. Please scan the tag.";
            
            bridgeSocket.onmessage = async (e) => {
                const data = JSON.parse(e.data);
                if(data.tagId) {
                    const sid = data.tagId;
                    if (scanStep === 0) {
                        firstScanId = sid;
                        scanStep = 1;
                        rfidInput.placeholder = "FIRST SCAN CAPTURED...";
                        statusText.innerText = "First scan successful. Please scan the same tag again to verify.";
                        statusText.style.color = '#f59e0b';
                    } else if (scanStep === 1) {
                        if (sid === firstScanId) {
                            // Check uniqueness
                            const checkRes = await fetch(`/admin/check-tag?tagId=${sid}`);
                            const checkData = await checkRes.json();
                            if (checkData.exists) {
                                Swal.fire('Tag Conflict', checkData.message, 'error');
                                resetScanner();
                                return;
                            }
                            
                            rfidInput.value = sid;
                            rfidInput.style.background = '#ecfdf5';
                            scanStep = 2;
                            statusText.innerText = "Tag verified and locked. Ready for registration.";
                            statusText.style.color = '#10b981';
                            Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Tag Verified!', showConfirmButton:false, timer:1500 });
                        } else {
                            statusText.innerText = "Mismatch detected. Please restart scan sequence.";
                            statusText.style.color = '#ef4444';
                            resetScanner();
                        }
                    }
                }
            };
            bridgeSocket.onerror = () => { 
                Swal.fire('Bridge Error', 'Could not reach RFID bridge. Ensure bridge_service.py is running.', 'error'); 
                bridgeSocket = null; 
                document.getElementById('scanBtnText').innerText = "Connect Scanner"; 
            };
        };

        // Manual Sync
        const manualInput = document.getElementById('manualRfidTagId');
        const confirmInput = document.getElementById('confirmRfidTagId');
        const syncManual = () => {
            if(modeManual.classList.contains('active')) {
                if(manualInput.value === confirmInput.value && manualInput.value !== '') {
                    rfidInput.value = manualInput.value;
                    rfidInput.style.background = '#ecfdf5';
                } else {
                    rfidInput.value = '';
                }
            }
        };
        manualInput.addEventListener('input', syncManual);
        confirmInput.addEventListener('input', syncManual);

        // Submission
        mainForm.onsubmit = async (e) => {
            e.preventDefault();
            if(!rfidInput.value) {
                Swal.fire('Missing Tag', 'Please complete the RFID scan sequence or manual entry before saving.', 'warning');
                return;
            }

            Swal.fire({ title:'Processing...', text:'Activating tag and updating records...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            
            const formData = new FormData(mainForm);
            
            @if(isset($registration) && $registration)
                formData.append('_method', 'PUT');
                const actionUrl = '{{ route('admin.rfid.update', $registration->id) }}';
            @else
                const actionUrl = '{{ route('admin.rfid.store') }}';
            @endif

            try {
                const res = await fetch(actionUrl, { 
                    method:'POST', 
                    body:formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                
                const data = await res.json();
                if(data.success) {
                    Swal.fire({ icon:'success', title:'Success!', text: data.message }).then(() => { window.location.href = '{{ route('admin.rfid') }}'; });
                } else {
                    Swal.fire('Error', data.message || 'Check fields.', 'error');
                }
            } catch(e) { 
                Swal.fire('Error', 'Communication failure.', 'error'); 
            }
        };
    });
</script>
@endsection
