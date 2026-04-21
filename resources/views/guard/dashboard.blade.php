@extends('layouts.app')

@section('title', __('messages.dashboard'))
@section('subtitle', __('messages.monitoring_tag'))

@section('content')

<!-- Real-Time System Clock & Global Search -->
<div class="no-print" style="margin-bottom: 2rem; display: flex; gap: 1.25rem; align-items: center;">
    <!-- Global Search -->
    <div style="flex: 1.5; position: relative;">
        <div style="background: white; padding: 0.75rem 1.25rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 0.8rem;">
            <i class="ph-bold ph-magnifying-glass" style="font-size: 1.25rem; color: #741b1b;"></i>
            <input type="text" id="globalSearchInput" placeholder="Search Plate Number or Owner Name..." 
                style="flex: 1; border: none; outline: none; font-size: 1rem; font-weight: 650; color: #1e293b; background: transparent;">
            <div id="searchLoader" style="display: none;">
                <i class="ph ph-spinner-gap animate-spin" style="font-size: 1.25rem; color: #741b1b;"></i>
            </div>
        </div>
        <!-- Search Results Dropdown -->
        <div id="searchResultsDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1100; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 15px 25px -5px rgba(0, 0, 0, 0.1); margin-top: 0.5rem; max-height: 400px; overflow-y: auto; padding: 0.4rem;">
        </div>
    </div>

    <!-- Clock -->
    <div style="flex: 1; background: linear-gradient(135deg, #1e293b, #334155); color: white; padding: 0.75rem 1.25rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; justify-content: center; align-items: center; gap: 1rem;">
        <i class="ph-bold ph-clock" style="font-size: 1.75rem; color: #f59e0b;"></i>
        <div style="display: flex; flex-direction: column; justify-content: center;">
            <div id="realTimeClock" style="font-size: 1.25rem; font-weight: 800; font-family: 'Inter', sans-serif; letter-spacing: 0.2px; line-height: 1;">-- --, ---- --:--:-- --</div>
            <div style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; font-weight: 700; margin-top: 2px;">University Reference Time (PST)</div>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const dateOptions = { month: 'long', day: 'numeric', year: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        
        const dateStr = now.toLocaleDateString('en-US', dateOptions);
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);
        
        const clockElement = document.getElementById('realTimeClock');
        if (clockElement) {
            clockElement.innerText = `${dateStr} at ${timeStr}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>

@php 
    $ldState = \Illuminate\Support\Facades\Cache::get('system_lockdown', ['active' => false, 'reason' => 'N/A']);
    if (!is_array($ldState)) {
        $ldState = ['active' => (bool)$ldState, 'reason' => 'N/A'];
    }
@endphp

<div id="lockdownBanner" style="{{ $ldState['active'] ? 'display: flex;' : 'display: none;' }} background: #dc2626; color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; align-items: center; gap: 1.5rem; animation: lockdown-pulse 2s infinite; box-shadow: 0 10px 30px -5px rgba(220, 38, 38, 0.5);">
     <i class="ph-fill ph-warning-octagon" style="font-size: 3rem;"></i>
     <div>
         <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800;">EMERGENCY LOCKDOWN ACTIVE</h2>
         <p style="margin: 0; font-size: 1rem; font-weight: 700; background: rgba(0,0,0,0.1); padding: 0.25rem 0.5rem; border-radius: 4px; display: inline-block; margin: 0.5rem 0;">REASON: <span id="lockdownReasonLabel">{{ $ldState['reason'] }}</span></p>
         <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">All vehicle entry and exit is strictly prohibited. Please secure the gate manually.</p>
     </div>
</div>

<audio id="lockdownAlarm" src="https://www.soundjay.com/buttons/sounds/beep-01a.mp3" preload="auto"></audio>
<audio id="blacklistAlarm" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto"></audio>
<audio id="expiredAlert" src="https://www.soundjay.com/communication/sounds/pager-beep-1.mp3" preload="auto"></audio>

<style>
    @keyframes lockdown-pulse {
        0% { transform: scale(1); box-shadow: 0 10px 30px -5px rgba(220, 38, 38, 0.5); }
        50% { transform: scale(1.01); box-shadow: 0 20px 50px -5px rgba(220, 38, 38, 0.8); }
        100% { transform: scale(1); box-shadow: 0 10px 30px -5px rgba(220, 38, 38, 0.5); }
    }
</style>

<!-- Overstaying Security Alert -->
@if($overstaying->count() > 0)
<div class="no-print" style="background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem; animation: slideIn 0.3s ease;">
    <div style="background: #ef4444; color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <i class="ph-fill ph-shield-warning" style="font-size: 1.75rem;"></i>
    </div>
    <div style="flex: 1;">
        <h4 style="margin: 0; color: #991b1b; font-weight: 800; font-size: 1rem;">SECURITY ALERT: OVERSTAYING VEHICLES ({{ $overstaying->count() }})</h4>
        <p style="margin: 0.25rem 0 0 0; color: #b91c1c; font-size: 0.9rem; font-weight: 600;">
            Detected {{ $overstaying->count() }} vehicle(s) inside for over 12 hours. Check logs for potential abandoned or overnight vehicles.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        @foreach($overstaying->take(5) as $o)
            <span class="badge overstaying-badge clickable-plate" 
                  title="Click to view details"
                  style="background: #ef4444; color: white; border: none; cursor: pointer; transition: transform 0.2s;" 
                  data-query="{{ $o->vehicleRegistration->plate_number ?? '' }}">
                {{ $o->vehicleRegistration->plate_number ?? $o->rfid_tag_id }}
            </span>
        @endforeach
        @if($overstaying->count() > 5)
            <span style="font-size: 0.8rem; color: #ef4444; font-weight: 800;">+{{ $overstaying->count() - 5 }} More</span>
        @endif
    </div>
</div>
@endif

<!-- Occupancy Tracker & Hourly Trend (Grid) -->
<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Left: Occupancy Tracker -->
    <div class="table-container" style="padding: 1.5rem; background: white; border-radius: 12px; border: 1px solid #e2e8f0; height: 100%; display: flex; flex-direction: column; justify-content: center;">
        @php
            $warningThreshold = (int)\App\Models\SystemSetting::get('occupancy_warning_threshold', 90);
            $percent = ($stats['occupancy'] / $stats['total_capacity']) * 100;
            $barColor = $percent >= $warningThreshold ? '#dc2626' : ($percent >= 70 ? '#f59e0b' : '#10b981');
        @endphp
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 750; color: #1e293b;"><i class="ph-bold ph-buildings"></i> Current Campus Occupancy</h3>
            <span id="occupancyText" style="font-weight: 800; color: {{ $barColor }}; font-size: 1.1rem;">
                <span id="occCount" style="font-size: 1.3rem;">{{ $stats['occupancy'] }}</span> / {{ $stats['total_capacity'] }}
            </span>
        </div>
        <div style="width: 100%; height: 20px; background: #f1f5f9; border-radius: 99px; overflow: hidden; border: 1px solid #e2e8f0; margin: 0.5rem 0;">
            <div id="occupancyBar" style="width: {{ $percent }}%; height: 100%; background: {{ $barColor }}; transition: width 0.5s ease; box-shadow: 0 0 10px {{ $barColor }}44;"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.85rem; font-weight: 650;">
            <span style="{{ $percent >= $warningThreshold ? 'color: #dc2626;' : 'color: #64748b;' }}"><i class="ph ph-activity"></i> Daily Entry/Exit Status</span>
            <span id="occPercent">{{ round($percent) }}% Capacity Used</span>
        </div>
    </div>

    <!-- Right: Hourly Traffic Trend Chart -->
    <div class="table-container" style="padding: 1.25rem; background: white; border-radius: 12px; border: 1px solid #e2e8f0; height: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 750; color: #1e293b;"><i class="ph-bold ph-chart-line"></i> Traffic Trend (Last 12 Hours)</h3>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">Real-time Analytics Flow</span>
        </div>
        <div style="height: 140px; width: 100%;">
            <canvas id="trafficTrendChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trafficTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($hourlyTrends['labels']),
                datasets: [
                    {
                        label: 'Entries',
                        data: @json($hourlyTrends['entries']),
                        borderColor: '#10b981',
                        backgroundColor: '#10b98122',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3
                    },
                    {
                        label: 'Exits',
                        data: @json($hourlyTrends['exits']),
                        borderColor: '#ef4444',
                        backgroundColor: '#ef444422',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: true, grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { display: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } }
                }
            }
        });
    });
</script>

<!-- Guard Dashboard Ticker -->
<div class="ticker-wrapper" style="position: fixed; bottom: 0; left: 0; right: 0; background: #741b1b; color: white; padding: 0.6rem 0; overflow: hidden; z-index: 1000; border-top: 2px solid #f59e0b; box-shadow: 0 -5px 15px rgba(0,0,0,0.2);">
    <marquee id="guardTickerMarquee" behavior="scroll" direction="left" scrollamount="6" style="font-weight: 800; font-size: 0.95rem; text-transform: uppercase;">
        <i class="ph-fill ph-megaphone" style="margin: 0 1rem; color: #f59e0b;"></i>
        <span id="tickerContent">{{ $stats['guard_ticker'] }}</span>
        <i class="ph-fill ph-megaphone" style="margin: 0 1rem; color: #f59e0b;"></i>
    </marquee>
</div>

<!-- Hardware Connection Header -->
<div class="tag-scanner-box mb-8" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div id="statusIcon" style="font-size: 1.5rem;"><i class="ph ph-broadcast text-gray-400"></i></div>
        <div>
            <div id="statusText" class="text-sm font-semibold text-gray-700">{{ __('messages.scanner_status') }}</div>
            <div id="scannerSubtext" class="text-xs text-gray-500">{{ __('messages.scanner_hint') }}</div>
        </div>

    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <!-- Manual Plate Override (Virtual Scan) -->
        <div style="display: flex; align-items: center; gap: 0.5rem; background: #f1f5f9; padding: 0.4rem 0.75rem; border-radius: 8px; border: 1px solid #cbd5e1; margin-right: 1rem;">
             <i class="ph ph-keyboard" style="color: #475569;"></i>
             <input type="text" id="manualPlateInput" placeholder="Plate Number" 
                 style="background: transparent; border: none; outline: none; font-weight: 700; width: 120px; font-size: 0.9rem; text-transform: uppercase;">
             <button type="button" id="btnVirtualScan" class="btn btn-primary" style="padding: 0.3rem 0.8rem; font-size: 0.75rem;">Process</button>
        </div>

        <!-- TTS Toggle Switch -->
        <div style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 0.5rem 1rem; border-radius: 99px; border: 1px solid #e2e8f0; margin-right: 1.5rem;">
            <i class="ph ph-speaker-high" style="font-size: 1.25rem; color: #64748b;"></i>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Voice Alert</span>
                <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 22px;">
                    <input type="checkbox" id="toggleVoice" checked style="opacity: 0; width: 0; height: 0;">
                    <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px;"></span>
                </label>
            </div>
        </div>

        <button type="button" id="btnConnectHardware" class="btn btn-outline" style="gap: 0.5rem;">
            <i class="ph ph-plugs"></i> <span>Connect Reader</span>
        </button>
        <button type="button" id="btnStopBridge" class="btn btn-outline" style="gap: 0.5rem; color: #ef4444; border-color: #fca5a5; display: none;">
            <i class="ph ph-power"></i> <span>Kill Bridge</span>
        </button>
    </div>
</div>

<style>
    .switch input:checked + .slider { background-color: #741b1b; }
    .switch input:focus + .slider { box-shadow: 0 0 1px #741b1b; }
    .switch input:checked + .slider:before { transform: translateX(20px); }
    .slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
        background-color: white; transition: .4s; border-radius: 50%;
    }
</style>

<!-- Summary Cards -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-label">{{ __('messages.total_entries') }}</div>
        <div class="stat-value" id="countEntries">{{ $stats['entries_today'] }}</div>
        <i class="ph ph-arrow-circle-down-left stat-icon"></i>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">{{ __('messages.total_exits') }}</div>
        <div class="stat-value" id="countExits">{{ $stats['exits_today'] }}</div>
        <i class="ph ph-arrow-circle-up-right stat-icon"></i>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">{{ __('messages.active_users') }}</div>
        <div class="stat-value">{{ $stats['visitors_inside'] }}</div>
        <i class="ph ph-users-three stat-icon"></i>
    </div>

    
    <div id="systemStatusCard" class="stat-card" style="transition: all 0.5s ease;">
        <div class="stat-label">System Status</div>
        <div id="systemStatusValue" class="stat-value text-success" style="font-size: 1.5rem;">ACTIVE</div>
        <i id="systemStatusIcon" class="ph ph-check-circle stat-icon text-success" style="opacity: 0.2"></i>
    </div>
</div>

<!-- Real-time Activity Logs -->
<div class="table-container">
    <div class="section-header" style="justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
        <h3 style="margin: 0;"><i class="ph-bold ph-clock-counter-clockwise"></i> {{ __('messages.recent_activity') }}</h3>
        
        <!-- Standardized Local Search -->
        <div style="flex-grow: 1; max-width: 400px; position: relative;" class="search-box-wrapper no-print">
            <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input type="text" id="logSearchInput" placeholder="Filter logs (Name, Plate, ID)..." 
                style="width: 100%; padding: 0.7rem 1rem 0.7rem 2.8rem; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;" class="no-print">
            <a href="{{ route('guard.entry') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-plus"></i> {{ __('messages.manual_entry') }}
            </a>
        </div>
    </div>


    <div class="table-wrapper">
        <table id="logsTable">
            <thead>
                <tr>
                    <th>{{ __('messages.field_timestamp') }}</th>
                    <th>{{ __('messages.field_vehicle') }}</th>
                    <th>{{ __('messages.field_owner') }}</th>
                    <th>{{ __('messages.field_plate') }}</th>
                    <th>{{ __('messages.field_type') }}</th>
                    <th>{{ __('messages.field_action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentLogs as $log)
                <tr data-plates="{{ $log->vehicleRegistration->plate_number ?? '' }}" data-ids="{{ $log->vehicleRegistration->university_id ?? '' }}">
                    <td>{{ $log->timestamp->format('h:i:s A') }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="ph ph-car" style="font-size: 1.25rem;"></i>
                            <div>
                                <div style="font-weight: 600;">{{ ($log->vehicleRegistration->make_brand ?? 'Unknown') . ' ' . ($log->vehicleRegistration->model_name ?? '') }}</div>
                                <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">{{ $log->vehicleRegistration->vehicle_type ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $log->vehicleRegistration->full_name ?? 'N/A' }}</td>
                    <td><span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0;">{{ $log->vehicleRegistration->plate_number ?? 'N/A' }}</span></td>
                    <td>
                        @if($log->type == 'entry')
                            <span class="badge badge-toggle" data-id="{{ $log->id }}" style="background: #ecfdf5; color: #059669; cursor: pointer;">ENTRY</span>
                        @else
                            <span class="badge badge-toggle" data-id="{{ $log->id }}" style="background: #fef2f2; color: #dc2626; cursor: pointer;">EXIT</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-outline btn-view-details" data-id="{{ $log->id }}" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">View Details</button>
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="6" style="text-align: center; padding: 2rem; color: #94a3b8;">No recent activity detected.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Manual Visitors Inside -->
<div class="table-container mt-8">
    <div class="section-header">
        <h3>Manual Visitors Inside</h3>
        <a href="{{ route('guard.exit') }}" class="btn btn-outline">
            View All Entries
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Visitor Name</th>
                    <th>Plate Number</th>
                    <th>Time In</th>
                    <th>Purpose</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visitors_inside as $visitor)
                <tr>
                    <td style="font-weight: 600;">{{ $visitor->name }}</td>
                    <td><span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0;">{{ $visitor->plate ?? 'N/A' }}</span></td>
                    <td>{{ $visitor->time_in->format('h:i A') }}</td>
                    <td>{{ $visitor->purpose }}</td>
                    <td style="text-align: right;">
                        <form action="{{ route('guard.visitor.exit.process', $visitor->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="background: #ef4444; border: none; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                Log Exit
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">No manual visitors currently inside.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>



<!-- Vehicle Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header" style="background: #741b1b; color: white; border-radius: 16px 16px 0 0; border: none; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" id="logDetailsModalLabel" style="font-weight: 800; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph-bold ph-info"></i> Activity Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div id="modalLoading" class="text-center py-5">
                    <i class="ph-bold ph-spinner-gap animate-spin" style="font-size: 3rem; color: #741b1b;"></i>
                    <p style="margin-top: 1rem; color: #64748b; font-weight: 600;">Fetching data...</p>
                </div>
                <div id="modalContent" style="display: none;">
                    <div style="background: #f8fafc; padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 0.25rem;">Vehicle Owner</div>
                        <div id="detailOwner" style="font-size: 1.2rem; font-weight: 800; color: #1e293b;">-</div>
                        <div id="detailTag" style="font-size: 0.9rem; font-weight: 600; color: #741b1b;">Tag: -</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Plate Number</div>
                            <div id="detailPlate" style="font-weight: 800; color: #1e293b;">-</div>
                        </div>
                        <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Log Type</div>
                            <div id="detailType" aria-label="Status Badge">-</div>
                        </div>
                    </div>

                    <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i class="ph ph-car" style="color: #64748b; font-size: 1.25rem;"></i>
                            <div>
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Vehicle Details</div>
                                <div id="detailVehicle" style="font-weight: 700; color: #1e293b;">-</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="ph ph-clock" style="color: #64748b; font-size: 1.25rem;"></i>
                            <div>
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Timestamp</div>
                                <div id="detailTimestamp" style="font-weight: 700; color: #1e293b;">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border: none; padding: 1rem 2rem 2rem;">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal" style="border-radius: 10px; padding: 0.75rem; font-weight: 700; background: #f1f5f9; color: #475569; border: none;">Close Details</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnConnect = document.getElementById('btnConnectHardware');
        const btnStop = document.getElementById('btnStopBridge');
        const toggleVoice = document.getElementById('toggleVoice');
        const statusText = document.getElementById('statusText');
        const statusIcon = document.getElementById('statusIcon');
        const scannerSubtext = document.getElementById('scannerSubtext');
        const logsTable = document.getElementById('logsTable').getElementsByTagName('tbody')[0];
        
        // --- TTS Engine ---
        let voiceEnabled = localStorage.getItem('guard_voice_alerts') !== 'false';
        toggleVoice.checked = voiceEnabled;
        toggleVoice.onchange = (e) => {
            voiceEnabled = e.target.checked;
            localStorage.setItem('guard_voice_alerts', voiceEnabled);
            if (voiceEnabled) announce("Voice announcements enabled.");
        };

        function announce(text, style = 'normal') {
            if (!voiceEnabled) return;
            const synth = window.speechSynthesis;
            const utterance = new SpeechSynthesisUtterance(text);
            
            const voices = synth.getVoices();
            const maleVoice = voices.find(v => 
                v.name.includes('Google US English Male') || 
                v.name.includes('Microsoft David') || 
                v.name.toLowerCase().includes('male')
            ) || voices.find(v => v.name.includes('Google') || v.name.includes('Natural')) || voices[0];

            utterance.voice = maleVoice;
            utterance.rate = 1.0; 
            utterance.pitch = 0.9;

            if (style === 'urgent') { // Blacklisted
                console.log("Triggering Siren (Urgent)");
                synth.cancel(); 
                const alarm = document.getElementById('blacklistAlarm');
                if (alarm) { alarm.currentTime = 0; alarm.play().catch(e => console.error("Siren Play Error:", e)); }
            } else if (style === 'warning') { // Expired
                console.log("Triggering Buzzer (Warning)");
                synth.cancel();
                const buzzer = document.getElementById('expiredAlert');
                if (buzzer) { buzzer.currentTime = 0; buzzer.play().catch(e => console.error("Buzzer Play Error:", e)); }
            }
            
            synth.speak(utterance);
        }

        /**
         * Optimized Voice Alert: [Plate Char-by-Char], [Last Name], [Status]
         */
        function speakScan(plate, fullName, status) {
            if (!voiceEnabled) return;
            
            // 1. Format Plate: Read character by character by adding spaces
            const formattedPlate = (plate || '').toUpperCase().split('').join(' ');
            
            // 2. Concise Last Name: Get last word of full_name
            const lastName = fullName ? fullName.trim().split(' ').pop() : 'User';
            
            // 3. Normalized Status
            const statusLabel = status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
            
            // 4. Construct Speech Text with pauses (commas)
            const speechText = `${formattedPlate}, ${lastName}, ${statusLabel}`;
            
            console.log("Voice Announcement:", speechText);
            announce(speechText);
        }
        
        let bridgeSocket = null;
        let isConnected = false;

        function updateUIStatus(status) {
            if (status === 'connected') {
                isConnected = true;
                statusText.innerText = 'Live Scanner Online';
                statusIcon.innerHTML = '<span class="live-badge"><span class="pulse-dot"></span>LIVE</span>';
                scannerSubtext.innerText = 'Live hardware monitoring active — broadcasting to all portals.';
                btnConnect.innerHTML = '<i class="ph ph-plugs-connected"></i> Disconnect Reader';
                btnConnect.classList.remove('btn-outline');
                btnConnect.classList.add('btn-primary');
            } else if (status === 'connecting') {
                statusText.innerText = 'Connecting...';
                statusIcon.innerHTML = '<i class="ph ph-spinner-gap animate-spin" style="color:#f59e0b;font-size:1.5rem;"></i>';
                btnConnect.disabled = true;
            } else {
                isConnected = false;
                statusText.innerText = 'Scanner Offline';
                statusIcon.innerHTML = '<i class="ph ph-broadcast" style="color:#94a3b8;font-size:1.5rem;"></i>';
                scannerSubtext.innerText = 'Connect to hardware bridge to begin monitoring.';
                btnConnect.innerHTML = '<i class="ph ph-plugs"></i> Connect Reader';
                btnConnect.classList.remove('btn-primary');
                btnConnect.classList.add('btn-outline');
                btnConnect.disabled = false;
                btnStop.style.display = 'none';
            }
        }

        async function checkBridgeStatus() {
            try {
                const res = await fetch('{{ route('bridge.status') }}');
                const data = await res.json();
                if (data.online) {
                    btnStop.style.display = 'flex';
                } else {
                    btnStop.style.display = 'none';
                    // Only force offline if we are NOT currently holding an active WebSocket connection
                    if (isConnected && (!bridgeSocket || bridgeSocket.readyState !== WebSocket.OPEN)) {
                        updateUIStatus('offline');
                    }
                }
            } catch (e) {}
        }
        setInterval(checkBridgeStatus, 5000);
        checkBridgeStatus();

        btnStop.onclick = async function() {
            const res = await fetch('{{ route('bridge.stop') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
            const data = await res.json();
            if(data.success) {
                if (bridgeSocket) bridgeSocket.close();
                updateUIStatus('offline');
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Bridge Process Terminated', showConfirmButton: false, timer: 2000 });
            }
        };

        btnConnect.addEventListener('click', async function() {

            if (isConnected) {
                bridgeSocket.close();
                return;
            }

            updateUIStatus('connecting');

            // Step 1 — auto-launch bridge_service.py via Laravel if not already running
            try {
                const res  = await fetch('{{ route("bridge.start") }}');
                const data = await res.json();
                if (data.status === 'error') {
                    updateUIStatus('offline');
                    Swal.fire({
                        icon: 'error',
                        title: 'Bridge Script Missing',
                        text: data.message,
                        confirmButtonColor: '#1e293b'
                    });
                    return;
                }
            } catch (e) {
                console.warn('Bridge start check failed, attempting direct connect:', e);
            }

            // Step 2 — open WebSocket (bridge v3.0 broadcasts to all clients universally)
            bridgeSocket = new WebSocket('ws://127.0.0.1:8080');

            bridgeSocket.onopen = function() {
                updateUIStatus('connected');
            };

            bridgeSocket.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    if (data.tagId && data.status === 'scanned') {
                        processTag(data.tagId);
                    }
                } catch (e) { console.error('Bridge error', e); }
            };

            bridgeSocket.onclose = function() {
                updateUIStatus('offline');
            };

            bridgeSocket.onerror = function() {
                updateUIStatus('offline');
                Swal.fire({
                    icon: 'error',
                    title: 'Bridge Not Responding',
                    text: 'Could not reach bridge_service.py. It may still be starting — try clicking Connect Reader again in a moment.',
                    confirmButtonColor: '#1e293b'
                });
            };
        });

        // --- Manual Override (Virtual Scan) ---
        const btnVirtualScan = document.getElementById('btnVirtualScan');
        const manualPlateInput = document.getElementById('manualPlateInput');

        btnVirtualScan.addEventListener('click', async function() {
            const plate = manualPlateInput.value.trim();
            if (!plate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Plate Required',
                    text: 'Please enter a plate number.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            btnVirtualScan.disabled = true;
            btnVirtualScan.innerText = '...';

            try {
                const response = await fetch("{{ route('guard.virtual.scan') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ plate_number: plate })
                });

                const result = await response.json();
                
                if (result.success) {
                    addLogRow(result.log);
                    updateCounters(result.log.type, result.occupancy);
                    manualPlateInput.value = '';
                    
                    const fullName = result.log.vehicle_registration?.full_name || 'User';
                    const plateVal = result.log.vehicle_registration?.plate_number || plate;
                    speakScan(plateVal, fullName, result.log.type);
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Virtual Scan Failed',
                        text: result.message,
                        confirmButtonColor: '#1e293b'
                    });
                }
            } catch (e) {
                console.error('Virtual scan error', e);
            } finally {
                btnVirtualScan.disabled = false;
                btnVirtualScan.innerText = 'Process';
            }
        });

        // Add Enter key support for plate input
        manualPlateInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                btnVirtualScan.click();
            }
        });

        // Local Table Search Logic
        const logSearchInput = document.getElementById('logSearchInput');
        logSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = logsTable.querySelectorAll('tr');
            
            rows.forEach(row => {
                if(row.id === 'emptyRow') return;
                const text = row.innerText.toLowerCase();
                const plates = row.getAttribute('data-plates') || '';
                const ids = row.getAttribute('data-ids') || '';
                
                if (text.includes(query) || plates.toLowerCase().includes(query) || ids.toLowerCase().includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        let lastProcessedTags = {}; // Store last seen time for each tag

        async function processTag(tagId) {
            // 0. Scanner Awareness: If the user is currently focused on an input field (like a search bar),
            // fill that field instead of performing a system-wide log override.
            const activeEl = document.activeElement;
            if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && !activeEl.readOnly) {
                activeEl.value = tagId;
                activeEl.dispatchEvent(new Event('input'));
                
                // Show a subtle visual cue that tag was captured into field
                activeEl.style.borderColor = '#10b981';
                setTimeout(() => activeEl.style.borderColor = '', 1000);
                
                announce("ID Captured.");
                return; // Exit without logging
            }

            // 1. Protection against modal spam: Don't trigger if a popup is already visible (only for unregistered tags)
            if (Swal.isVisible() && !Swal.isTimerRunning()) {
                 console.log("Ignoring scan - SweetAlert is currently visible");
                 return;
            }

            // 2. Cooldown: Don't process the same tag ID too quickly (within 5 seconds)
            const now = Date.now();
            if (lastProcessedTags[tagId] && (now - lastProcessedTags[tagId]) < 5000) {
                console.log("Ignoring repeat scan - Cooldown active for " + tagId);
                return;
            }

            lastProcessedTags[tagId] = now;
            scannerSubtext.innerHTML = `Last detected: <b style="color:var(--bg-sidebar)">${tagId}</b>`;

            // First lookup the tag
            try {
                const response = await fetch(`{{ url('guard/lookup-tag') }}?tagId=${tagId}`);
                const result = await response.json();

                if (result.success) {
                    // AUTO-PILOT: Registered tag found, log immediately without modal
                    const vehicle = result.data;
                    const action = result.suggested_action;
                    
                    logVehicle(tagId, action, vehicle);
                } else {
                    // Trigger Audio for Unregistered Tag
                    announce("Unregistered Tag detected. Please check documents.", true);

                    // EXCEPTIONS: Only show popup for unregistered tags (Visitors)
                    Swal.fire({
                        icon: 'warning',
                        title: 'Unregistered Tag',
                        text: `The tag ID ${tagId} is not registered in the system.`,
                        footer: '<span style="color: #64748b; font-size: 0.8rem;">Register this vehicle or log as visitor.</span>',
                        confirmButtonText: 'Manual Entry',
                        showCancelButton: true,
                        confirmButtonColor: '#1e293b'
                    }).then((r) => {
                        if (r.isConfirmed) {
                             window.location.href = "{{ route('guard.entry') }}?tagId=" + tagId;
                        }
                    });
                }
            } catch (e) {
                console.error('Processing error', e);
            }
        }

        async function logVehicle(tagId, type, vehicleInfo = null) {
            try {
                const response = await fetch("{{ route('guard.log.vehicle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ tagId, type })
                });
                
                const result = await response.json();
                if (result.success) {
                    addLogRow(result.log);
                    updateCounters(type, result.occupancy);
                    
                    // Instant UI Feedback via Toast (Top-Right)
                    const title = type === 'entry' ? 'Entry Logged' : 'Exit Logged';
                    const name = vehicleInfo ? vehicleInfo.full_name : (result.log.vehicle_registration?.full_name || 'User');
                    const plate = vehicleInfo ? vehicleInfo.plate_number : (result.log.vehicle_registration?.plate_number || '');
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `${title}: ${name} (${plate})`,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });

                    // Trigger Optimized Voice
                    speakScan(plate, name, type);
                } else if (result.lockdown) {
                    // CRITICAL LOCKDOWN ALERT
                    Swal.fire({
                        icon: 'error',
                        title: 'SYSTEM LOCKDOWN',
                        text: result.message,
                        background: '#741b1b',
                        color: '#ffffff',
                        confirmButtonColor: '#dc2626'
                    });
                } else if (result.blacklisted) {
                    // ████ BLACKLIST ALERT — Hard Denial ████
                    const statusCard = document.getElementById('systemStatusCard');
                    const statusVal = document.getElementById('systemStatusValue');
                    const statusIco = document.getElementById('systemStatusIcon');

                    if (statusCard) {
                        statusCard.style.background = '#7f1d1d';
                        statusCard.style.borderColor = '#991b1b';
                        statusCard.style.color = '#fff';
                        statusVal.innerHTML = '⚠ BLACKLISTED';
                        statusVal.className = 'stat-value';
                        statusVal.style.color = '#fca5a5';
                        statusIco.className = 'ph ph-prohibit stat-icon';
                        statusIco.style.color = '#fca5a5';

                        setTimeout(() => {
                            statusCard.style.background = '';
                            statusCard.style.borderColor = '';
                            statusCard.style.color = '';
                            statusVal.innerText = 'ACTIVE';
                            statusVal.className = 'stat-value text-success';
                            statusVal.style.color = '';
                            statusIco.className = 'ph ph-check-circle stat-icon text-success';
                            statusIco.style.color = '';
                        }, 8000);
                    }

                    // Play blacklist alarm (repeat 3×)
                    const blAlarm = document.getElementById('blacklistAlarm');
                    if (blAlarm) {
                        let plays = 0;
                        const playAlarm = () => { if (plays++ < 3) { blAlarm.currentTime = 0; blAlarm.play().catch(() => {}); } };
                        playAlarm();
                        blAlarm.onended = playAlarm;
                    }

                    // Urgent voice announcement
                    announce(`Warning! ${result.owner} is blacklisted. Do not allow entry. Detain immediately.`, 'urgent');

                    Swal.fire({
                        icon: 'error',
                        title: '🚫 BLACKLISTED VEHICLE',
                        html: `
                            <div style="background:#7f1d1d;color:#fff;padding:1rem;border-radius:12px;margin-bottom:1rem;">
                                <strong style="font-size:1.25rem;">${result.owner}</strong><br>
                                <span style="font-family:monospace;font-size:1.1rem;background:#991b1b;padding:2px 8px;border-radius:4px;">${result.plate}</span>
                            </div>
                            <p style="color:#dc2626;font-weight:700;">${result.message}</p>
                            <p style="font-size:0.85rem;color:#64748b;">This vehicle is flagged in the system. Do NOT allow access. Alert security if needed.</p>
                        `,
                        background: '#fff1f2',
                        confirmButtonColor: '#7f1d1d',
                        confirmButtonText: '✓ Acknowledged — Deny Entry',
                        showCancelButton: false,
                        allowOutsideClick: false,
                    }).then(() => {
                        if (blAlarm) { blAlarm.pause(); blAlarm.onended = null; }
                    });

                } else if (result.expired) {
                    // RED ALERT: Expiry Access Denied
                    const statusCard = document.getElementById('systemStatusCard');
                    const statusVal = document.getElementById('systemStatusValue');
                    const statusIcon = document.getElementById('systemStatusIcon');
                    
                    if (statusCard) {
                        statusCard.style.background = '#fef2f2';
                        statusCard.style.borderColor = '#ef4444';
                        statusVal.innerHTML = 'DENIED: EXPIRED TAG';
                        statusVal.className = 'stat-value text-danger';
                        statusIcon.className = 'ph ph-x-circle stat-icon text-danger';
                        
                        setTimeout(() => {
                            statusCard.style.background = '';
                            statusCard.style.borderColor = '';
                            statusVal.innerText = 'ACTIVE';
                            statusVal.className = 'stat-value text-success';
                            statusIcon.className = 'ph ph-check-circle stat-icon text-success';
                        }, 5000);
                    }

                    // Trigger Siren & MESO Renewal Voice Alert
                    const plate = result.plate || '';
                    announce(`Warning! Expired Tag detected is blacklisted. ${plate} denied entry. Please proceed to MESO, Maintenance and Engineering Services Office, for the renewal of your tag.`, 'urgent');

                    Swal.fire({
                        icon: 'error',
                        title: 'ACCESS DENIED',
                        html: `
                            <p style="font-weight: 700; color: #dc2626; margin-bottom: 0.5rem;">${result.message}</p>
                            <p style="font-size: 0.9rem; color: #64748b;">Please advise the owner to visit the <strong>MESO (Maintenance and Engineering Services Office)</strong> for tag renewal.</p>
                        `,
                        background: '#fee2e2',
                        confirmButtonColor: '#dc2626'
                    });
                }
            } catch (e) { console.error('Logging error', e); }
        }

        function addLogRow(log) {
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            const plates = log.vehicle_registration?.plate_number || log.vehicle?.plate_number || '';
            const univId = log.vehicle_registration?.university_id || '';
            const timestamp = new Date(log.timestamp);
            const timeStr = timestamp.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

            const row = document.createElement('tr');
            row.setAttribute('data-plates', plates);
            row.setAttribute('data-ids', univId);

            const badgeType = log.type === 'entry' 
                ? `<span class="badge badge-toggle" data-id="${log.id}" style="background: #ecfdf5; color: #059669; cursor: pointer;">ENTRY</span>`
                : `<span class="badge badge-toggle" data-id="${log.id}" style="background: #fef2f2; color: #dc2626; cursor: pointer;">EXIT</span>`;

            row.innerHTML = `
                <td>${timeStr}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="ph ph-car" style="font-size: 1.25rem;"></i>
                        <div>
                            <div style="font-weight: 600;">${(log.vehicle_registration?.make_brand || 'Unknown')} ${(log.vehicle_registration?.model_name || '')}</div>
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">${(log.vehicle_registration?.vehicle_type || 'N/A')}</div>
                        </div>
                    </div>
                </td>
                <td>${log.vehicle_registration?.full_name || 'N/A'}</td>
                <td><span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0;">${plates || 'N/A'}</span></td>
                <td>${badgeType}</td>
                <td><button class="btn btn-outline btn-view-details" data-id="${log.id}" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">View Details</button></td>
            `;
            
            logsTable.prepend(row);
            
            // Highlight animation
            row.style.animation = 'highlight 1s ease-out';
        }


        function updateCounters(type, newOccupancy = null) {
            const counter = type === 'entry' ? document.getElementById('countEntries') : document.getElementById('countExits');
            if (counter) {
                counter.innerText = parseInt(counter.innerText) + 1;
            }

            if (newOccupancy !== null) {
                const total = {{ $stats['total_capacity'] }};
                const occCount = document.getElementById('occCount');
                const occPercent = document.getElementById('occPercent');
                const occBar = document.getElementById('occupancyBar');

                occCount.innerText = newOccupancy;
                const percent = Math.round((newOccupancy / total) * 100);
                occPercent.innerText = `${percent}% Capacity Used`;
                occBar.style.width = `${percent}%`;

                // Update Bar Color
                if (percent > 90) occBar.style.background = '#ef4444';
                else if (percent > 70) occBar.style.background = '#f59e0b';
                else occBar.style.background = '#10b981';
            }
        }

        // Manual Correction: Click badge to toggle Entry/Exit
        logsTable.addEventListener('click', function(e) {
            const badge = e.target.closest('.badge-toggle');
            if (badge) {
                const logId = badge.getAttribute('data-id');
                if (logId) {
                    toggleLogType(logId, badge);
                }
            }
        });
        
        async function toggleLogType(id, badgeElement) {
            try {
                const response = await fetch(`{{ url('guard/log-vehicle') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    const newType = result.new_type;
                    
                    // Update the badge UI
                    if (newType === 'entry') {
                        badgeElement.style.background = '#ecfdf5';
                        badgeElement.style.color = '#059669';
                        badgeElement.innerText = 'ENTRY';
                        
                        // Increment Entry counter, decrement Exit counter
                        document.getElementById('countEntries').innerText = parseInt(document.getElementById('countEntries').innerText) + 1;
                        document.getElementById('countExits').innerText = Math.max(0, parseInt(document.getElementById('countExits').innerText) - 1);
                    } else {
                        badgeElement.style.background = '#fef2f2';
                        badgeElement.style.color = '#dc2626';
                        badgeElement.innerText = 'EXIT';
                        
                        // Increment Exit counter, decrement Entry counter
                        document.getElementById('countExits').innerText = parseInt(document.getElementById('countExits').innerText) + 1;
                        document.getElementById('countEntries').innerText = Math.max(0, parseInt(document.getElementById('countEntries').innerText) - 1);
                    }
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Status Updated',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } catch (e) {
                console.error('Toggle error', e);
            }
        }

        // --- Global Search Logic ---
        const globalSearchInput = document.getElementById('globalSearchInput');
        const searchResultsDropdown = document.getElementById('searchResultsDropdown');
        const searchLoader = document.getElementById('searchLoader');
        let searchTimeout;

        if (globalSearchInput) {
            globalSearchInput.addEventListener('input', function() {
                const query = this.value.trim();
                if (query.length < 2) {
                    searchResultsDropdown.style.display = 'none';
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(async () => {
                    searchLoader.style.display = 'block';
                    try {
                        const response = await fetch(`{{ route('guard.search') }}?query=${encodeURIComponent(query)}`);
                        const results = await response.json();
                        
                        if (results.length > 0) {
                            searchResultsDropdown.innerHTML = results.map(item => `
                                <div class="search-result-item" onclick='showOwnerDetails(${JSON.stringify(item).replace(/'/g, "&#39;")})' style="padding: 1rem; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div>
                                            <div style="font-weight: 700; color: #1e293b;">${item.full_name}</div>
                                            <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">${item.college_dept || item.office || 'Department N/A'}</div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 800; color: #741b1b; font-size: 0.9rem;">${item.plate_number || 'No Plate'}</div>
                                            <div style="font-size: 0.75rem; color: #94a3b8;">${item.vehicle_type || ''}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                            searchResultsDropdown.style.display = 'block';
                        } else {
                            searchResultsDropdown.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: #94a3b8;">No matching records found.</div>';
                            searchResultsDropdown.style.display = 'block';
                        }
                    } catch (e) {
                        console.error('Search error', e);
                    } finally {
                        searchLoader.style.display = 'none';
                    }
                }, 300);
            });
        }

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (globalSearchInput && !globalSearchInput.contains(e.target) && searchResultsDropdown && !searchResultsDropdown.contains(e.target)) {
                searchResultsDropdown.style.display = 'none';
            }
        });

        // Handle overstaying badge clicks
        document.addEventListener('click', function(e) {
            const plateBtn = e.target.closest('.clickable-plate');
            if (plateBtn) {
                const query = plateBtn.getAttribute('data-query');
                globalSearchInput.value = query;
                const event = new Event('input', { bubbles: true });
                globalSearchInput.dispatchEvent(event);
                globalSearchInput.focus();
                
                // Scroll to top to ensure search is visible
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        window.showOwnerDetails = (item) => {
            searchResultsDropdown.style.display = 'none';
            Swal.fire({
                title: '<div style="font-size: 1.25rem; font-weight: 800; color: #1e293b;">Vehicle Owner Profile</div>',
                html: `
                    <div style="text-align: left; padding: 0.5rem;">
                        <div style="background: #f8fafc; padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 0.25rem;">Owner Name</div>
                            <div style="font-size: 1.2rem; font-weight: 800; color: #1e293b;">${item.full_name}</div>
                            <div style="font-size: 0.9rem; font-weight: 600; color: #741b1b;">ID: ${item.university_id || 'N/A'}</div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Primary Plate</div>
                                <div style="font-weight: 800; color: #1e293b;">${item.plate_number || 'N/A'}</div>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Vehicle Type</div>
                                <div style="font-weight: 800; color: #1e293b;">${item.vehicle_type || 'N/A'}</div>
                            </div>
                        </div>

                        <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <i class="ph ph-buildings" style="color: #64748b;"></i>
                                <div>
                                    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Department/Role</div>
                                    <div style="font-weight: 700; color: #1e293b;">${item.college_dept || item.office || item.role || 'N/A'}</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="ph ph-phone" style="color: #64748b;"></i>
                                <div>
                                    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Contact Number</div>
                                    <div style="font-weight: 700; color: #1e293b;">${item.contact_number || 'No contact info'}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #fef2f2; padding: 0.75rem; border-radius: 8px; border: 1px dashed #ef4444; text-align: center; color: #dc2626; font-weight: 700; font-size: 0.85rem;">
                            Status: ${item.status?.toUpperCase() || 'UNKNOWN'}
                        </div>
                    </div>
                `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#1e293b',
                width: '450px'
            });
        };
        // --- View Details Modal Logic ---
        const logDetailsModalEl = document.getElementById('logDetailsModal');
        // Safely initialize Bootstrap Modal if available
        const logDetailsModal = (logDetailsModalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(logDetailsModalEl) : null;
        const modalLoading = document.getElementById('modalLoading');
        const modalContent = document.getElementById('modalContent');
        
        // Use event delegation on the table for better performance and reliability
        if (logsTable) {
            logsTable.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-view-details');
                if (btn) {
                    e.preventDefault();
                    const logId = btn.getAttribute('data-id');
                    console.log('View Details clicked for ID:', logId);
                    if (logId) showLogDetails(logId);
                }
            });
        }

        async function showLogDetails(id) {
            if (!logDetailsModal) {
                console.error('Bootstrap Modal not initialized. Check if bootstrap is loaded.');
                // Fallback to SweetAlert if Bootstrap modal fails
                fetchAndShowSwal(id);
                return;
            }
            
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            logDetailsModal.show();

            try {
                const response = await fetch(`{{ url('guard/logs') }}/${id}`);
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    document.getElementById('detailOwner').innerText = data.owner;
                    document.getElementById('detailTag').innerText = `Tag: ${data.rfid_tag}`;
                    document.getElementById('detailPlate').innerText = data.plate;
                    document.getElementById('detailVehicle').innerText = data.vehicle_details;
                    document.getElementById('detailTimestamp').innerText = data.timestamp;
                    
                    const typeEl = document.getElementById('detailType');
                    typeEl.innerText = data.type;
                    typeEl.className = 'badge';
                    if (data.type === 'ENTRY') {
                        typeEl.style.background = '#ecfdf5';
                        typeEl.style.color = '#059669';
                        typeEl.style.border = '1px solid #10b981';
                    } else {
                        typeEl.style.background = '#fef2f2';
                        typeEl.style.color = '#dc2626';
                        typeEl.style.border = '1px solid #ef4444';
                    }

                    modalLoading.style.display = 'none';
                    modalContent.style.display = 'block';
                }
            } catch (e) {
                console.error('Error fetching log details:', e);
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: 'Could not retrieve activity details.',
                    confirmButtonColor: '#1e293b'
                });
                logDetailsModal.hide();
            }
        }

        async function fetchAndShowSwal(id) {
            Swal.fire({
                title: 'Loading...',
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await fetch(`{{ url('guard/logs') }}/${id}`);
                const result = await response.json();
                if (result.success) {
                    const data = result.data;
                    Swal.fire({
                        title: 'Activity Details',
                        html: `
                            <div style="text-align: left;">
                                <strong>Owner:</strong> ${data.owner}<br>
                                <strong>Plate:</strong> ${data.plate}<br>
                                <strong>Type:</strong> ${data.type}<br>
                                <strong>Vehicle:</strong> ${data.vehicle_details}<br>
                                <strong>Time:</strong> ${data.timestamp}
                            </div>
                        `,
                        confirmButtonColor: '#741b1b'
                    });
                }
            } catch (e) {
                Swal.fire('Error', 'Fetch failed', 'error');
            }
        }
    });

    let isLockdownActive = {{ $ldState['active'] ? 'true' : 'false' }};
    function checkLockdownStatus() {
        fetch('{{ route('guard.lockdown.check') }}')
            .then(res => res.json())
            .then(data => {
                const banner = document.getElementById('lockdownBanner');
                const reasonLabel = document.getElementById('lockdownReasonLabel');
                const alarm = document.getElementById('lockdownAlarm');
                const ticker = document.getElementById('tickerContent');
                
                if (ticker && data.ticker) ticker.textContent = data.ticker;

                if (data.active) {
                    banner.style.display = 'flex';
                    if (reasonLabel) reasonLabel.textContent = data.reason;
                    if (!isLockdownActive) {
                        if (alarm) {
                            alarm.currentTime = 0;
                            alarm.play().catch(e => console.log('Audio playback blocked by browser policy until interaction', e));
                        }
                        isLockdownActive = true;
                    }
                } else {
                    banner.style.display = 'none';
                    isLockdownActive = false;
                }
            })
            .catch(e => console.error('Lockdown poll error', e));
    }
    setInterval(checkLockdownStatus, 10000);
</script>

<style>
    .animate-spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes highlight { from { background: #f0f9ff; } to { background: transparent; } }
    .badge { padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
    .search-result-item:hover { background: #f8fafc; }
    .clickable-plate:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
</style>
@endsection
@endsection
