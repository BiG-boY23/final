@extends('layouts.app')

@section('title', 'Admin Overview')
@section('subtitle', 'Manage system settings and users.')

@section('content')
<div class="dashboard-header-row no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: var(--bg-card, white); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--border-color, #e2e8f0);">
    <div class="header-text">
        <h2 style="margin:0; font-size: 1.25rem; font-weight: 800; color: var(--text-main, #1e293b);">System Command Center</h2>
        <p style="margin:0; font-size: 0.85rem; color: var(--text-muted, #64748b);">{{ $stats['is_filtered'] ? 'Showing traffic data from ' . \Carbon\Carbon::parse($stats['from'])->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($stats['to'])->format('M d, Y') : 'Real-time monitoring and oversight.' }}</p>
    </div>
    
    <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: center;">
        <div style="display: flex; align-items: center; background: var(--bg-app, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 4px 8px; gap: 8px;">
            <i class="ph ph-calendar" style="color: var(--text-muted, #64748b);"></i>
            <input type="date" name="from" value="{{ $stats['from'] }}" style="border:none; background:transparent; font-size: 0.85rem; font-weight: 600; outline:none; color: var(--text-main, #1e293b);">
            <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted, #94a3b8);">TO</span>
            <input type="date" name="to" value="{{ $stats['to'] }}" style="border:none; background:transparent; font-size: 0.85rem; font-weight: 600; outline:none; color: var(--text-main, #1e293b);">
        </div>
        <button type="submit" style="background: #741b1b; color: white; border: none; padding: 0.6rem 1rem; border-radius: 10px; cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 6px;">
            <i class="ph ph-funnel"></i> Filter
        </button>
        @if($stats['is_filtered'])
            <a href="{{ route('admin.dashboard') }}" style="background: var(--bg-card, #f1f5f9); color: var(--text-muted, #64748b); padding: 0.6rem 1rem; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none; border: 1px solid var(--border-color, transparent);">Reset</a>
        @endif
    </form>
</div>

<!-- Summary Cards -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
    <!-- Total RFID Tags -->
    <div class="stat-card" style="border-left: 4px solid var(--bg-sidebar);">
        <div class="stat-label" style="color: var(--bg-sidebar);">Total RFID Tags</div>
        <div class="stat-value">{{ number_format($stats['total_rfid']) }}</div>
        <i class="ph ph-identification-card stat-icon" style="color: var(--bg-sidebar); opacity: 0.15;"></i>
    </div>

    <!-- Active Tags -->
    <div class="stat-card" style="border-left: 4px solid var(--color-success);">
        <div class="stat-label" style="color: var(--color-success);">Active Tags</div>
        <div class="stat-value">{{ number_format($stats['active_rfid']) }}</div>
        <i class="ph ph-check-circle stat-icon" style="color: var(--color-success); opacity: 0.15;"></i>
    </div>

    <!-- Deactivated/Blacklisted -->
    <div class="stat-card" style="border-left: 4px solid var(--color-danger);">
        <div class="stat-label" style="color: var(--color-danger);">Blacklisted / Inactive</div>
        <div class="stat-value">{{ $stats['blacklisted_rfid'] }}</div>
        <i class="ph ph-prohibit stat-icon" style="color: var(--color-danger); opacity: 0.15;"></i>
    </div>

    <!-- Today's Entries -->
    <div class="stat-card" style="border-left: 4px solid var(--color-evsu-gold);">
        <div class="stat-label" style="color: var(--color-primary-hover);">{{ $stats['is_filtered'] ? 'Entries in Range' : 'Entries Today' }}</div>
        <div class="stat-value">{{ number_format($stats['entries_today']) }}</div>
        <i class="ph ph-car stat-icon" style="color: var(--color-evsu-gold); opacity: 0.2;"></i>
    </div>

    <!-- Current Occupancy -->
    <div class="stat-card" style="border-left: 4px solid #3b82f6;">
        <div class="stat-label" style="color: #3b82f6;">Current Occupancy</div>
        <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.5rem;">
            {{ number_format($stats['current_occupancy']) }}
            <span style="font-size: 0.9rem; font-weight: 400; color: #94a3b8;">/ {{ $stats['total_capacity'] }}</span>
        </div>
        <i class="ph ph-buildings stat-icon" style="color: #3b82f6; opacity: 0.2;"></i>
        @php
            $occPercent = min(100, ($stats['current_occupancy'] / $stats['total_capacity']) * 100);
        @endphp
        <div style="width: 100%; height: 4px; background: #eff6ff; border-radius: 99px; margin-top: 1rem; overflow: hidden;">
            <div style="width: {{ $occPercent }}%; height: 100%; background: #3b82f6; border-radius: 99px;"></div>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; margin-top: 2rem;">
    <!-- Recent Activity / Audit Log -->
    <div class="table-container">
        <div class="section-header">
            <h3><i class="ph ph-files" style="color: var(--color-evsu-gold); margin-right: 8px;"></i> Recent Audit Logs</h3>
            <a href="{{ route('admin.reports') }}" class="btn btn-outline" style="font-size: 0.85rem;">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $log['time'] }}</td>
                        <td style="font-weight: 500; color: var(--bg-sidebar);">{{ $log['user'] }}</td>
                        <td>
                            <span class="badge badge-neutral">{{ $log['action'] }}</span>
                        </td>
                        <td style="color: var(--text-muted);">{{ $log['details'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions / System Status -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- System Status & Bridge Diagnostics -->
        <div class="stat-card">
            <div class="section-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                <h3 style="font-size: 1rem; color: #1e293b;"><i class="ph ph-shield-check"></i> System Diagnostics</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @php
                    $lastHeartbeat = \Illuminate\Support\Facades\Cache::get('bridge_last_heartbeat');
                    $isOnline = $lastHeartbeat && \Carbon\Carbon::parse($lastHeartbeat)->diffInMinutes(now()) < 1;
                    $comPort = \Illuminate\Support\Facades\Cache::get('bridge_com_port', 'Unknown');
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.9rem; color: #64748b;">Bridge Service</span>
                        @if($isOnline)
                            <span class="badge" style="background: #ecfdf5; color: #059669; border: 1px solid #10b981;">Online ({{ $comPort }})</span>
                        @else
                            <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #ef4444;">Offline</span>
                        @endif
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase;">Hardware Control</div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem;">
                        <button id="btnRestartScanner" class="btn btn-outline" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; color: #b91c1c; border-color: #fecaca; background: white;" {{ !$isOnline ? 'disabled' : '' }}>
                            <i class="ph ph-arrows-clockwise"></i> Restart Bridge
                        </button>
                        <a href="{{ route('admin.logs') }}" class="btn btn-outline" style="flex: 1; padding: 0.5rem; font-size: 0.75rem; text-decoration: none; text-align: center;">
                            <i class="ph ph-list-magnifying-glass"></i> Logs
                        </a>
                    </div>
                </div>

                <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.4rem;">
                     <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <span style="color: #64748b;">Gate Database</span>
                        <span style="color: var(--color-success); font-weight: 600;">CONNECTED</span>
                     </div>
                     <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <span style="color: #64748b;">Server Cluster</span>
                        <span style="color: var(--color-success); font-weight: 600;">ACTIVE</span>
                     </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="table-container" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                <a href="{{ route('admin.users') }}" class="btn btn-primary" style="justify-content: center;">
                    <i class="ph ph-user-plus"></i> Add New User
                </a>
                <a href="{{ route('admin.settings') }}" class="btn btn-outline" style="justify-content: center;">
                    <i class="ph ph-gear"></i> System Settings
                </a>
                @php 
                    $lockDownState = \Illuminate\Support\Facades\Cache::get('system_lockdown', ['active' => false, 'reason' => '']);
                    if (!is_array($lockDownState)) {
                        $lockDownState = ['active' => (bool)$lockDownState, 'reason' => ''];
                    }
                    $isLocked = $lockDownState['active'];
                @endphp
                <button type="button" id="btnLockdown" class="btn" style="justify-content: center; width: 100%; border-radius: 6px; padding: 0.75rem; font-weight: 700; transition: 0.3s;
                    {{ $isLocked 
                        ? 'background: #dc2626; color: white; border: none; animation: lockdown-pulse 2s infinite;' 
                        : 'border: 1px solid var(--color-danger); color: var(--color-danger); background: white;' 
                    }}">
                    <i class="ph {{ $isLocked ? 'ph-lock-simple-open' : 'ph-lock-key' }}"></i> 
                    {{ $isLocked ? 'Deactivate Lockdown' : 'Emergency Lockdown' }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lockdown History Section -->
<div class="dashboard-grid" style="margin-top: 2rem;">
    <div class="table-container">
        <div class="section-header">
            <h3><i class="ph ph-shield-warning" style="color: #dc2626; margin-right: 8px;"></i> Lockdown Activity Historian</h3>
            <span style="font-size: 0.8rem; color: #64748b;">Most recent emergency interventions</span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Started At</th>
                        <th>Ended At</th>
                        <th>Administrator</th>
                        <th>Reason / Incident</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lockdownHistory as $rec)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($rec->started_at)->format('M d, Y h:i A') }}</td>
                        <td>{{ $rec->ended_at ? \Carbon\Carbon::parse($rec->ended_at)->format('M d, Y h:i A') : '-- STILL ACTIVE --' }}</td>
                        <td>{{ $rec->admin->first_name ?? 'Admin' }} {{ $rec->admin->last_name ?? '' }}</td>
                        <td style="font-weight: 600; color: #b91c1c;">{{ $rec->reason }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 2rem; color: var(--text-muted);">No lockdown events recorded in the history.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
<style>
    @keyframes lockdown-pulse {
        0% { background: #dc2626; box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
        70% { background: #991b1b; box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); }
        100% { background: #dc2626; box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnLockdown = document.getElementById('btnLockdown');
    if (btnLockdown) {
        btnLockdown.addEventListener('click', function() {
            const isActive = {{ $isLocked ? 'true' : 'false' }};
            const title = isActive ? 'Deactivate System Lockdown?' : 'ACTIVATE EMERGENCY LOCKDOWN?';
            const text = isActive ? 'This will resume normal operations and allow vehicle entry.' : 'ALL gate access will be immediately blocked for all users and visitors.';

            Swal.fire({
                title: title,
                text: text,
                input: isActive ? null : 'text',
                inputPlaceholder: 'Brief Reason (e.g., Suspicious Activity, Maintenance)',
                icon: isActive ? 'info' : 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: isActive ? 'Resume Normal' : 'Yes, LOCKDOWN NOW',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!isActive && !value) {
                        return 'You must provide a reason for the lockdown!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value || 'N/A';
                    fetch('{{ route('admin.lockdown.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason: reason })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Lockdown Command Failed');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: data.lockdown ? 'System Locked' : 'System Restored',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        }
                    })
                    .catch(e => {
                        console.error('Lockdown Error:', e);
                        Swal.fire('Command Failed', 'The server encountered an error while processing the lockdown request. Please check the logs.', 'error');
                    });
                }
            });
        });
    }
    const btnRestart = document.getElementById('btnRestartScanner');
    if (btnRestart) {
        btnRestart.addEventListener('click', function() {
            Swal.fire({
                title: 'Restart Bridge Service?',
                text: 'This will reboot the UHF scanner connection. Active scans may be interrupted during the 3-second reboot.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                confirmButtonText: 'Yes, Reboot Hardware'
            }).then((result) => {
                if (result.isConfirmed) {
                    const ws = new WebSocket('ws://127.0.0.1:8080');
                    ws.onopen = function() {
                        ws.send(JSON.stringify({ cmd: 'restart' }));
                        Swal.fire({
                            title: 'Rebooting Hardware...',
                            text: 'Command sent to bridge service. Refreshing dashboard in 3 seconds.',
                            icon: 'info',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => location.reload());
                    };
                    ws.onerror = function() {
                        Swal.fire('Connection Error', 'Could not reach the local bridge. Please ensure the service is running locally.', 'error');
                    }
                }
            });
        });
    }
});
</script>
@endsection
@endsection
