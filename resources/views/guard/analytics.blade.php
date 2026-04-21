@extends('layouts.app')

@section('title', 'Traffic Analytics Hub')
@section('subtitle', 'Comprehensive flow monitoring and asset tracking reports.')

@section('content')
<div class="analytics-container">
    
    <!-- Top Action Row -->
    <div class="analytics-header no-print">
        <div class="range-filters">
            <a href="?range=12h" class="filter-btn {{ $currentRange === '12h' ? 'active' : '' }}">Last 12 Hours</a>
            <a href="?range=today" class="filter-btn {{ $currentRange === 'today' ? 'active' : '' }}">Today</a>
            <a href="?range=7d" class="filter-btn {{ $currentRange === '7d' ? 'active' : '' }}">Last 7 Days</a>
        </div>

        <form action="{{ url()->current() }}" method="GET" class="custom-range-filter no-print">
            <div class="date-input-group">
                <input type="date" name="from" value="{{ $fromDate ?? date('Y-m-d') }}" class="range-input">
                <span class="divider">to</span>
                <input type="date" name="to" value="{{ $toDate ?? date('Y-m-d') }}" class="range-input">
                <button type="submit" class="range-submit">
                    <i class="ph ph-funnel"></i>
                </button>
            </div>
        </form>
        
        <div class="header-actions">
            <div id="syncIndicator" class="sync-status">
                <i class="ph ph-arrows-clockwise"></i> Direct Sync Live
            </div>
            <button onclick="window.print()" class="btn-print">
                <i class="ph ph-printer"></i> Print Analytics Report
            </button>
        </div>
    </div>

    <!-- PRINT HEADER (ONLY VISIBLE ON PRINT) -->
    @include('partials.report-header')

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card entries">
            <div class="card-icon"><i class="ph-fill ph-sign-in"></i></div>
            <div class="card-content">
                <span class="card-label">Total Entries</span>
                <h2 id="summaryEntries">{{ number_format($summary['total_entries']) }}</h2>
                <span class="card-trend text-success"><i class="ph ph-trend-up"></i> Verified Flow</span>
            </div>
        </div>
        
        <div class="stat-card exits">
            <div class="card-icon"><i class="ph-fill ph-sign-out"></i></div>
            <div class="card-content">
                <span class="card-label">Total Exits</span>
                <h2 id="summaryExits">{{ number_format($summary['total_exits']) }}</h2>
                <span class="card-trend text-info"><i class="ph ph-check-circle"></i> Logged Dispatches</span>
            </div>
        </div>

        <div class="stat-card net">
            <div class="card-icon"><i class="ph-fill ph-buildings"></i></div>
            <div class="card-content">
                <span class="card-label">Est. Assets Inside</span>
                <h2 id="summaryInside">{{ number_format($summary['total_entries'] - $summary['total_exits']) }}</h2>
                <span class="card-trend"><i class="ph ph-activity"></i> Active Presence</span>
            </div>
        </div>
    </div>

    <!-- Main Chart Section -->
    <div class="chart-section canvas-container">
        <div class="section-title mb-4">
            <h3><i class="ph ph-chart-line-up"></i> Real-time Traffic Trend Visualization</h3>
            <p>Hourly entry vs. exit delta spanning {{ $summary['range_text'] }}</p>
        </div>
        <div style="height: 400px; position: relative;">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>

    <div class="bottom-grid">
        <!-- Peak Hours Table -->
        <div class="table-card">
            <div class="section-title mb-4">
                <h3><i class="ph ph-clock-countdown"></i> Peak Hour Flow Breakdown</h3>
                <p>Top 5 time slots with highest entry/exit density.</p>
            </div>
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Time Window</th>
                        <th>Activity Volume</th>
                        <th>Density Status</th>
                    </tr>
                </thead>
                <tbody id="peakHoursTable">
                    @forelse($peakHours as $p)
                        <tr>
                            <td class="font-bold">{{ $p['hour'] }}</td>
                            <td>{{ $p['count'] }} scans</td>
                            <td>
                                @if($p['count'] > 20)
                                    <span class="badge badge-error">High Density</span>
                                @else
                                    <span class="badge badge-warning">Moderate</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No peak data found for this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Overstaying Report -->
        <div class="table-card">
            <div class="section-title mb-4" style="color: #ef4444;">
                <h3><i class="ph ph-warning-octagon"></i> Security Overstay Report</h3>
                <p>Vehicles that entered > 12 hours ago and haven't logged an exit.</p>
            </div>
            <table class="analytics-table table-red">
                <thead>
                    <tr>
                        <th>Plate / Tag ID</th>
                        <th>Owner</th>
                        <th>Entry Timestamp</th>
                    </tr>
                </thead>
                <tbody id="overstayTable">
                    @forelse($overstaying as $o)
                        <tr>
                            <td>
                                <div class="font-bold">{{ $o->vehicle->plate_number ?? 'NO PLATE' }}</div>
                                <div class="text-muted small">{{ $o->rfid_tag_id }}</div>
                            </td>
                            <td>{{ $o->vehicleRegistration->full_name ?? 'Unregistered' }}</td>
                            <td class="text-danger font-bold">{{ $o->timestamp->format('M d, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No overstaying vehicles detected.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.report-signatories')
</div>

<style>
    .analytics-container { display: flex; flex-direction: column; gap: 2rem; }
    
    .analytics-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #e2e8f0; }
    
    .range-filters { display: flex; gap: 0.75rem; }
    .filter-btn { padding: 0.6rem 1.25rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; transition: 0.2s; }
    .filter-btn:hover { background: #f1f5f9; color: #1e293b; }
    .filter-btn.active { background: #741b1b; color: white; border-color: #741b1b; }

    .custom-range-filter { display: flex; align-items: center; }
    .date-input-group { display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 4px; gap: 8px; }
    .range-input { border: none; background: transparent; font-size: 0.8rem; font-weight: 700; color: #1e293b; padding: 0.4rem 0.6rem; outline: none; }
    .divider { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
    .range-submit { background: #741b1b; color: white; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .range-submit:hover { background: #450a0a; transform: scale(1.05); }
    [data-theme='dark'] .date-input-group { background: #0f172a; border-color: #334155; }
    [data-theme='dark'] .range-input { color: #f8fafc; }

    .sync-status { display: inline-flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 800; color: #10b981; margin-right: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .sync-status i { animation: spin 4s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    .btn-print { background: #1e293b; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s; }
    .btn-print:hover { background: #0f172a; transform: translateY(-2px); }

    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .stat-card { background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; gap: 1.25rem; align-items: center; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    
    .card-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .entries .card-icon { background: #dcfce7; color: #10b981; }
    .exits .card-icon { background: #eff6ff; color: #3b82f6; }
    .net .card-icon { background: #fefce8; color: #f59e0b; }
    
    .card-content { flex: 1; }
    .card-label { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
    .card-content h2 { font-size: 1.75rem; font-weight: 800; margin: 0.25rem 0; color: #1e293b; }
    .card-trend { font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 4px; }

    .chart-section { background: white; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; }
    .section-title h3 { font-size: 1.25rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem 0; }
    .section-title p { font-size: 0.85rem; color: #64748b; margin: 0; }

    .bottom-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem; }
    .table-card { background: white; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; }

    .analytics-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    .analytics-table th { text-align: left; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .analytics-table td { padding: 14px 12px; font-size: 0.9rem; border-bottom: 1px solid #f8fafc; }
    .table-red tr:hover { background: #fff1f2 !important; }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
    .badge-error { background: #fee2e2; color: #ef4444; }
    .badge-warning { background: #fef3c7; color: #92400e; }

    .font-bold { font-weight: 700; }
    .text-muted { color: #94a3b8; }
    .small { font-size: 0.75rem; }

    /* Print Specifics */
    .print-only { display: none; }
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .letterhead { display: flex !important; align-items: center; gap: 2rem; padding: 3rem 0; border-bottom: 3px double #1e293b; margin-bottom: 3rem; }
        .print-logo { width: 100px; }
        .letterhead-text h2 { margin: 0; font-size: 1.5rem; font-weight: 900; }
        .letterhead-text p { margin: 0; color: #64748b; font-weight: 600; }
        .report-meta { margin-top: 1rem; font-size: 0.9rem; }
        
        .analytics-container { padding: 0 !important; }
        .stats-grid { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; margin-bottom: 2rem; }
        .stat-card { border: 1px solid #e2e8f0 !important; }
        .chart-section, .table-card { border: none !important; padding: 0 !important; }
        canvas { max-width: 100% !important; height: auto !important; }
        .bottom-grid { grid-template-columns: 1fr !important; }
    }
</style>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trafficChart').getContext('2d');
        let trafficChart;

        // Gradient for Entries
        const entryGradient = ctx.createLinearGradient(0, 0, 0, 400);
        entryGradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        entryGradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        // Gradient for Exits
        const exitGradient = ctx.createLinearGradient(0, 0, 0, 400);
        exitGradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        exitGradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        function initChart(labels, entryData, exitData) {
            if (!ctx) return;
            try {
                console.log("Analytics Data Loaded:", { labels, entryData, exitData });
                trafficChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Entries',
                            data: entryData,
                            borderColor: '#10b981',
                            backgroundColor: entryGradient,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 4,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            pointBackgroundColor: 'white'
                        },
                        {
                            label: 'Exits',
                            data: exitData,
                            borderColor: '#3b82f6',
                            backgroundColor: exitGradient,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 4,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            pointBackgroundColor: 'white'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { weight: '700' } } },
                        tooltip: { backgroundColor: '#1e293b', padding: 12, titleFont: { size: 14, weight: '800' } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { weight: '600' } } },
                        x: { grid: { display: false }, ticks: { font: { weight: '600' } } }
                    }
                }
            });
            } catch (e) {
                console.error("Traffic Chart Initialization Failed:", e);
            }
        }

        // Initial Data
        const initialLabels = @json($labels);
        const initialEntries = @json($entries);
        const initialExits = @json($exits);
        initChart(initialLabels, initialEntries, initialExits);

        // Real-time Sync (60s interval)
        async function refreshData() {
            try {
                const params = new URLSearchParams(window.location.search);
                const range = params.get('range') || 'today';
                const from = params.get('from') || '';
                const to = params.get('to') || '';

                // Determine API endpoint based on current path
                const isGuard = window.location.pathname.includes('/guard/');
                let url = isGuard 
                    ? `{{ route('guard.analytics.data') }}?range=${range}` 
                    : `{{ route('admin.traffic-analytics.data') }}?range=${range}`;

                if(from && to) url += `&from=${from}&to=${to}`;
                
                const response = await fetch(url);
                const data = await response.json();

                // Update Summary
                document.getElementById('summaryEntries').innerText = data.summary.total_entries.toLocaleString();
                document.getElementById('summaryExits').innerText = data.summary.total_exits.toLocaleString();
                document.getElementById('summaryInside').innerText = (data.summary.total_entries - data.summary.total_exits).toLocaleString();

                // Update Chart
                trafficChart.data.labels = data.labels;
                trafficChart.data.datasets[0].data = data.entries;
                trafficChart.data.datasets[1].data = data.exits;
                trafficChart.update('none'); // silent update

                // Update Peak Hours Table
                const peakTable = document.getElementById('peakHoursTable');
                if (data.peakHours.length) {
                    peakTable.innerHTML = data.peakHours.map(p => `
                        <tr>
                            <td class="font-bold">${p.hour}</td>
                            <td>${p.count} scans</td>
                            <td>
                                <span class="badge ${p.count > 20 ? 'badge-error' : 'badge-warning'}">
                                    ${p.count > 20 ? 'High Density' : 'Moderate'}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    peakTable.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No peak data found for this range.</td></tr>';
                }

                // Briefly flash the sync indicator
                const indicator = document.getElementById('syncIndicator');
                indicator.style.color = '#3b82f6';
                setTimeout(() => indicator.style.color = '#10b981', 2000);

            } catch (error) {
                console.error('Failed to sync analytics:', error);
            }
        }

        setInterval(refreshData, 60000);
    });
</script>
@endsection
