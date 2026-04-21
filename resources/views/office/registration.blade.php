@extends('layouts.app')

@section('title', 'Vehicle Owner Registration')
@section('subtitle', 'Register new vehicle owners and assign RFID tags for system access.')

@section('content')
<div class="table-container">
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
                        <span>Student</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="faculty">
                        <span>Personnel</span>
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="staff">
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
                        <label class="field-label">First Name</label>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Last Name</label>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" placeholder="Middle Name">
                    </div>
                    <div class="form-field">
                        <label id="contact-label" class="field-label">Contact Number <span style="color:red">*</span></label>
                        <input type="text" name="contact_number" id="contact-input" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="form-field md-col-2">
                        <label id="email-label" class="field-label">Email Address <span style="color:red">*</span></label>
                        <input type="email" name="email_address" id="email-input" placeholder="email@example.com" required>
                    </div>
                </div>

                <!-- Role-Specific Fields Area -->
                <div id="dynamic-applicant-fields" class="mt-4">
                    <!-- Injected via JavaScript -->
                </div>
            </section>

            <hr class="section-divider">

            <!-- ================= VEHICLE MANAGEMENT ================= -->
            <section class="form-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="section-title" style="margin-bottom: 0;">
                        <i class="ph ph-car"></i> Vehicle Identity
                    </h2>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <div id="bridge-status-badge" style="display: flex; align-items: center; gap: 6px; padding: 4px 10px; background: #f1f5f9; border-radius: 99px; font-size: 0.7rem; font-weight: 800; color: #64748b; border: 1px solid #e2e8f0;">
                            <span style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;"></span>
                            BRIDGE OFFLINE
                        </div>
                        <button type="button" id="kill-bridge-btn" title="Stop Local Bridge Service" class="btn btn-outline" style="padding: 4px; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: #ef4444; border-color: #fca5a5; display: none;">
                            <i class="ph ph-power"></i>
                        </button>
                        <button type="button" id="add-vehicle-btn" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.75rem;">
                            <i class="ph ph-plus-circle"></i> Add Another Vehicle
                        </button>
                    </div>
                </div>

                <div id="vehicle-container" style="display: flex; flex-direction: column; gap: 16px; width: 100%;">
                    <!-- Dynamic Vehicle Entries will be injected here -->
                </div>

                <!-- Vehicle Entry Template -->
                <template id="vehicle-template">
                    <div class="vehicle-set" style="display: block; width: 100%; padding: 20px; border: 2px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-sizing: border-box; position: relative;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                            <span class="vehicle-number-badge" style="background: #4f46e5; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">1</span>
                            <span class="vehicle-number-label" style="font-weight: 600; font-size: 0.95rem; color: #1e293b;">Vehicle #1</span>
                            <button type="button" class="remove-vehicle" style="margin-left: auto; color: #ef4444; background: none; border: 1px solid #fca5a5; border-radius: 6px; cursor: pointer; padding: 4px 10px; font-size: 0.75rem;" title="Remove Vehicle">
                                <i class="ph ph-trash"></i> Remove
                            </button>
                        </div>
                        
                        <input type="hidden" name="vehicles[{index}][id]" class="vehicle-id">
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #475569;">Vehicle Category <span style="color:red">*</span></label>
                                <select name="vehicles[{index}][vehicle_type]" class="category-selector" required style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; background: white;">
                                    <option value="" disabled selected>Select Category...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" data-id="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #475569;">Brand <span style="color:red">*</span></label>
                                <div style="position: relative;">
                                    <select name="vehicles[{index}][make_brand]" class="brand-selector" required disabled style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; background: white;">
                                        <option value="" disabled selected>Select Category First...</option>
                                    </select>
                                    <span class="brand-loader" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #6366f1;">...</span>
                                </div>
                                <input type="text" name="vehicles[{index}][make_brand_other]" class="brand-other-input" placeholder="Type Brand Name..." style="display: none; width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; box-sizing: border-box;">
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #475569;">Specific Model</label>
                                <div style="position: relative;">
                                    <select name="vehicles[{index}][model_name]" class="model-selector" disabled style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; background: white;">
                                        <option value="" disabled selected>Select Brand First...</option>
                                    </select>
                                    <span class="model-loader" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #6366f1;">...</span>
                                </div>
                                <input type="text" name="vehicles[{index}][model_name_other]" class="model-other-input" placeholder="Type Model Name..." style="display: none; width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; box-sizing: border-box;">
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #475569;">Plate Number <span style="color:red">*</span></label>
                                <input type="text" name="vehicles[{index}][plate_number]" class="plate-input" placeholder="ABC 1234" required style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; text-transform: uppercase; box-sizing: border-box;">
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                            <label style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #475569; display: block; margin-bottom: 8px;">RFID Tag Assignment</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="vehicles[{index}][rfid_tag]" class="rfid-input" placeholder="Scan or Enter Tag ID" style="flex-grow: 1; padding: 0.625rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-size: 0.875rem;">
                                <button type="button" class="scan-vehicle-tag" style="padding: 0 1rem; border: 1px solid #6366f1; color: #6366f1; background: white; border-radius: 8px; cursor: pointer; font-size: 0.875rem; white-space: nowrap;"><i class="ph ph-scan"></i> Scan</button>
                            </div>
                        </div>
                    </div>
                </template>
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
                            <span>Vehicle CR Verified</span>
                        </div>
                    </label>
                    <label class="v-card">
                        <input type="checkbox" name="verified_or" required>
                        <div class="v-content">
                            <i class="ph ph-receipt"></i>
                            <span>Vehicle OR Verified</span>
                        </div>
                    </label>
                    <label class="v-card">
                        <input type="checkbox" name="verified_license" required>
                        <div class="v-content">
                            <i class="ph ph-identification-card"></i>
                            <span>Driver's License Verified</span>
                        </div>
                    </label>
                    <label class="v-card" id="role-verification-item">
                        <input type="checkbox" name="verified_institutional" required>
                        <div class="v-content">
                            <i class="ph ph-student"></i>
                            <span id="role-v-label">Institutional ID Verified</span>
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

            <!-- RFID section is now integrated into each vehicle entry -->

            <div class="form-actions mt-8">
                <button type="submit" class="btn btn-primary w-full justify-center" style="height: 54px; font-size: 1.1rem; font-weight: 700;">
                    <i class="ph ph-check-circle"></i> Complete Owner Registration
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .form-section { margin-bottom: 2rem; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
    .role-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .role-option { display: flex; align-items: center; gap: 0.75rem; padding: 1.2rem 1rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: 0.3s; font-weight: 700; color: #64748b; }
    .role-option:hover { border-color: #cbd5e1; background: #f1f5f9; }
    .role-option input { width: 18px; height: 18px; accent-color: #741b1b; }
    .role-option input:checked + span { color: #741b1b; }
    .role-option:has(input:checked) { border-color: #741b1b; background: #fffcfc; color: #741b1b; box-shadow: 0 4px 12px rgba(116, 27, 27, 0.05); }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .form-field { margin-bottom: 0.5rem; }
    .field-label { display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; }
    .md-col-2 { grid-column: span 2; }
    .section-divider { border: 0; border-top: 2px solid #f1f5f9; margin: 2.5rem 0; }
    
    input, select { width: 100%; padding: 0.85rem; border-radius: 8px; border: 1px solid #e2e8f0; outline: none; font-weight: 600; transition: border-color 0.2s; }
    input:focus, select:focus { border-color: #741b1b; box-shadow: 0 0 0 3px rgba(116, 27, 27, 0.05); }

    /* Verification Cards */
    .verification-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .v-card { position: relative; cursor: pointer; }
    .v-card input { position: absolute; opacity: 0; width: 0; height: 0; }
    .v-content { background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1.5rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 10px; transition: 0.2s; text-align: center; }
    .v-content i { font-size: 1.5rem; color: #94a3b8; }
    .v-content span { font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; line-height: 1.2; }
    .v-card input:checked + .v-content { background: #f0fdf4; border-color: #10b981; }
    .v-card input:checked + .v-content i { color: #10b981; }
    .v-card input:checked + .v-content span { color: #10b981; }

    .btn-outline.active { background: #1e293b; color: white; border-color: #1e293b; }
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

@endsection

@section('scripts')
<script>
    const collegesData = @json($colleges);
    const officesData = @json($offices);

    document.addEventListener('DOMContentLoaded', function() {
        const roleRadios = document.querySelectorAll('input[name="role"]');
        const dynamicFields = document.getElementById('dynamic-applicant-fields');
        const regContainer = document.getElementById('registration-details-container');
        const roleVLabel = document.getElementById('role-v-label');
        const roleVIcon = document.querySelector('#role-verification-item i');
        const mainForm = document.getElementById('registerForm');

        // Role Dynamics
        const populateForm = (data) => {
            // Sync Institutional Role (Radio buttons) FIRST to generate dynamic DOM structure
            if (data.role) {
                const roleInput = mainForm.querySelector(`input[name="role"][value="${data.role}"]`);
                if (roleInput) { 
                    roleInput.checked = true; 
                    roleInput.dispatchEvent(new Event('change')); 
                }
            }

            // After role change, populate dynamic and standard fields
            setTimeout(() => {
                // Formatting
                let fN = data.first_name || '';
                let lN = data.last_name || '';
                let mN = data.middle_name || '';
                
                // Fallback for older records stored before first_name/last_name columns
                if (!fN && data.full_name) {
                    const parts = data.full_name.trim().split(' ');
                    lN = parts.pop(); // Last element is last name
                    fN = parts.shift() || ''; // First element is first name
                    if(parts.length > 0) mN = parts.join(' ');
                }

                // Date field correction for <input type="date"> which requires strict YYYY-MM-DD
                let vFrom = data.validity_from ? data.validity_from.split('T')[0] : '';
                let vTo = data.validity_to ? data.validity_to.split('T')[0] : '';

                // Mapping standard fields including dynamically created university_id
                const fieldsMap = {
                    'first_name': fN,
                    'last_name': lN,
                    'middle_name': mN,
                    'contact_number': data.contact_number,
                    'email_address': data.email_address,
                    'university_id': data.university_id,
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
                        if (sel) { sel.value = data.college_dept; sel.dispatchEvent(new Event('change')); }
                    }
                    setTimeout(() => {
                        const cSel = document.getElementById('course-selector');
                        if (cSel) cSel.value = data.course || '';
                    }, 500);
                    const ySel = mainForm.querySelector('select[name="year_level"]');
                    if (ySel) ySel.value = data.year_level || '';
                } else if (data.role === 'faculty') {
                    const dSel = mainForm.querySelector('select[name="college_dept"]');
                    if (dSel) dSel.value = data.college_dept || '';
                } else if (data.role === 'staff') {
                    const biz = mainForm.querySelector('input[name="business_stall_name"]');
                    if (biz) biz.value = data.business_stall_name || '';
                    const loc = mainForm.querySelector('input[name="vendor_address"]');
                    if (loc) loc.value = data.vendor_address || '';
                }

                // --- MULTI-VEHICLE POPULATION ---
                // Normalize vehicle data from the Vehicle model
                // Vehicle model has: id, plate_number, vehicle_details (combined), rfid_tag, vehicle_type
                const normalizeVehicle = (v, regData) => {
                    let make_brand = v.make_brand || null;
                    let model_name = v.model_name || null;

                    if (!make_brand && v.vehicle_details) {
                        const parts = v.vehicle_details.trim().split(' ');
                        make_brand = parts[0] || null;
                        model_name = parts.slice(1).join(' ') || null;
                    }

                    if (!make_brand) make_brand = regData.make_brand || null;
                    if (!model_name) model_name = regData.model_name || null;

                    return {
                        id: v.id || '',
                        vehicle_type: v.vehicle_type || regData.vehicle_type || '',
                        make_brand: make_brand || '',
                        model_name: model_name || '',
                        plate_number: v.plate_number || regData.plate_number || '',
                        rfid_tag: v.rfid_tag || regData.rfid_tag_id || ''
                    };
                };

                const vehiclesToLoad = (data.vehicles && data.vehicles.length > 0)
                    ? data.vehicles.map(v => normalizeVehicle(v, data))
                    : [{ id: '', vehicle_type: data.vehicle_type||'', make_brand: data.make_brand||'', model_name: data.model_name||'', plate_number: data.plate_number||'', rfid_tag: data.rfid_tag_id||'' }];

                console.log('[SmartGate] Vehicles to load:', vehiclesToLoad.length, vehiclesToLoad);

                // Clear container and reset index FIRST before anything else
                const vehicleContainerEl = document.getElementById('vehicle-container');
                vehicleContainerEl.innerHTML = '';
                vehicleIndex = 0;

                // Populate each vehicle card sequentially (await keeps order correct)
                const populateAllVehicles = async () => {
                    for (let i = 0; i < vehiclesToLoad.length; i++) {
                        const v = vehiclesToLoad[i];
                        console.log(`[SmartGate] Rendering vehicle #${i + 1}:`, v);

                        // 1. Append a new card to the DOM
                        const entry = addVehicleEntry();
                        console.log(`[SmartGate] Cards in DOM after append:`, vehicleContainerEl.querySelectorAll('.vehicle-set').length);

                        // 2. Set hidden vehicle ID (for update operations)
                        entry.querySelector('.vehicle-id').value = v.id || '';

                        // 3. Set & fetch Category → Brand chain
                        const catSel = entry.querySelector('.category-selector');
                        if (v.vehicle_type) {
                            catSel.value = v.vehicle_type;
                            await handleCategoryChange.call(catSel);
                        }

                        // 4. Set Brand (or "Other")
                        const brandSel = entry.querySelector('.brand-selector');
                        if (v.make_brand) {
                            const bExists = Array.from(brandSel.options).some(o => o.value === v.make_brand);
                            if (bExists) {
                                brandSel.value = v.make_brand;
                            } else {
                                brandSel.value = 'Other';
                                const otherInput = entry.querySelector('.brand-other-input');
                                if (otherInput) { otherInput.value = v.make_brand; otherInput.style.display = 'block'; }
                            }
                            await handleBrandChange.call(brandSel);
                        }

                        // 5. Set Model (or "Other")
                        const modelSel = entry.querySelector('.model-selector');
                        if (v.model_name) {
                            const mExists = Array.from(modelSel.options).some(o => o.value === v.model_name);
                            if (mExists) {
                                modelSel.value = v.model_name;
                            } else {
                                modelSel.value = 'Other';
                                const mOther = entry.querySelector('.model-other-input');
                                if (mOther) { mOther.value = v.model_name; mOther.style.display = 'block'; }
                            }
                        }

                        // 6. Set Plate & RFID
                        const plateInput = entry.querySelector('.plate-input');
                        if (plateInput) plateInput.value = v.plate_number || '';
                        const rfidEl = entry.querySelector('.rfid-input');
                        if (rfidEl) rfidEl.value = v.rfid_tag || '';

                        console.log(`[SmartGate] Vehicle #${i + 1} populated.`);
                    }

                    renumberVehicles();
                    console.log(`[SmartGate] All ${vehiclesToLoad.length} vehicles rendered.`);
                };

                // Allow role-based fields to render before appending vehicle cards
                setTimeout(() => {
                    populateAllVehicles().then(() => {
                        const finalContainer = document.getElementById('vehicle-container');
                        console.log('[SmartGate] Total vehicle cards rendered:', finalContainer.querySelectorAll('.vehicle-set').length);
                        if (data.id) {
                            mainForm.querySelectorAll('.v-card input[type="checkbox"]').forEach(c => c.checked = true);
                        }
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `${vehiclesToLoad.length} vehicle(s) loaded!`, showConfirmButton: false, timer: 2000 });
                    });
                }, 150);

            }, 200);
        };

        const fetchExistingData = async (uid) => {
            if (!uid) return;
            Swal.fire({ title: 'Searching...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const res = await fetch(`/office/registration/fetch-user/${encodeURIComponent(uid)}`);
                const result = await res.json();
                if (result.success) { populateForm(result.data); }
                else { Swal.fire('No History', result.message, 'info'); }
            } catch (err) { Swal.fire('Error', 'Fetch failed.', 'error'); }
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
                    contactLabel.innerHTML = 'Contact Number <span style="color:gray">(Optional)</span>';
                    emailInput.required = false;
                    emailLabel.innerHTML = 'Email Address <span style="color:gray">(Optional)</span>';
                } else {
                    contactInput.required = true;
                    contactLabel.innerHTML = 'Contact Number <span style="color:red">*</span>';
                    emailInput.required = true;
                    emailLabel.innerHTML = 'Email Address <span style="color:red">*</span>';
                }

                let html = '';
                if (role === 'student' || role === 'faculty') {
                    const idLabel = role === 'student' ? 'Student ID Number' : 'Personnel ID Number';
                    html = `<div class="form-grid">
                        <div class="form-field">
                            <label class="field-label">${idLabel.toUpperCase()}</label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" name="university_id" id="search-id-input" placeholder="Search ID or Plate..." ${role === 'student' ? 'required' : ''} style="flex-grow:1">
                                <button type="button" id="fetch-search-btn" class="btn btn-outline" style="padding: 0 0.75rem;"><i class="ph ph-magnifying-glass"></i></button>
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
                        html += `<div class="form-field md-col-2">
                             <label class="field-label">Academic Department / Key Office</label>
                             <select name="college_dept" required>
                                 <option value="" disabled selected>Select Department/Office...</option>
                                 @if(count($colleges) > 0)
                                 <optgroup label="Academic Departments">
                                    ${collegesData.map(c => `<option value="${c.name}">${c.name}</option>`).join('')}
                                 </optgroup>
                                 @endif
                                 @if(count($offices) > 0)
                                 <optgroup label="Key Administrative Offices">
                                    ${officesData.map(o => `<option value="${o.name}">${o.name}</option>`).join('')}
                                 </optgroup>
                                 @endif
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

                const searchBtn = document.getElementById('fetch-search-btn');
                const searchInput = document.getElementById('search-id-input');
                if (searchBtn && searchInput) {
                    searchBtn.onclick = (e) => { e.preventDefault(); e.stopPropagation(); fetchExistingData(searchInput.value.trim()); };
                    searchInput.onkeypress = (e) => { if (e.key === 'Enter') { e.preventDefault(); fetchExistingData(searchInput.value.trim()); } };
                }
            };
        });

        // Trigger auto-population if editing an existing registration
        @if(isset($registration) && $registration)
            const editData = @json($registration);
            setTimeout(() => {
                // Populate the form with the existing data
                populateForm(editData);
            }, 500); // Small delay to ensure DOM and listeners are ready
        @endif

        // College Chained Dropdown - Refactored
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

        // Vehicle Multi-Set Logic
        const vehicleContainer = document.getElementById('vehicle-container');
        const vehicleTemplate = document.getElementById('vehicle-template');
        const addVehicleBtn = document.getElementById('add-vehicle-btn');
        let vehicleIndex = 0;

        // Renumber all vehicle cards after add/remove
        function renumberVehicles() {
            vehicleContainer.querySelectorAll('.vehicle-set').forEach((set, i) => {
                const badge = set.querySelector('.vehicle-number-badge');
                const label = set.querySelector('.vehicle-number-label');
                if (badge) badge.textContent = i + 1;
                if (label) label.textContent = `Vehicle #${i + 1}`;
            });
        }

        function addVehicleEntry() {
            const index = vehicleIndex++;
            const clone = vehicleTemplate.content.cloneNode(true);
            const wrapper = clone.querySelector('.vehicle-set');
            
            // Replace {index} placeholders in names
            wrapper.querySelectorAll('[name*="{index}"]').forEach(el => {
                el.name = el.name.replace(/{index}/g, index);
            });

            // Update number badge to current count + 1
            const currentCount = vehicleContainer.querySelectorAll('.vehicle-set').length;
            const badge = wrapper.querySelector('.vehicle-number-badge');
            const label = wrapper.querySelector('.vehicle-number-label');
            if (badge) badge.textContent = currentCount + 1;
            if (label) label.textContent = `Vehicle #${currentCount + 1}`;

            // Bind Events
            wrapper.querySelector('.category-selector').onchange = handleCategoryChange;
            wrapper.querySelector('.brand-selector').onchange = handleBrandChange;
            
            const modelSel = wrapper.querySelector('.model-selector');
            modelSel.onchange = function() {
                const other = wrapper.querySelector('.model-other-input');
                if (this.value === 'Other') {
                    other.style.display = 'block';
                    other.required = true;
                    other.name = `vehicles[${index}][model_name]`;
                    this.name = `vehicles[${index}][model_name_select]`;
                } else {
                    other.style.display = 'none';
                    other.required = false;
                    other.name = '';
                    this.name = `vehicles[${index}][model_name]`;
                }
            };

            wrapper.querySelector('.remove-vehicle').onclick = () => {
                if(vehicleContainer.querySelectorAll('.vehicle-set').length > 1) {
                    wrapper.remove();
                    renumberVehicles();
                } else {
                    Swal.fire('Requirement', 'At least one vehicle is required.', 'info');
                }
            };

            // RFID Scan Specific
            wrapper.querySelector('.scan-vehicle-tag').onclick = function() {
                const targetInput = wrapper.querySelector('.rfid-input');
                startScanning(targetInput, this);
            };

            vehicleContainer.appendChild(clone);
            return vehicleContainer.lastElementChild;
        }

        addVehicleBtn.onclick = () => addVehicleEntry();

        // Chained Handlers Refactored for Multi-Row
        async function handleCategoryChange() {
            const wrapper = this.closest('.vehicle-set');
            const brandSel = wrapper.querySelector('.brand-selector');
            const loader = wrapper.querySelector('.brand-loader');
            const catId = this.selectedOptions[0]?.dataset.id;

            if(!catId) return;

            brandSel.innerHTML = '<option value="" disabled selected>Loading...</option>';
            brandSel.disabled = true;
            loader.style.display = 'block';

            try {
                const res = await fetch(`/api/brands/${catId}`);
                const brands = await res.json();
                brandSel.innerHTML = '<option value="" disabled selected>Select Brand...</option>';
                brands.forEach(b => { brandSel.innerHTML += `<option value="${b.name}" data-id="${b.id}">${b.name}</option>`; });
                brandSel.innerHTML += `<option value="Other">Other</option>`;
                brandSel.disabled = false;
            } catch(e) { console.error(e); }
            finally { loader.style.display = 'none'; }
        }

        async function handleBrandChange() {
            const wrapper = this.closest('.vehicle-set');
            const isOther = this.value === 'Other';
            const brandId = this.selectedOptions[0]?.dataset.id;
            const otherInput = wrapper.querySelector('.brand-other-input');
            const modelSel = wrapper.querySelector('.model-selector');
            const loader = wrapper.querySelector('.model-loader');
            
            // Extract index from name
            const nameMatch = this.name.match(/vehicles\[(\d+)\]/);
            const idx = nameMatch ? nameMatch[1] : 0;

            if (isOther) {
                otherInput.style.display = 'block';
                otherInput.required = true;
                otherInput.name = `vehicles[${idx}][make_brand]`;
                this.name = `vehicles[${idx}][make_brand_select]`;
            } else {
                otherInput.style.display = 'none';
                otherInput.required = false;
                otherInput.name = '';
                this.name = `vehicles[${idx}][make_brand]`;
            }

            modelSel.innerHTML = '<option value="" disabled selected>Loading...</option>';
            modelSel.disabled = true;
            loader.style.display = 'block';

            if (!brandId && !isOther) {
                modelSel.innerHTML = '<option value="" disabled selected>Select Brand First...</option>';
                loader.style.display = 'none';
                return;
            }

            if(isOther) {
                modelSel.innerHTML = '<option value="" disabled selected>Select Model...</option><option value="Other">Other</option>';
                modelSel.disabled = false;
                loader.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`/api/models/${brandId}`);
                const models = await res.json();
                modelSel.innerHTML = '<option value="" disabled selected>Select Model...</option>';
                models.forEach(m => { modelSel.innerHTML += `<option value="${m.name}">${m.name}</option>`; });
                modelSel.innerHTML += `<option value="Other">Other</option>`;
                modelSel.disabled = false;
            } catch(e) { console.error(e); }
            finally { loader.style.display = 'none'; }
        }

        // Shared Scan logic
        let scanSocket = null;
        let isPollingStatus = false;

        async function updateBridgeBadge() {
            try {
                const res = await fetch('{{ route('bridge.status') }}');
                const data = await res.json();
                if (data.online) {
                    setBadgeOnline();
                } else {
                    // Only set offline if scan is not active
                    if(!scanSocket) setBadgeOffline();
                }
            } catch (e) {}
        }

        function setBadgeOnline() {
            const badge = document.getElementById('bridge-status-badge');
            const killBtn = document.getElementById('kill-bridge-btn');
            if(!badge) return;
            badge.innerHTML = '<span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 5px #10b981;"></span> BRIDGE ONLINE';
            badge.style.background = '#f0fdf4';
            badge.style.color = '#059669';
            badge.style.borderColor = '#bbf7d0';
            if(killBtn) killBtn.style.display = 'flex';
        }

        function setBadgeOffline() {
            const badge = document.getElementById('bridge-status-badge');
            const killBtn = document.getElementById('kill-bridge-btn');
            if(!badge) return;
            badge.innerHTML = '<span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span> BRIDGE OFFLINE';
            badge.style.background = '#fef2f2';
            badge.style.color = '#dc2626';
            badge.style.borderColor = '#fecaca';
            if(killBtn) killBtn.style.display = 'none';
        }

        // Poll status every 5 seconds
        setInterval(updateBridgeBadge, 5000);
        updateBridgeBadge();

        document.getElementById('kill-bridge-btn').onclick = async function() {
            const res = await fetch('{{ route('bridge.stop') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            const data = await res.json();
            if(data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Bridge Stopped', showConfirmButton: false, timer: 2000 });
                updateBridgeBadge();
            }
        };

        async function startScanning(input, btn) {
            if(scanSocket) { scanSocket.close(); scanSocket = null; btn.innerHTML = '<i class="ph ph-scan"></i> Scan'; return; }
            
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Initializing...';
            btn.disabled = true;

            // Auto-check and START bridge if offline
            try {
                const statusRes = await fetch('{{ route('bridge.status') }}');
                const statusData = await statusRes.json();
                if (!statusData.online) {
                    btn.innerHTML = '<i class="ph ph-rocket-launch animate-spin"></i> Starting Bridge...';
                    await fetch('{{ route('bridge.start') }}');
                    // Wait a bit for bridge to bind
                    await new Promise(r => setTimeout(r, 2000));
                }
            } catch(e) { console.warn('Bridge auto-start check failed', e); }

            scanSocket = new WebSocket('ws://localhost:8080');
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-broadcast animate-pulse"></i> Listening...';
            
            scanSocket.onmessage = (e) => {
                const data = JSON.parse(e.data);
                const tag = data.tagId || data.tag_id;
                if(tag) {
                    input.value = tag;
                    input.dispatchEvent(new Event('input'));
                    input.style.background = '#ecfdf5';
                    Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Tag Captured!', showConfirmButton:false, timer:1500 });
                    scanSocket.close();
                }
            };

            scanSocket.onopen = () => { setBadgeOnline(); };
            scanSocket.onclose = () => { scanSocket = null; btn.innerHTML = originalHtml; updateBridgeBadge(); };
            scanSocket.onerror = () => { 
                Swal.fire({
                    icon: 'error',
                    title: 'Bridge Connection Failed',
                    text: 'The RFID Bridge service is not responding. Please ensure bridge_service.py is running or try again.',
                    confirmButtonColor: '#741b1b'
                });
                scanSocket.close(); 
            };
        }

        // Initial entry
        addVehicleEntry();

        // Complete Submission
        mainForm.onsubmit = async (e) => {
            e.preventDefault();
            Swal.fire({ title:'Saving Registration...', text:'Recording verification and assigning tags...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
            
            const formData = new FormData(mainForm);
            
            @if(isset($registration) && $registration)
                formData.append('_method', 'PUT');
                const actionUrl = '{{ route('office.registration.update', $registration->id) }}';
            @else
                const actionUrl = '{{ route('office.registration.store') }}';
            @endif

            try {
                const res = await fetch(actionUrl, { 
                    method: 'POST', 
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await res.json();
                if(data.success) {
                    Swal.fire({ icon:'success', title:'Registration Saved', text: data.message }).then(() => { window.location.href = '{{ route('office.registration') }}'; });
                } else {
                    Swal.fire('Validation/Server Error', data.message || 'Check form fields and try again.', 'error');
                }
            } catch(e) { 
                console.error(e);
                Swal.fire('Submission Failed', 'Could not reach the server or invalid response.', 'error'); 
            }
        };
    });
</script>
@endsection
