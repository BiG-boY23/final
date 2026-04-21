@extends('layouts.app')

@section('title', 'Tag Expiry Tracking')
@section('subtitle', 'Monitoring registration validity and proactive renewal tracking.')

@section('content')
<div class="stats-container">
    <div class="stats-header">
        <div class="header-info">
            <h2>Tag Lifecycle Oversight</h2>
            <p>Tracking the validity periods of all active vehicle tags and registrations across the campus.</p>
        </div>
        <div class="header-actions">
            <button id="btnSendAlerts" class="btn-export">
                <i class="ph ph-envelope-simple"></i> <span id="btnText">Send Renewal Alerts</span>
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Expired Card -->
        <div class="expiry-card expired">
            <div class="card-icon"><i class="ph-fill ph-warning-circle"></i></div>
            <div class="card-data">
                <span class="label">Expired Tags</span>
                <h3 id="count-expired">{{ $expired }}</h3>
                <span class="trend text-danger"><i class="ph ph-lock-key"></i> Access Blocked</span>
            </div>
            <div class="card-foot" id="foot-expired" style="background: rgba(239, 68, 68, 0.05);">{{ $expiredPerc }}% of Fleet</div>
        </div>

        <!-- Critical Card -->
        <div class="expiry-card critical">
            <div class="card-icon"><i class="ph-fill ph-clock-countdown"></i></div>
            <div class="card-data">
                <span class="label">Expiring Soon (< 30d)</span>
                <h3 id="count-critical">{{ $critical }}</h3>
                <span class="trend text-warning"><i class="ph ph-bell-ringing"></i> Renewal Required</span>
            </div>
            <div class="card-foot" id="foot-critical" style="background: rgba(245, 158, 11, 0.05);">{{ $criticalPerc }}% of Fleet</div>
        </div>

        <!-- Healthy Card -->
        <div class="expiry-card active">
            <div class="card-icon"><i class="ph-fill ph-check-circle"></i></div>
            <div class="card-data">
                <span class="label">Long-term Active</span>
                <h3 id="count-healthy">{{ $healthy }}</h3>
                <span class="trend text-success"><i class="ph ph-shield-check"></i> Compliant Flow</span>
            </div>
            <div class="card-foot" id="foot-healthy" style="background: rgba(16, 185, 129, 0.05);">{{ $healthyPerc }}% of Fleet</div>
        </div>
    </div>

    <!-- Expiry Directory Table -->
    <div class="table-container shadow-sm" style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; margin-top: 1rem;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
             <div style="display: flex; align-items: center; gap: 10px;">
                <i class="ph-bold ph-calendar-check" style="color: #741b1b; font-size: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #1e293b;">Registration Validity Directory</h3>
             </div>
              <div style="display: flex; align-items: center; gap: 1rem;">
                 <div class="filter-chips" style="display: flex; gap: 0.5rem;">
                    <button class="status-chip active" data-filter="all">All</button>
                    <button class="status-chip" data-filter="expired">Expired</button>
                    <button class="status-chip" data-filter="critical">Soon</button>
                    <button class="status-chip" data-filter="healthy">Active</button>
                 </div>
                 <div class="search-box-mini" style="position: relative; width: 250px;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    <input type="text" id="expirySearch" placeholder="Search owner, plate..." style="width: 100%; padding: 8px 12px 8px 35px; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; font-size: 0.85rem; font-weight: 600;">
                 </div>
              </div>
        </div>
        <div class="table-wrapper">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800;">Owner Name</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800;">RFID Tag ID</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800;">Plate</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800;">Registration Date</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; text-align: right;">Validity Until</th>
                        <th style="padding: 1rem 2rem; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 800; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeRegistrations as $reg)
                        @php
                            $expiryDate = \Carbon\Carbon::parse($reg->validity_to)->startOfDay();
                            $today = now()->startOfDay();
                            $isExpired = $expiryDate->isBefore($today);
                            $isExpiringSoon = !$isExpired && $today->diffInDays($expiryDate, false) <= 30;
                            
                            $rowStyle = '';
                            $status = $isExpired ? 'expired' : ($isExpiringSoon ? 'critical' : 'healthy');
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;" data-status="{{ $isExpired ? 'expired' : ($isExpiringSoon ? 'critical' : 'healthy') }}">
                            <td style="padding: 1.25rem 2rem;"><strong>{{ $reg->full_name }}</strong></td>
                            <td style="padding: 1.25rem 2rem; font-family: monospace; font-weight: 700;">{{ $reg->rfid_tag_id }}</td>
                            <td style="padding: 1.25rem 2rem;">{{ $reg->plate_number }}</td>
                            <td style="padding: 1.25rem 2rem;">
                                <div style="font-size: 0.85rem; color: #475569; font-weight: 600;">{{ $reg->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.7rem; color: #94a3b8;">{{ $reg->created_at->format('h:i A') }}</div>
                            </td>
                            <td style="padding: 1.25rem 2rem; text-align: right;">
                                @php
                                    $pillClass = $isExpired ? 'pill-expired' : ($isExpiringSoon ? 'pill-soon' : 'pill-active');
                                @endphp
                                <div class="validity-pill {{ $pillClass }}">
                                    <i class="ph {{ $isExpired ? 'ph-prohibit' : ($isExpiringSoon ? 'ph-clock-countdown' : 'ph-check-circle') }}"></i>
                                    {{ $expiryDate->format('M d, Y') }}
                                </div>
                            </td>
                            <td style="padding: 1.25rem 2rem; text-align: right;">
                                <button class="btn-renew" onclick="openRenewModal('{{ $reg->id }}', '{{ $reg->full_name }}', '{{ $reg->validity_to }}')" title="Renew Registration">
                                    <i class="ph ph-calendar-plus"></i> Renew
                                </button>
                            </td>
                        </tr>
                    @empty
                         <tr>
                             <td colspan="6" style="text-align: center; padding: 4rem; color: #94a3b8;">
                                <i class="ph ph-shield-slash" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p style="margin-top: 1rem;">No active tags found with validity dates.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .stats-container { display: flex; flex-direction: column; gap: 2rem; }
    .stats-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 2rem; border-radius: 20px; border: 1px solid #e2e8f0; }
    .header-info h2 { margin: 0; font-size: 1.5rem; font-weight: 800; color: #1e293b; }
    .header-info p { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }
    
    .btn-export { background: #741b1b; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }

    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .expiry-card { background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; position: relative; }
    .expiry-card .card-icon { position: absolute; top: 1.5rem; right: 1.5rem; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    
    .expired .card-icon { background: #fee2e2; color: #ef4444; }
    .critical .card-icon { background: #fef3c7; color: #f59e0b; }
    .active .card-icon { background: #dcfce7; color: #10b981; }

    .card-data { padding: 1.5rem; }
    .card-data .label { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
    .card-data h3 { margin: 4px 0; font-size: 1.75rem; font-weight: 800; color: #1e293b; }
    .card-data .trend { font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 5px; }

    .card-foot { padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; text-align: center; }

    /* Renew Button Style */
    .btn-renew { 
        background: #fdf2f2; 
        color: #741b1b; 
        border: 1px solid #fee2e2; 
        padding: 6px 14px; 
        border-radius: 8px; 
        font-size: 0.75rem; 
        font-weight: 700; 
        cursor: pointer; 
        display: inline-flex; 
        align-items: center; 
        gap: 6px;
        transition: 0.2s;
    }
    .btn-renew:hover { background: #741b1b; color: white; border-color: #741b1b; }

    /* New Status Chips & Pills */
    .status-chip { 
        background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 6px 16px; border-radius: 8px; 
        font-size: 0.75rem; font-weight: 800; cursor: pointer; transition: 0.2s;
    }
    .status-chip:hover { border-color: #741b1b; color: #741b1b; }
    .status-chip.active { background: #741b1b; color: white; border-color: #741b1b; }

    .validity-pill { 
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 99px; 
        font-size: 0.85rem; font-weight: 700;
    }
    .pill-expired { background: #fee2e2; color: #ef4444; }
    .pill-soon { background: #fef3c7; color: #f59e0b; }
    .pill-active { background: #dcfce7; color: #10b981; }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('btnSendAlerts');
        const btnText = document.getElementById('btnText');

        btn.addEventListener('click', function() {
            Swal.fire({
                title: 'Send Renewal Alerts?',
                text: "This will send an email reminder to all owners whose tags are expiring within the next 30 days.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#741b1b',
                confirmButtonText: 'Yes, send them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    btnText.innerText = 'Sending...';

                    fetch("{{ route(auth()->user()->role . '.stats.expiry.alerts') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        btn.disabled = false;
                        btnText.innerText = 'Send Renewal Alerts';
                        if(data.success) {
                            Swal.fire('Dispatched!', data.message, 'success');
                        } else {
                            Swal.fire('Error', 'Failed to send alerts.', 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btnText.innerText = 'Send Renewal Alerts';
                        Swal.fire('Error', 'A system error occurred.', 'error');
                    });
                }
            });
        });

        // Live Search & Status Filtering
        const searchInput = document.getElementById('expirySearch');
        const chips = document.querySelectorAll('.status-chip');
        let currentFilter = 'all';

        const performFilter = () => {
            const query = searchInput.value.toLowerCase();
            let visibleCount = 0;
            let stats = { expired: 0, critical: 0, healthy: 0 };

            document.querySelectorAll('tbody tr').forEach(row => {
                const status = row.dataset.status;
                if(!status) return;

                const text = row.innerText.toLowerCase();
                const matchesSearch = text.includes(query);
                const matchesStatus = currentFilter === 'all' || status === currentFilter;

                if (matchesSearch && matchesStatus) {
                    row.style.display = 'table-row';
                    visibleCount++;
                    stats[status]++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update Dynamic Stats Cards (Optional for UX)
            // document.getElementById('count-expired').innerText = stats.expired;
            // ... (keep original counts based on server-side logic for consistency)

            const emptyRow = document.querySelector('tbody tr td[colspan="5"]')?.closest('tr');
            if(visibleCount === 0 && emptyRow) {
                emptyRow.style.display = 'table-row';
            } else if (emptyRow) {
                emptyRow.style.display = 'none';
            }
        };

        searchInput.addEventListener('input', performFilter);

        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                performFilter();
            });
        });

        // One-Click Renewal Logic
        window.openRenewModal = function(id, name, currentExpiry) {
            const nextYear = new Date();
            nextYear.setFullYear(nextYear.getFullYear() + 1);
            const defaultDate = nextYear.toISOString().split('T')[0];

            Swal.fire({
                title: 'Renew Registration',
                html: `
                    <div style="text-align: left; padding: 1rem 0;">
                        <p style="margin-bottom: 1rem; font-size: 0.9rem;">Renewing access for <strong>${name}</strong>.</p>
                        <div class="form-group">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem;">New Expiration Date</label>
                            <input type="date" id="newExpiryInput" value="${defaultDate}" class="swal2-input" style="width: 100%; margin: 0;">
                        </div>
                        <p style="margin-top: 1rem; font-size: 0.75rem; color: #94a3b8;">* This will automatically extend validity and update the gate hardware records.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#741b1b',
                confirmButtonText: '<i class="ph ph-check-circle"></i> Confirm Renewal',
                preConfirm: () => {
                    return document.getElementById('newExpiryInput').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processRenewal(id, result.value);
                }
            });
        };

        function processRenewal(id, date) {
            Swal.fire({
                title: 'Processing...',
                text: 'Synchronizing with gate hardware',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Handle dynamic role-based route prefix
            const rolePrefix = '{{ auth()->user()->role }}';
            const url = `/${rolePrefix}/stats/expiry/${id}/renew`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ new_expiry: date })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload(); // Reload to refresh table and stats
                    });
                } else {
                    Swal.fire('Error', data.message || 'Renewal failed.', 'error');
                }
            })
            .catch(err => {
                Swal.fire('System Error', 'Could not complete renewal at this time.', 'error');
            });
        }
    });
</script>
@endsection
