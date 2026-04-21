@extends('layouts.app')

@section('title', 'RFID Master Management')
@section('subtitle', 'Oversee all registered vehicle tags, monitor status, and manage security access.')

@section('content')

<!-- RFID Status Summary -->
<div class="dashboard-grid mb-8">
    <div class="stat-card">
        <div class="stat-label">Total Registered Tags</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <i class="ph ph-identification-card stat-icon"></i>
    </div>
    
    <div class="stat-card" style="border-left: 4px solid #10b981;">
        <div class="stat-label">Active / Authorized</div>
        <div class="stat-value text-success">{{ $stats['active'] }}</div>
        <i class="ph ph-check-circle stat-icon" style="color: #10b981; opacity: 0.2;"></i>
    </div>
    
    <div class="stat-card" style="border-left: 4px solid #ef4444;">
        <div class="stat-label">Blacklisted / Blocked</div>
        <div class="stat-value text-danger">{{ $stats['blacklisted'] }}</div>
        <i class="ph ph-prohibit stat-icon" style="color: #ef4444; opacity: 0.2;"></i>
    </div>
</div>

<div class="table-container">
    <div class="section-header" style="flex-direction: column; align-items: stretch; gap: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Master Tag Directory</h3>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.rfid.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Register New Tag</a>
                <button class="btn btn-outline" onclick="window.location.reload()"><i class="ph ph-arrows-clockwise"></i> Refresh</button>
                <a href="{{ route('admin.reports') }}" class="btn btn-outline"><i class="ph ph-file-text"></i> View Audit Logs</a>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <form action="{{ route('admin.rfid') }}" method="GET" style="display: flex; gap: 15px; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="flex-grow: 1; position: relative;">
                <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Owner, Plate, or RFID Tag ID..." 
                    style="width: 100%; padding: 0.7rem 1rem 0.7rem 2.8rem; border: 1px solid #e2e8f0; border-radius: 8px; outline: none;">
            </div>
            <select name="status" style="padding: 0.7rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; background: white; outline: none;">
                <option value="all">All Status</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Active</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Blacklisted</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 0 1.5rem;">Apply Filter</button>
            @if(request()->has('search') || (request()->has('status') && request('status') != 'all'))
                <a href="{{ route('admin.rfid') }}" class="btn btn-outline" style="padding: 0.7rem 1rem; color: #ef4444; border-color: #fee2e2;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tag ID</th>
                    <th>Owner / Vehicle</th>
                    <th>Plate Number</th>
                    <th>Date Registered</th>
                    <th>Registered By</th>
                    <th>Status</th>
                    <th style="text-align: right;">Security Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                <tr id="row-{{ $reg->id }}">
                    <td>
                        <div style="font-family: monospace; font-weight: 600; color: #1e293b; background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block;">
                            {{ $reg->rfid_tag_id }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">{{ $reg->full_name }}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">
                            <span style="font-weight: 700; text-transform: uppercase;">{{ $reg->vehicle_type }}</span> • 
                            {{ $reg->make_brand . ' ' . ($reg->model_name ?? '') }}
                        </div>
                    </td>
                    <td><span class="badge-plate">{{ $reg->plate_number }}</span></td>
                    <td>
                        <div style="font-size: 0.85rem; color: #475569; font-weight: 600;">{{ $reg->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 0.7rem; color: #94a3b8;">{{ $reg->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: #475569;">{{ $reg->officeUser->name ?? 'System' }}</div>
                    </td>
                    <td id="status-cell-{{ $reg->id }}">
                        @if($reg->status === 'approved')
                            <span class="badge badge-active"><i class="ph ph-check-circle"></i> Authorized</span>
                        @else
                            <span class="badge badge-blocked"><i class="ph ph-prohibit"></i> Blacklisted</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button class="btn btn-icon" onclick="viewDetails({{ $reg->id }})" title="View Details">
                                <i class="ph ph-eye"></i>
                            </button>
                            <a href="{{ route('admin.rfid.create') }}?id={{ $reg->id }}" class="btn btn-icon" title="Edit Registration">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <button id="toggle-btn-{{ $reg->id }}" 
                                    class="btn {{ $reg->status === 'approved' ? 'btn-danger' : 'btn-success' }}" 
                                    style="padding: 0.4rem 0.8rem; font-size: 0.75rem; min-width: 100px;"
                                    onclick="toggleTagStatus({{ $reg->id }}, '{{ $reg->status }}')">
                                <i class="ph {{ $reg->status === 'approved' ? 'ph-lock' : 'ph-lock-open' }}"></i>
                                {{ $reg->status === 'approved' ? 'Blacklist' : 'Activate' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 4rem; color: #94a3b8;">
                        <i class="ph ph-identification-card" style="font-size: 3rem; opacity: 0.2; display: block; margin: 0 auto 1rem;"></i>
                        No RFID registrations matched your search.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $registrations->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function toggleTagStatus(id, currentStatus) {
        const action = currentStatus === 'approved' ? 'BLACKLIST' : 'ACTIVATE';
        const color = currentStatus === 'approved' ? '#ef4444' : '#10b981';
        
        const result = await Swal.fire({
            title: `${action} Tag?`,
            text: `Are you sure you want to ${action.toLowerCase()} this RFID tag? This will immediately affect campus access.`,
            icon: currentStatus === 'approved' ? 'warning' : 'info',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action}`,
            cancelButtonText: 'Cancel',
            confirmButtonColor: color,
            reverseButtons: true
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`{{ url('admin/rfid') }}/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Update UI dynamically
                    const statusCell = document.getElementById(`status-cell-${id}`);
                    const toggleBtn = document.getElementById(`toggle-btn-${id}`);
                    
                    if (data.new_status === 'approved') {
                        statusCell.innerHTML = '<span class="badge badge-active"><i class="ph ph-check-circle"></i> Authorized</span>';
                        toggleBtn.className = 'btn btn-danger';
                        toggleBtn.innerHTML = '<i class="ph ph-lock"></i> Blacklist';
                        toggleBtn.setAttribute('onclick', `toggleTagStatus(${id}, 'approved')`);
                    } else {
                        statusCell.innerHTML = '<span class="badge badge-blocked"><i class="ph ph-prohibit"></i> Blacklisted</span>';
                        toggleBtn.className = 'btn btn-success';
                        toggleBtn.innerHTML = '<i class="ph ph-lock-open"></i> Activate';
                        toggleBtn.setAttribute('onclick', `toggleTagStatus(${id}, 'rejected')`);
                    }
                }
            } catch (e) {
                Swal.fire('Error', 'Failed to update tag status.', 'error');
            }
        }
    }

    async function viewDetails(id) {
        try {
            const response = await fetch(`{{ url('admin/rfid') }}/${id}`);
            const reg = await response.json();
            
            Swal.fire({
                title: 'RFID Tag Details',
                html: `
                    <div style="text-align: left; font-size: 0.9rem;">
                        <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #e2e8f0;">
                            <div style="margin-bottom: 0.5rem;"><b style="color: #475569;">RFID Tag ID:</b> <span style="font-family: monospace;">${reg.rfid_tag_id}</span></div>
                            <div style="margin-bottom: 0.5rem;"><b style="color: #475569;">Status:</b> ${reg.status === 'approved' ? '<span style="color:#10b981">AUTHORIZED</span>' : '<span style="color:#ef4444">BLACKLISTED</span>'}</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <h4 style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 0.5rem;">Applicant</h4>
                                <div style="font-weight: 600;">${reg.full_name}</div>
                                <div style="font-size: 0.8rem;">${reg.role ? reg.role.toUpperCase() : 'N/A'}</div>
                                <div style="font-size: 0.8rem;">${reg.contact_number || 'N/A'}</div>
                            </div>
                            <div>
                                <h4 style="font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 0.5rem;">Vehicle</h4>
                                <div style="font-weight: 600;">${reg.plate_number}</div>
                                <div style="font-size: 0.8rem;">${reg.make_brand} ${reg.model_name || ''}</div>
                                <div style="font-size: 0.8rem; font-weight: 700;">${reg.vehicle_type.toUpperCase()}</div>
                            </div>
                        </div>
                        <hr style="margin: 1rem 0; border: 0; border-top: 1px solid #e2e8f0;">
                        <div style="font-size: 0.75rem; color: #64748b;">
                            Registered by ${reg.office_user ? reg.office_user.name : 'System'} on ${new Date(reg.created_at).toLocaleDateString()}
                        </div>
                    </div>
                `,
                confirmButtonColor: '#1e293b'
            });
        } catch (e) {
            Swal.fire('Error', 'Could not load tag details.', 'error');
        }
    }

    // Legacy modal registration logic removed in favor of rfid.create view
</script>

<style>
    .badge-plate { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 700; font-family: 'Outfit', sans-serif; font-size: 0.85rem; }
    .badge { padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .badge-active { background: #ecfdf5; color: #059669; }
    .badge-blocked { background: #fef2f2; color: #dc2626; }
    .btn-icon { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .btn-icon:hover { background: #f1f5f9; color: #1e293b; }
    .btn-success { background: #10b981; color: white; border: none; border-radius: 8px; transition: opacity 0.2s; }
    .btn-success:hover { opacity: 0.9; }
    .btn-danger { background: #ef4444; color: white; border: none; border-radius: 8px; transition: opacity 0.2s; }
    .btn-danger:hover { opacity: 0.9; }
    
    /* Mode Toggle for Modal */
    .btn-mode {
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 6px;
        font-weight: 600;
        outline: none;
    }
    .btn-mode.active {
        background: white;
        color: #1e293b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>

@endsection
