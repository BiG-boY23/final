@extends('layouts.app')

@section('title', 'Registered Users & Owners')
@section('subtitle', 'SmartGate – Management of registered vehicle owners and their vehicle profiles.')

@section('content')

<!-- Directory Summary Stats -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="stats-card" style="background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; position: relative; overflow: hidden;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Total Registry</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #1e293b; margin-top: 5px;">{{ $totalUsers }}</div>
        <i class="ph-bold ph-users-three" style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 2rem; color: #741b1b; opacity: 0.1;"></i>
    </div>
    <div class="stats-card" style="background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; position: relative;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Active Tags</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #10b981; margin-top: 5px;">{{ $activeTags }}</div>
        <i class="ph-bold ph-broadcast" style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 2rem; color: #10b981; opacity: 0.1;"></i>
    </div>
    <div class="stats-card" style="background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; position: relative;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Pending Verification</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #f59e0b; margin-top: 5px;">{{ $pendingReg }}</div>
        <i class="ph-bold ph-shield-warning" style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 2rem; color: #f59e0b; opacity: 0.1;"></i>
    </div>
    <div class="stats-card" style="background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; position: relative;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Verified (No Tag)</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #3b82f6; margin-top: 5px;">{{ $verifiedReg }}</div>
        <i class="ph-bold ph-list-checks" style="position: absolute; right: 1.5rem; top: 1.5rem; font-size: 2rem; color: #3b82f6; opacity: 0.1;"></i>
    </div>
</div>
<div class="table-container">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div style="display: flex; gap: 1.25rem; align-items: center; flex: 1;">
            <div style="position: relative; flex: 1; max-width: 450px;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" id="searchInput" placeholder="Search by name, ID, or plate..." 
                       style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; background: #f8fafc; font-size: 0.95rem;">
            </div>
            
            <select id="roleFilter" style="padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; background: #fff; color: #475569; font-weight: 600; cursor: pointer;">
                <option value="all">Filter by Role: All </option>
                <option value="student">Student</option>
                <option value="faculty">Personnel</option>
                <option value="staff">Vendor</option>
            </select>

            <select id="statusFilter" style="padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; background: #fff; color: #475569; font-weight: 600; cursor: pointer;">
                <option value="all">Filter by Status: All</option>
                <option value="active">Active (with Tag)</option>
                <option value="verified">Verified (No Tag)</option>
                <option value="pending">Pending</option>
                <option value="blacklisted">Blacklisted</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        
        <a href="{{ route('office.registration') }}" class="btn btn-primary" style="padding: 0.85rem 1.5rem; border-radius: 10px;">
            <i class="ph ph-user-plus"></i> New User Profile
        </a>
    </div>

    <div class="table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Owner Profile</th>
                    <th>College / Dept</th>
                    <th style="min-width: 350px;">Registered Vehicles & Validity</th>
                    <th>Date Registered</th>
                    <th>Account Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTable">
                @forelse($registrations as $reg)
                @php
                    $roleLabel = match($reg->role) {
                        'faculty' => 'Personnel',
                        'staff'   => 'Vendor',
                        default => ucfirst($reg->role),
                    };
                    
                    // Determine Account Status based on Vehicle Expiry
                    $isExpired = false;
                    foreach($reg->vehicles as $v) {
                        if($v->expiry_date && $v->expiry_date->isPast()) {
                            $isExpired = true;
                            break;
                        }
                    }
                    if(!$isExpired && $reg->validity_to && $reg->validity_to->isPast()) {
                        $isExpired = true;
                    }

                    $status = $reg->status;
                    if($isExpired) $status = 'expired';

                    // AUTO-ACTIVE LOGIC: If verified and has at least one vehicle, it's ACTIVE
                    if ($status === 'verified' && $reg->vehicles->count() > 0) {
                        $status = 'active';
                    }

                    $badgeClass = 'badge-normal';
                    $badgeText = ucfirst($status);
                    
                    if ($status === 'rejected' || $status === 'blacklisted') {
                        $badgeClass = 'badge-danger';
                        if ($reg->vehicles->count() > 0) {
                            $badgeText = 'Blacklisted';
                            $status = 'blacklisted'; // Normalize for filter
                        } else {
                            $badgeText = 'Rejected';
                            $status = 'rejected';
                        }
                    } elseif ($status === 'pending') {
                        $badgeClass = 'badge-warning';
                    } elseif ($status === 'verified') {
                        $badgeClass = 'badge-info';
                        $badgeText = 'Verified';
                    } elseif ($status === 'active' || $status === 'ACTIVE') {
                        $badgeClass = 'badge-success';
                        $badgeText = 'Active';
                        $status = 'active'; // Normalize for filter
                    } elseif ($status === 'expired') {
                        $badgeClass = 'badge-danger';
                        $badgeText = 'EXPIRED';
                    }

                    $idCardPath = $reg->role === 'student' ? $reg->student_id_path : $reg->employee_id_path;
                    $isPaid = $reg->payments->count() > 0;
                @endphp
                <tr class="user-row" data-role="{{ $reg->role }}"
                    data-id="{{ $reg->id }}"
                    data-name="{{ $reg->full_name }}"
                    data-univ-id="{{ $reg->university_id }}"
                    data-plates="{{ $reg->vehicles->pluck('plate_number')->implode(',') }}"
                    data-status="{{ $status }}"
                    data-cr="{{ $reg->cr_path ? asset('storage/' . $reg->cr_path) : '' }}"
                    data-or="{{ $reg->or_path ? asset('storage/' . $reg->or_path) : '' }}"
                    data-license="{{ $reg->license_path ? asset('storage/' . $reg->license_path) : '' }}"
                    data-idcard="{{ $idCardPath ? asset('storage/' . $idCardPath) : '' }}"
                    data-office="{{ $reg->college_dept }}"
                >
                    <td>
                        <div class="owner-profile-cell">
                             <div class="owner-name">{{ $reg->full_name }}</div>
                             <div class="owner-meta">
                                 <span class="univ-id-span">{{ $reg->university_id }}</span>
                                 <span class="meta-dot"></span>
                                 <span class="owner-role-tag">{{ $roleLabel }}</span>
                             </div>
                        </div>
                    </td>
                    <td>
                        <div class="college-dept-cell">{{ $reg->college_dept }}</div>
                    </td>
                    <td>
                        <div class="vehicle-column-container">
                            <div class="vehicle-list-stacked">
                                @forelse($reg->vehicles as $v)
                                    @php
                                        $expiryColor = '#10b981'; // Green
                                        $expiryText = $v->expiry_date ? $v->expiry_date->format('F d, Y') : 'Life';
                                        $expiringSoon = false;
                                        
                                        if($v->expiry_date) {
                                            $daysLeft = now()->diffInDays($v->expiry_date, false);
                                            if($daysLeft < 0) {
                                                $expiryColor = '#ef4444'; // Red
                                            } elseif($daysLeft <= 7) {
                                                $expiryColor = '#f59e0b'; // Yellow
                                                $expiringSoon = true;
                                            }
                                        }
                                    @endphp
                                    <div class="vehicle-line-item mb-1">
                                        <i class="ph ph-car" style="font-size: 0.9rem; color: #64748b;"></i>
                                        <span class="v-details">{{ $v->vehicle_details ?: 'Unknown Model' }}</span>
                                        <span class="v-separator">|</span>
                                        <span class="v-plate">{{ $v->plate_number }}</span>
                                        <span class="v-separator">|</span>
                                        <div class="v-expiry-info" style="color: {{ $expiryColor }}; font-weight: 700;">
                                            <i class="ph ph-calendar-blank"></i>
                                            {{ $expiryText }}
                                            @if($expiringSoon)
                                                <span class="expiring-soon-label">EXPIRING SOON</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <span class="no-vehicle-alert">No vehicles registered</span>
                                @endforelse
                            </div>
                            <div style="margin-top: 5px;">
                                <button type="button" class="btn-add-vehicle-inline btn-add-vehicle" 
                                        data-id="{{ $reg->id }}" data-name="{{ $reg->full_name }}"
                                        title="Quick link another vehicle">
                                    <i class="ph ph-plus-circle"></i> Add Vehicle
                                </button>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;">{{ $reg->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">{{ $reg->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        @if($isPaid)
                            <span class="badge" style="background: #10b981; color: white; border: none; margin-left: 5px;">
                                <i class="ph ph-check-square"></i> PAID
                            </span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div class="action-group">
                            @if($reg->status === 'pending')
                            <button type="button" class="btn-icon btn-verify" title="Review Submission" data-id="{{ $reg->id }}">
                                 <i class="ph ph-shield-check"></i>
                            </button>
                            @endif

                            {{-- Hide Edit button if Rejected/Blacklisted --}}
                            @if($status !== 'rejected' && $status !== 'blacklisted')
                            <a href="{{ route('office.registration') }}?id={{ $reg->id }}" class="btn-icon btn-edit" title="Profile Manager">
                                <i class="ph ph-pencil-simple-line"></i>
                            </a>
                            @endif

                            <button type="button" class="btn-icon btn-delete" title="Purge Account" data-id="{{ $reg->id }}">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding: 4rem;">
                        <div style="display:flex; flex-direction:column; align-items:center; gap: 1rem; color: #94a3b8;">
                            <i class="ph ph-users-four" style="font-size: 3rem; opacity: 0.5;"></i>
                            <p>System reports 0 registered accounts.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODALS -->

<!-- ADD VEHICLE MODAL -->
<div id="addVehicleModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 550px; border-radius: 28px; display: flex; flex-direction: column; max-height: 95vh; padding: 0;">
        <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="ph ph-plus-circle" style="color: #741b1b; font-size: 1.5rem;"></i>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b;">Link New Vehicle</h3>
            </div>
            <button class="close-modal" style="background: #f1f5f9; border: none; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="ph ph-x" style="font-size: 1.25rem;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 2rem; flex: 1; overflow-y: auto;">
            <div style="background: #fdf2f2; border: 1px solid #fee2e2; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
                <p style="margin: 0; color: #991b1b; font-size: 0.85rem; font-weight: 600;">ASSIGNING TO:</p>
                <strong id="add-v-owner" style="color: #741b1b; font-size: 1.1rem; font-weight: 800;"></strong>
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">Vehicle Category</label>
                <select id="new-vehicle-type" class="form-control" required>
                    <option value="" disabled selected>Select Category...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" data-id="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Manufacturer / Brand</label>
                <div style="position: relative;">
                    <select id="new-brand" class="form-control" disabled>
                        <option value="" disabled selected>Select Category First...</option>
                    </select>
                    <div id="new-brand-loader" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="ph ph-circle-notch animate-spin text-blue-500"></i>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4" id="model-group">
                <label class="form-label">Vehicle Model</label>
                <div style="position: relative;">
                    <select id="new-model" class="form-control" disabled>
                        <option value="" disabled selected>Select Brand First...</option>
                    </select>
                    <div id="new-model-loader" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="ph ph-circle-notch animate-spin text-blue-500"></i>
                    </div>
                </div>
            </div>

            {{-- Hidden combined field submitted to server --}}
            <input type="hidden" id="new-details">
            
            <div class="form-group mb-4">
                <label class="form-label">License Plate Number</label>
                <input type="text" id="new-plate" placeholder="e.g. ABC 1234" class="form-control" style="text-transform: uppercase;">
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">RFID Tag ID</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="new-tag" placeholder="Scan or enter tag..." class="form-control" style="flex: 1;">
                    <button id="btnScanTag" class="btn-icon" style="height: 50px; width: 60px; border-radius: 12px; border-color: #741b1b; color: #741b1b;" title="Focus for Scanner">
                        <i class="ph-bold ph-broadcast"></i>
                    </button>
                </div>
            </div>

            <!-- Document Checklist -->
            <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 16px;">
                <p style="margin: 0 0 1rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Document Validation (Registrar Use Only)</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 0.95rem; color: #1e293b;">
                        <input type="checkbox" class="doc-check" id="check-or" style="width: 20px; height: 20px; accent-color: #741b1b;">
                        Official Receipt (OR) Verified
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 0.95rem; color: #1e293b;">
                        <input type="checkbox" class="doc-check" id="check-cr" style="width: 20px; height: 20px; accent-color: #741b1b;">
                        Certificate of Registration (CR) Verified
                    </label>
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 0.95rem; color: #1e293b;">
                        <input type="checkbox" class="doc-check" id="check-license" style="width: 20px; height: 20px; accent-color: #741b1b;">
                        Valid Driver's License Presented
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; gap: 1rem;">
            <button class="btn-cancel close-modal" style="flex: 1; padding: 0.8rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; font-weight: 800; color: #64748b; cursor: pointer;">Cancel</button>
            <button id="confirmAddVehicle" disabled title="Check all documents to enable" style="flex: 2; padding: 0.8rem; border-radius: 12px; border: none; background: #64748b; color: #fff; font-weight: 800; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;">
                <i class="ph ph-lightning"></i> Assign Tag & Activate
            </button>
        </div>
    </div>
</div>

<!-- VERIFICATION MODAL -->
<div id="verifyModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width: 950px; border-radius: 28px; display: flex; flex-direction: column; max-height: 95vh; padding: 0;">
        <!-- Fixed Header -->
        <div class="modal-header" style="padding: 2.5rem 2.5rem 1.5rem; margin-bottom: 0;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <i class="ph-fill ph-shield-check" style="font-size: 2.5rem; color: #10b981;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 1.5rem;">Review Submission Documents</h3>
                    <p style="margin: 0; font-size: 0.9rem; color: #64748b;">Verify identity and vehicle credentials for final approval.</p>
                </div>
            </div>
            <button class="close-modal">&times;</button>
        </div>

        <!-- Scrollable Body -->
        <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 1.5rem 2.5rem;">
            <div id="verifyDetails">
                <!-- Applicant Badge -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin: 0 0 0.25rem; font-size: 0.75rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Applicant Identity</p>
                        <strong id="v-name" style="color: #1e293b; font-size: 1.25rem;"></strong>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0 0 0.25rem; font-size: 0.75rem; color: #94a3b8; font-weight: 800; text-transform: uppercase;">University ID Number</p>
                        <span id="v-univ-id" style="font-family: 'JetBrains Mono', monospace; color: #1e293b; font-weight: 800; font-size: 1.1rem;"></span>
                    </div>
                </div>

                <!-- 2x2 Clean Grid -->
                <div class="document-previews" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                    <div class="doc-card">
                        <label>Certificate of Registration (CR)</label>
                        <div class="img-container lightbox-trigger" id="p-cr-container">
                            <div class="img-placeholder" id="p-cr">No file</div>
                            <div class="img-overlay-zoom">
                                <i class="ph ph-magnifying-glass-plus"></i>
                                <span>INSPECT DOCUMENT</span>
                            </div>
                        </div>
                    </div>
                    <div class="doc-card">
                        <label>Official Receipt (OR)</label>
                        <div class="img-container lightbox-trigger" id="p-or-container">
                            <div class="img-placeholder" id="p-or">No file</div>
                            <div class="img-overlay-zoom">
                                <i class="ph ph-magnifying-glass-plus"></i>
                                <span>INSPECT DOCUMENT</span>
                            </div>
                        </div>
                    </div>
                    <div class="doc-card">
                        <label>Driver's License</label>
                        <div class="img-container lightbox-trigger" id="p-license-container">
                            <div class="img-placeholder" id="p-license">No file</div>
                            <div class="img-overlay-zoom">
                                <i class="ph ph-magnifying-glass-plus"></i>
                                <span>INSPECT DOCUMENT</span>
                            </div>
                        </div>
                    </div>
                    <div class="doc-card">
                        <label>University Identity (Faculty/Student ID)</label>
                        <div class="img-container lightbox-trigger" id="p-idcard-container">
                            <div class="img-placeholder" id="p-idcard">No file</div>
                            <div class="img-overlay-zoom">
                                <i class="ph ph-magnifying-glass-plus"></i>
                                <span>INSPECT DOCUMENT</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Textarea (Slide-In Animation) -->
                <div id="rejectionSection" style="display: none; margin-top: 2.5rem; padding: 1.5rem; background: #fff1f2; border: 2px solid #fecaca; border-radius: 20px; animation: slideUp 0.3s ease-out;">
                    <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem;">
                        <i class="ph ph-warning-circle" style="color: #ef4444; font-size: 1.5rem;"></i>
                        <label style="font-size: 0.9rem; font-weight: 800; color: #991b1b; text-transform: uppercase;">Submission Rejection Protocol</label>
                    </div>
                    <textarea id="rejectionReasonText" class="form-control" rows="3" 
                              style="border-color: #fecaca; background: #fff;"
                              placeholder="Describe the discrepancy (e.g., 'Expired License', 'Blurred ID Card') so the applicant can resubmit valid documents."></textarea>
                    
                    <div style="margin-top: 1.25rem; display: flex; gap: 1rem;">
                        <button id="cancelRejectAction" class="btn btn-outline" style="flex: 1; border-color: #fecaca; background: #fff;">Cancel</button>
                        <button id="submitRejectAction" class="btn btn-primary" style="flex: 2; background: #ef4444; border-color: #ef4444;">
                            <i class="ph ph-paper-plane-tilt"></i> Dispatch Rejection Email
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Footer -->
        <div class="modal-footer" id="mainModalFooter" style="padding: 2rem 2.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 1.5rem; background: #fff; border-bottom-left-radius: 28px; border-bottom-right-radius: 28px;">
            <button class="btn btn-outline close-modal" style="flex: 1;">Close Registry</button>
            <button id="btnRejectMode" class="btn btn-outline" style="flex: 1; border-color: #ef4444; color: #ef4444;">
                <i class="ph ph-x-circle"></i> Reject Submission
            </button>
            <button id="confirmVerify" class="btn btn-primary" style="flex: 2; background: #10b981; border-color: #10b981;">
                <i class="ph ph-check-circle"></i> Verify & Finalize Enrollment
            </button>
        </div>
    </div>
</div>

<!-- INLINE LIGHTBOX OVERLAY -->
<div id="lightboxOverlay" class="lightbox-overlay" style="display: none;">
    <div class="lightbox-container">
        <img id="lightboxImg" src="" alt="Zoomed Document">
        <div class="lightbox-controls">
            <button id="closeLightbox" class="lightbox-close"><i class="ph ph-x"></i></button>
        </div>
    </div>
</div>

<style>
    :root {
        --evsu-primary: #741b1b;
        --evsu-hover: #5d1515;
    }

    .table-container { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); }
    .users-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .users-table th { padding: 16px; text-align: left; background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800; border-bottom: 2px solid #f1f5f9; }
    .users-table td { padding: 18px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .user-row { transition: all 0.2s; }
    .user-row:hover { background: #fcfcfc; }

    .owner-name { font-weight: 700; color: #1e293b; font-size: 1rem; margin-bottom: 4px; }
    .owner-meta { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #94a3b8; }
    .univ-id-span { font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #475569; }
    .owner-role-tag { color: #64748b; font-weight: 600; }
    .meta-dot { width: 4px; height: 4px; border-radius: 50%; background: #e2e8f0; }

    .vehicle-line-item { font-size: 0.85rem; color: #334155; background: #f8fafc; padding: 6px 12px; border-radius: 10px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; width: fit-content; transition: 0.2s; }
    .vehicle-line-item:hover { transform: translateX(2px); border-color: #cbd5e1; }
    .v-details { font-weight: 700; color: #1e293b; }
    .v-plate { font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #741b1b; }
    .v-expiry-info { display: flex; align-items: center; gap: 5px; font-size: 0.8rem; }
    
    .badge { padding: 6px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; border: 1px solid transparent; }
    .badge-success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
    .badge-warning { background: #fffbeb; color: #92400e; border-color: #fef3c7; }
    .badge-danger { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }
    .badge-info { background: #eff6ff; color: #1e40af; border-color: #dbeafe; }

    .action-group { display: flex; justify-content: flex-end; gap: 8px; }
    .btn-icon { width: 36px; height: 36px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; color: #64748b; transition: all 0.2s; }
    .btn-icon:hover { background: #f8fafc; color: #1e293b; transform: translateY(-2px); border-color: #cbd5e1; }

    /* Modal Styling */
    .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 2000; transition: 0.3s; }
    .modal-content { background: white; box-shadow: 0 25px 60px rgba(0,0,0,0.15); width: 95%; overflow: hidden; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .close-modal { background: #f1f5f9; border: none; width: 34px; height: 34px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .close-modal:hover { background: #fee2e2; color: #ef4444; }
    .modal-body { padding: 1.5rem; }
    .modal-footer { padding: 1.5rem; background: #f8fafc; }

    /* Form Controls */
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; }
    .form-control { width: 100%; padding: 0.85rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; outline: none; transition: 0.2s; font-size: 0.95rem; }
    .form-control:focus { border-color: #741b1b; box-shadow: 0 0 0 3px rgba(116, 27, 27, 0.1); }
    .modal-body .form-label { color: #741b1b; }

    /* Doc Cards & Grid */
    .doc-card label { display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; }
    .img-container { position: relative; border: 2px dashed #e2e8f0; border-radius: 20px; min-height: 220px; display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: zoom-in; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .img-container:hover { border-color: #3b82f6; transform: translateY(-4px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.1); }
    .img-container:hover .img-overlay-zoom { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    
    .img-overlay-zoom { pointer-events: none; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) scale(0.9); background: rgba(30, 41, 59, 0.95); color: white; padding: 0.75rem 1.5rem; border-radius: 99px; font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 10px; opacity: 0; transition: 0.3s; z-index: 10; border: 1px solid rgba(255,255,255,0.1); }
    .img-placeholder img { width: 100%; height: 220px; object-fit: cover; border-radius: 18px; }

    /* Lightbox */
    .lightbox-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; z-index: 3000; animation: fadeIn 0.2s ease; }
    .lightbox-container { position: relative; max-width: 90%; max-height: 90%; }
    .lightbox-container img { max-width: 100%; max-height: 90vh; border-radius: 12px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
    .lightbox-close { position: absolute; top: -50px; right: -50px; background: white; border: none; width: 50px; height: 50px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .btn-add-vehicle-inline { background: none; border: 1px dashed #cbd5e1; color: #64748b; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px; }

    /* Ensure SweetAlert2 always appears above the modal overlay */
    .swal-on-top { z-index: 9999 !important; }
</style>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const searchInput = document.getElementById("searchInput");
        const roleFilter = document.getElementById("roleFilter");
        const statusFilter = document.getElementById("statusFilter");
        const rows = document.querySelectorAll(".user-row");

        function filterTable() {
            const q = searchInput.value.toLowerCase();
            const role = roleFilter.value.toLowerCase();
            const status = statusFilter.value.toLowerCase();

            rows.forEach(row => {
                const name = row.dataset.name.toLowerCase();
                const univId = row.dataset.univId.toLowerCase();
                const plates = row.dataset.plates.toLowerCase();
                const rRole = row.dataset.role.toLowerCase();
                const rStatus = row.dataset.status.toLowerCase();

                const matchesSearch = name.includes(q) || univId.includes(q) || plates.includes(q);
                const matchesRole = role === "all" || rRole === role;
                const matchesStatus = status === "all" || rStatus === status;

                row.style.display = (matchesSearch && matchesRole && matchesStatus) ? "" : "none";
            });
        }

        searchInput.addEventListener("input", filterTable);
        roleFilter.addEventListener("change", filterTable);
        statusFilter.addEventListener("change", filterTable);

        const toast = (msg, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon, title: msg });

        const addVehicleModal = document.getElementById('addVehicleModal');
        const verifyModal = document.getElementById('verifyModal');
        const lightboxOverlay = document.getElementById('lightboxOverlay');
        const lightboxImg = document.getElementById('lightboxImg');
        
        let currentOwnerId = null;
        let currentVerifyId = null;

        // Modal Controls
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                addVehicleModal.style.display = 'none';
                verifyModal.style.display = 'none';
                document.getElementById('rejectionSection').style.display = 'none';
                document.getElementById('mainModalFooter').style.display = 'flex';
            });
        });

        // Add Vehicle
        document.querySelectorAll('.btn-add-vehicle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                currentOwnerId = btn.dataset.id;
                document.getElementById('add-v-owner').textContent = btn.dataset.name;
                addVehicleModal.style.display = 'flex';
                // Reset form
                document.getElementById('new-details').value = '';
                document.getElementById('new-plate').value = '';
                document.getElementById('new-tag').value = '';
                document.getElementById('new-vehicle-type').value = '';
                document.getElementById('new-brand').value = '';
                const modelSel = document.getElementById('new-model');
                modelSel.innerHTML = '<option value="" disabled selected>Select Brand First...</option>';
                document.querySelectorAll('.doc-check').forEach(c => c.checked = false);
                updateCommitState();
            });
        });

        // Dynamic Chained Dropdowns for Add Vehicle Modal
        const addVType = document.getElementById('new-vehicle-type');
        const addBrand = document.getElementById('new-brand');
        const addModel = document.getElementById('new-model');
        const addBrandLoader = document.getElementById('new-brand-loader');
        const addModelLoader = document.getElementById('new-model-loader');

        addVType.addEventListener('change', async function() {
            const categoryId = this.selectedOptions[0].dataset.id;
            if (!categoryId) return;
            
            addBrand.innerHTML = '<option value="" disabled selected>Loading brands...</option>';
            addBrand.disabled = true;
            addModel.innerHTML = '<option value="" disabled selected>Select Brand First...</option>';
            addModel.disabled = true;
            addBrandLoader.style.display = 'block';

            try {
                const res = await fetch(`/api/brands/${categoryId}`);
                const brands = await res.json();
                
                addBrand.innerHTML = '<option value="" disabled selected>Select Brand...</option>';
                if (brands.length === 0) {
                    addBrand.innerHTML = '<option value="" disabled selected>No specific brands for this category</option>';
                } else {
                    brands.forEach(b => {
                        const opt = document.createElement('option');
                        opt.value = b.name;
                        opt.innerText = b.name;
                        opt.dataset.id = b.id;
                        addBrand.appendChild(opt);
                    });
                }
                addBrand.innerHTML += '<option value="Other" data-id="">Other / Brand Not Listed</option>';
                addBrand.disabled = false;
            } catch (e) {
                console.error('Fetch brands error:', e);
                addBrand.innerHTML = '<option value="" disabled selected>Error loading brands</option>';
                addBrand.innerHTML += '<option value="Other" data-id="">Other / Brand Not Listed</option>';
                addBrand.disabled = false;
            } finally {
                addBrandLoader.style.display = 'none';
            }
        });

        addBrand.addEventListener('change', async function() {
            const brandId = this.selectedOptions[0].dataset.id;
            addModel.innerHTML = '<option value="" disabled selected>Loading models...</option>';
            addModel.disabled = true;
            addModelLoader.style.display = 'block';

            if (!brandId) {
                addModel.innerHTML = '<option value="Other (Not Listed)" selected>Other / Not Listed</option>';
                addModel.disabled = false;
                addModelLoader.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`/api/models/${brandId}`);
                const models = await res.json();
                
                addModel.innerHTML = '<option value="" disabled selected>Select Model...</option>';
                if (models.length === 0) {
                    addModel.innerHTML = '<option value="Other (Not Listed)" selected>Other / Not Listed</option>';
                } else {
                    models.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.name;
                        opt.innerText = m.name;
                        addModel.appendChild(opt);
                    });
                    addModel.innerHTML += '<option value="Other (Not Listed)">Other / Model Not Listed</option>';
                }
                addModel.disabled = false;
            } catch (e) {
                console.error('Fetch models error:', e);
                addModel.innerHTML = '<option value="" disabled selected>Error loading models</option>';
                addModel.innerHTML += '<option value="Other (Not Listed)" selected>Other / Not Listed</option>';
                addModel.disabled = false;
            } finally {
                addModelLoader.style.display = 'none';
            }
        });

        function getConstructedDetails() {
            const brand = addBrand.value;
            const model = addModel.value;
            return `${brand} ${model}`.trim();
        }

        // 0. WebSocket Bridge Integration
        let ws;
        const connectBridge = () => {
            ws = new WebSocket('ws://localhost:8080');
            ws.onopen = () => console.log('Bridge Connected');
            ws.onmessage = (e) => {
                try {
                    const data = JSON.parse(e.data);
                    if (data.tagId && addVehicleModal.style.display === 'flex') {
                        document.getElementById('new-tag').value = data.tagId;
                        toast(`Tag Captured: ${data.tagId}`, 'success');
                    }
                } catch (err) { console.error('WS Error:', err); }
            };
            ws.onclose = () => {
                console.log('Bridge Disconnected. Retrying...');
                setTimeout(connectBridge, 3000);
            };
        };
        connectBridge();

        // 1. Scan Toggle (Shortcut)
        document.getElementById('btnScanTag').onclick = () => {
            const tagInput = document.getElementById('new-tag');
            tagInput.focus();
            toast('Ready for scanning...', 'info');
        };

        // 2. Checklist Validation
        const docChecks = document.querySelectorAll('.doc-check');
        const commitBtn = document.getElementById('confirmAddVehicle');

        const updateCommitState = () => {
            const allChecked = Array.from(docChecks).every(c => c.checked);
            if (allChecked) {
                commitBtn.disabled = false;
                commitBtn.style.background = '#741b1b';
                commitBtn.style.cursor = 'pointer';
                commitBtn.title = 'Ready to Activate';
            } else {
                commitBtn.disabled = true;
                commitBtn.style.background = '#64748b';
                commitBtn.style.cursor = 'not-allowed';
                commitBtn.title = 'Check all documents to enable';
            }
        };

        docChecks.forEach(c => c.onchange = updateCommitState);

        // 3. One-Click Assignment & Activation
        commitBtn.addEventListener('click', async () => {
            const plate   = document.getElementById('new-plate').value.trim();
            const tag     = document.getElementById('new-tag').value.trim();
            const details = document.getElementById('new-details').value.trim();
            const type    = document.getElementById('new-vehicle-type').value;
            
            if (!plate || !tag || !type) {
                Swal.fire({ 
                    title: 'Missing Fields', 
                    text: 'Please fill in Vehicle Category, Plate Number, and RFID Tag.', 
                    icon: 'warning',
                    customClass: { container: 'swal-on-top' }
                });
                return;
            }

            commitBtn.disabled = true;
            commitBtn.innerHTML = '<i class="ph ph-circle-notch-bold"></i> Activating...';

            try {
                const res = await fetch(`/office/users/${currentOwnerId}/add-vehicle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        plate_number: plate, 
                        vehicle_details: details || getConstructedDetails() || type, 
                        rfid_tag: tag, 
                        vehicle_type: type 
                    })
                });
                const data = await res.json();

                if (data.success) {
                    addVehicleModal.style.display = 'none';
                    await Swal.fire({ 
                        icon: 'success', 
                        title: 'Vehicle Activated!', 
                        text: data.message,
                        customClass: { container: 'swal-on-top' }
                    });
                    location.reload();
                } else { 
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Failed', 
                        text: data.message,
                        customClass: { container: 'swal-on-top' }
                    });
                    commitBtn.disabled = false;
                    updateCommitState();
                    commitBtn.innerHTML = '<i class="ph ph-lightning"></i> Assign Tag & Activate';
                }
            } catch (e) { 
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Server Error', 
                    text: 'Could not reach the server. Please try again.',
                    customClass: { container: 'swal-on-top' }
                });
                commitBtn.disabled = false;
                updateCommitState();
            }
        });

        // Verification Modal Launch
        document.querySelectorAll('.btn-verify').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                currentVerifyId = btn.dataset.id;
                document.getElementById('v-name').textContent = row.dataset.name;
                document.getElementById('v-univ-id').textContent = row.dataset.univId;
                
                const setImg = (id, src) => {
                    const el = document.getElementById(id);
                    if (src) {
                        el.innerHTML = `<img src="${src}" data-fullsrc="${src}" class="zoom-target">`;
                        el.parentElement.style.opacity = '1';
                        el.parentElement.style.cursor = 'zoom-in';
                    } else {
                        el.innerHTML = `<div style="padding:2rem; color:#94a3b8; font-weight:800; font-size:0.75rem;">NO UPLOAD</div>`;
                        el.parentElement.style.opacity = '0.5';
                        el.parentElement.style.cursor = 'default';
                    }
                };

                setImg('p-cr', row.dataset.cr);
                setImg('p-or', row.dataset.or);
                setImg('p-license', row.dataset.license);
                setImg('p-idcard', row.dataset.idcard);

                verifyModal.style.display = 'flex';
            });
        });

        // Inline Lightbox Logic
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.zoom-target');
            if (trigger && trigger.dataset.fullsrc) {
                lightboxImg.src = trigger.dataset.fullsrc;
                lightboxOverlay.style.display = 'flex';
            }
        });

        document.getElementById('closeLightbox').onclick = () => lightboxOverlay.style.display = 'none';
        lightboxOverlay.onclick = (e) => { if(e.target === lightboxOverlay) lightboxOverlay.style.display = 'none'; };

        // Post-Verification Action
        document.getElementById('confirmVerify').addEventListener('click', async () => {
            const result = await Swal.fire({
                title: 'Finalize Registration?',
                text: "This will approve the user profile and send a confirmation email. Proceed?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, finalize',
                customClass: { container: 'swal-on-top' }
            });

            if (result.isConfirmed) {
                const btn = document.getElementById('confirmVerify');
                btn.disabled = true;
                btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Processing...';
                
                // Full Screen Processing Overlay
                Swal.fire({
                    title: 'Processing Enrollment',
                    text: 'Please wait while we finalize the profile and dispatch notice...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: { container: 'swal-on-top' },
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const res = await fetch(`/office/registration/${currentVerifyId}/verify`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                    const data = await res.json();

                    if (res.ok && data.success) { 
                        Swal.fire({ icon: 'success', title: 'Verified!', text: data.message, timer: 2000, showConfirmButton: false, customClass: { container: 'swal-on-top' } });
                        setTimeout(() => location.reload(), 2000); 
                    } else { 
                        Swal.fire({ icon: 'error', title: 'Action Failed', text: data.message || 'Error occurred.', customClass: { container: 'swal-on-top' } });
                        btn.disabled = false; 
                        btn.innerHTML = '<i class="ph ph-check-circle"></i> Verify & Finalize Enrollment'; 
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.', customClass: { container: 'swal-on-top' } });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-check-circle"></i> Verify & Finalize Enrollment';
                }
            }
        });

        // Reject Action
        document.getElementById('btnRejectMode').onclick = () => {
            document.getElementById('rejectionSection').style.display = 'block';
            document.getElementById('mainModalFooter').style.display = 'none';
            document.getElementById('rejectionReasonText').focus();
        };

        document.getElementById('cancelRejectAction').onclick = () => {
            document.getElementById('rejectionSection').style.display = 'none';
            document.getElementById('mainModalFooter').style.display = 'flex';
        };

        document.getElementById('submitRejectAction').onclick = async () => {
            const reason = document.getElementById('rejectionReasonText').value.trim();
            if(!reason) return toast('Reason required.', 'error');
            
            const btn = document.getElementById('submitRejectAction');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Dispatched...';

            Swal.fire({
                title: 'Rejecting Submission',
                text: 'Notifying the applicant of the decision...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: { container: 'swal-on-top' },
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`/office/registration/${currentVerifyId}/reject`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason })
                });
                const data = await res.json();

                if (res.ok && data.success) { 
                    Swal.fire({ icon: 'info', title: 'Submission Rejected', text: data.message, timer: 2000, showConfirmButton: false, customClass: { container: 'swal-on-top' } });
                    setTimeout(() => location.reload(), 2000); 
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error occurred.', customClass: { container: 'swal-on-top' } });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-paper-plane-tilt"></i> Dispatch Rejection Email';
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect to the server.' });
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-paper-plane-tilt"></i> Dispatch Rejection Email';
            }
        };

        // Delete Action
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently purge this account and all linked vehicles!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#741b1b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, purge it!',
                    customClass: { container: 'swal-on-top' }
                });

                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`/office/registration/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const data = await res.json();
                        if (data.success) {
                            toast(data.message);
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            toast('Error deleting account.', 'error');
                        }
                    } catch (e) {
                        toast('Server error.', 'error');
                    }
                }
            });
        });

        // UI Helpers
        const filter = () => {
            const query = searchInput.value.toLowerCase();
            rows.forEach(r => {
                const nameMatch = r.dataset.name.toLowerCase().includes(query);
                const idMatch = r.dataset.univId.toLowerCase().includes(query);
                const plateMatch = r.dataset.plates.toLowerCase().includes(query);
                const officeMatch = r.dataset.office.toLowerCase().includes(query);
                
                const rMatch = roleFilter.value === 'all' || r.dataset.role === roleFilter.value;
                r.style.display = ((nameMatch || idMatch || plateMatch || officeMatch) && rMatch) ? '' : 'none';
            });
        };
        searchInput.oninput = filter;
        roleFilter.onchange = filter;

        window.onclick = (e) => { 
            if(e.target === addVehicleModal) addVehicleModal.style.display = 'none';
            if(e.target === verifyModal) {
                verifyModal.style.display = 'none';
                document.getElementById('rejectionSection').style.display = 'none';
                document.getElementById('mainModalFooter').style.display = 'flex';
            }
        };
    });
</script>
@endsection
@endsection
