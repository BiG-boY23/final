@extends('layouts.app')

@section('title', 'Owner Behavior Reports')
@section('subtitle', 'Deep-dive analysis into individual campus mobility patterns.')

@section('content')
<div class="stats-container">
    @include('partials.report-header')
    <!-- Header with Global Search -->
    <div class="behavior-control-bar" style="gap: 1rem; flex-wrap: wrap;">
        <div class="search-box-wrapper" style="min-width: 300px;">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="behaviorSearch" placeholder="Search owner to analyze..." autocomplete="off">
        </div>
        
        <!-- Date Picker Controls -->
        <div class="date-filter-group" style="display: flex; gap: 0.5rem; align-items: center; background: #f8fafc; padding: 5px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <select id="datePreset" class="form-control-sm" style="border: none; background: transparent; font-weight: 800; color: #741b1b; padding-right: 20px; cursor: pointer;">
                <option value="today">Today</option>
                <option value="7days" selected>This Week</option>
                <option value="30days">This Month</option>
                <option value="year">This Year</option>
                <option value="custom">Custom Range</option>
            </select>
            <div id="customRangeInputs" style="display: none; align-items: center; gap: 5px; padding: 0 10px; border-left: 1px solid #e2e8f0;">
                <input type="date" id="startDate" value="{{ $startDate }}" class="date-mini">
                <span style="color: #94a3b8; font-weight: 800; font-size: 0.7rem;">TO</span>
                <input type="date" id="endDate" value="{{ $endDate }}" class="date-mini">
            </div>
            <button id="btnApplyDate" class="btn-icon-mini" title="Apply Filter"><i class="ph ph-funnel"></i></button>
        </div>

        <div class="utility-chips">
            <span class="chip active" data-role="all">All Roles</span>
            <span class="chip" data-role="student">Student</span>
            <span class="chip" data-role="faculty">Personnel</span>
            <span class="chip" data-role="staff">Vendor</span>
        </div>
    </div>

    <!-- 1. Owner Behavior Summary Statistics Cards -->
    @php
        $behaviorStats = [
            ['label' => 'Total Active Users', 'value' => $summary['activeUsers'], 'icon' => 'ph ph-users', 'id' => 'card-active-users'],
            ['label' => 'Peak Activity Day', 'value' => $summary['peakDay'], 'icon' => 'ph ph-calendar-check', 'id' => 'card-peak-day'],
            ['label' => 'Most Frequent Role', 'value' => $summary['frequentRole'], 'icon' => 'ph ph-identification-card', 'id' => 'card-freq-role'],
            ['label' => 'Avg Scans per Day', 'value' => $summary['avgScans'], 'icon' => 'ph ph-chart-line-up', 'id' => 'card-avg-scans'],
        ];
    @endphp
    @include('partials.stats-overview', ['stats' => $behaviorStats])

    <div class="stats-grid">
        <!-- Dynamic Behavior Search Table -->
        <div class="stats-card search-card">
            <div class="card-title">
                <h3><i class="ph ph-list-magnifying-glass" style="color: #741b1b;"></i> Behavior Analysis Registry</h3>
                <p>Click a row to expand quick 7-day trends, or use "Audit" for deep dive.</p>
            </div>
            <div class="behavior-table-wrapper">
                <table class="behavior-table" id="behaviorTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Owner Profile</th>
                            <th>Role</th>
                            <th class="text-right">Assets</th>
                            <th class="text-right">Activity</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="behaviorTbody">
                        <!-- AJAX Content -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sidebar-stats">
            <!-- Peak Intensity Mini-Trend -->
            <div class="stats-card mini-stats">
                <div class="card-title">
                    <h3 style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span><i class="ph ph-trend-up" style="color: #10b981;"></i> Campus Traffic Intensity</span>
                    </h3>
                    <p id="trendDescription">Total daily scans for the selected period.</p>
                </div>
                <div style="height: 180px;">
                    <canvas id="behaviorTrendChart"></canvas>
                </div>
            </div>

            <!-- THE HOTLIST: Top 10 Frequent Explorers -->
            <div class="stats-card mini-stats">
                <div class="card-title">
                    <h3><i class="ph ph-fire" style="color: #f59e0b;"></i> Top 10 Frequent Explorers</h3>
                    <p>Highest scan volume in this period.</p>
                </div>
                <div id="hotlistContainer">
                    <table class="behavior-table compact">
                        <tbody id="hotlistTbody">
                            @foreach($frequentFlyers as $index => $f)
                            <tr>
                                <td style="width: 30px; font-weight: 900; color: #94a3b8;">#{{ $index + 1 }}</td>
                                <td>
                                    <div style="font-weight: 800; font-size: 0.85rem;">{{ $f->name }}</div>
                                    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">{{ $f->role }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="badge-count">{{ $f->count }} Scans</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Multi-Vehicle Concentration -->
            <div class="stats-card mini-stats">
                <div class="card-title">
                    <h3><i class="ph ph-files" style="color: #3b82f6;"></i> Asset Clusters</h3>
                    <p>Owners managing more than one vehicle.</p>
                </div>
                <table class="behavior-table compact">
                    @foreach($multiOwners as $m)
                    <tr>
                        <td style="font-size: 0.85rem; font-weight: 700;">{{ $m->full_name }}</td>
                        <td class="text-right font-bold" style="color: #741b1b;">{{ $m->vehicles_count }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DEEP AUDIT MODAL -->
<div id="auditModal" class="modal-overlay" style="display:none;">
    <div class="modal-content audit-modal">
        <div class="audit-header">
            <div class="ah-left">
                <div class="pfp-placeholder"><i class="ph ph-user"></i></div>
                <div class="ah-info">
                    <h2 id="modalOwnerName">-</h2>
                    <p id="modalOwnerMeta">-</p>
                </div>
            </div>
            <button class="close-audit"><i class="ph ph-x"></i></button>
        </div>
        
        <div class="audit-body">
            <div class="audit-grid">
                <div class="audit-left">
                    <div class="metrics-row">
                        <div class="metric-mini">
                            <label>Volume</label>
                            <span id="metricTotal">0</span>
                        </div>
                        <div class="metric-mini">
                            <label>Peak Time</label>
                            <span id="metricPeak">--:--</span>
                        </div>
                        <div class="metric-mini">
                            <label>Main Plate</label>
                            <span id="metricVehicle">N/A</span>
                        </div>
                    </div>

                    <div class="chart-section" style="margin-top: 2rem;">
                        <div class="chart-header-sub">
                            <h3>30-Day Activity Flow</h3>
                            <div class="legend-sub">
                                <span><i class="ph-fill ph-circle" style="color:#741b1b;"></i> In</span>
                                <span><i class="ph-fill ph-circle" style="color:#94a3b8;"></i> Out</span>
                            </div>
                        </div>
                        <div style="height: 250px;">
                            <canvas id="individualBehaviorChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="audit-right">
                    <h3 class="panel-title">Recent Activity Logs</h3>
                    <div class="logs-container" id="logsContainer">
                        <!-- AJAX Logs -->
                    </div>
                </div>
            </div>
        </div>

        <div class="audit-footer">
            <button class="btn-export-audit">
                <i class="ph ph-file-pdf"></i> Export Behavior History
            </button>
        </div>
    </div>
    <div class="no-print mt-6" style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="ph ph-printer"></i> Print Official Analysis
        </button>
    </div>

    @include('partials.report-signatories')
</div>

<style>
    .stats-container { display: flex; flex-direction: column; gap: 1.5rem; }
    .behavior-control-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1.25rem 2rem; border-radius: 20px; border: 1px solid #e2e8f0; }
    .search-box-wrapper { display: flex; align-items: center; gap: 1.25rem; flex: 1; }
    .search-box-wrapper input { width: 100%; border: none; outline: none; font-size: 1.1rem; font-weight: 600; color: #1e293b; background: transparent; }
    
    .utility-chips { display: flex; gap: 0.75rem; }
    .chip { padding: 8px 18px; border-radius: 99px; background: #f1f5f9; color: #64748b; font-size: 0.8rem; font-weight: 800; cursor: pointer; transition: 0.2s; border: 1px solid transparent; }
    .chip.active { background: #741b1b; color: #fff; border-color: #741b1b; }

    .stats-grid { display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem; align-items: start; }
    .stats-card { background: white; padding: 2rem; border-radius: 24px; border: 1px solid #e2e8f0; }
    
    .behavior-table { width: 100%; border-collapse: collapse; }
    .behavior-table th { text-align: left; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; padding: 1rem; border-bottom: 1px solid #f1f5f9; }
    .behavior-table td { padding: 1.25rem 1rem; font-size: 0.95rem; border-bottom: 1px solid #f8fafc; color: #1e293b; transition: 0.2s; }
    .owner-row { cursor: pointer; }
    .owner-row:hover td { background: #fcfcfc; }
    .owner-row.expanded td { background: #fffcfc; border-bottom: none; }

    .expandable-row { display: none; background: #fffcfc; }
    .expand-content { padding: 0 1rem 2rem 50px; }
    .sparkline-box { height: 100px; max-width: 400px; background: #fff; border: 1px solid #fee2e2; border-radius: 12px; padding: 10px; }

    .btn-audit-trigger { padding: 8px 16px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; color: #741b1b; font-weight: 800; font-size: 0.75rem; cursor: pointer; transition: 0.2s; }
    .btn-audit-trigger:hover { background: #741b1b; color: #fff; transform: translateY(-2px); }

    .sidebar-stats { display: flex; flex-direction: column; gap: 1.5rem; }
    
    .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; z-index: 2000; }
    .audit-modal { background: #fff; width: 95%; max-width: 1000px; border-radius: 32px; overflow: hidden; box-shadow: 0 30px 100px rgba(0,0,0,0.3); }
    .audit-header { padding: 2.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .ah-left { display: flex; align-items: center; gap: 1.5rem; }
    .pfp-placeholder { width: 60px; height: 60px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #94a3b8; }
    .ah-info h2 { margin: 0; font-size: 1.5rem; font-weight: 800; color: #1e293b; }
    .ah-info p { margin: 0; color: #64748b; font-weight: 700; font-size: 0.9rem; }
    .close-audit { background: #f1f5f9; border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; }

    .audit-body { padding: 2.5rem; }
    .audit-grid { display: grid; grid-template-columns: 1fr 380px; gap: 3rem; }
    
    .metrics-row { display: flex; gap: 1.5rem; }
    .metric-mini { flex: 1; padding: 1.25rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-align: center; }
    .metric-mini label { display: block; font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.5rem; }
    .metric-mini span { font-size: 1.25rem; font-weight: 800; color: #1e293b; }

    .chart-header-sub { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .chart-header-sub h3 { font-size: 1rem; font-weight: 800; margin: 0; color: #1e293b; }
    .legend-sub { display: flex; gap: 1rem; font-size: 0.75rem; font-weight: 700; }

    .panel-title { font-size: 1rem; font-weight: 800; margin: 0 0 1.5rem; color: #1e293b; }
    .logs-container { max-height: 400px; overflow-y: auto; padding-right: 10px; }
    .log-item { display: flex; flex-direction: column; gap: 4px; padding: 12px; background: #f8fafc; border-radius: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
    .log-item .l-top { display: flex; justify-content: space-between; align-items: center; }
    .l-type { font-size: 0.65rem; font-weight: 900; padding: 2px 8px; border-radius: 4px; }
    .l-type-entry { background: #dcfce7; color: #166534; }
    .l-type-exit { background: #f1f5f9; color: #475569; }
    .l-time { font-size: 0.8rem; font-weight: 700; color: #1e293b; }
    .l-plate { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 800; color: #741b1b; }

    .audit-footer { padding: 1.5rem 2.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .btn-export-audit { width: 100%; padding: 1.1rem; border-radius: 12px; border: none; background: #1e293b; color: #fff; font-size: 1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; }

    /* New styles for Date Filtering & Badges */
    .date-mini { border: none; background: #fff; font-size: 0.75rem; font-weight: 800; color: #1e293b; padding: 4px 8px; border-radius: 6px; outline: none; }
    .btn-icon-mini { background: #741b1b; color: #fff; border: none; width: 28px; height: 28px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .btn-icon-mini:hover { background: #5d1515; transform: scale(1.1); }
    .badge-count { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 900; }
    
    .scan-tooltip { position: relative; cursor: help; }
    .scan-tooltip:hover::after { content: attr(data-detail); position: absolute; bottom: 100%; right: 0; background: #1e293b; color: #fff; padding: 5px 10px; border-radius: 8px; font-size: 0.7rem; white-space: nowrap; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @php $rolePrefix = auth()->user()->role; @endphp
    document.addEventListener('DOMContentLoaded', function() {
        const rolePrefix = '{{ $rolePrefix }}';
        const searchInput = document.getElementById('behaviorSearch');
        const tbody = document.getElementById('behaviorTbody');
        const hotlistTbody = document.getElementById('hotlistTbody');
        const auditModal = document.getElementById('auditModal');
        const datePreset = document.getElementById('datePreset');
        const customRangeInputs = document.getElementById('customRangeInputs');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const btnApplyDate = document.getElementById('btnApplyDate');

        let auditChart = null;
        let globalTrendChart = null;
        let sparklineCharts = {};
        let currentRole = 'all';

        // 1. Initialize Global Trend Chart
        const initGlobalChart = (labels, counts) => {
            const ctxGlobal = document.getElementById('behaviorTrendChart').getContext('2d');
            if (globalTrendChart) globalTrendChart.destroy();
            globalTrendChart = new Chart(ctxGlobal, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true, tension: 0.4, borderWidth: 3, pointRadius: 2, pointHoverRadius: 5
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                    scales: { 
                        x: { grid: { display: false } }, 
                        y: { display: true, beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 1 } } 
                    }
                }
            });
        };

        initGlobalChart(@json($labels), @json($activityCounts));

        // 2. Fetch Registry Table & Sidebar Data
        const updateAnalytics = async () => {
            const q = searchInput.value;
            const role = currentRole;
            const start = startDateInput.value;
            const end = endDateInput.value;

            // Update Registry Table
            const resTable = await fetch(`/${rolePrefix}/stats/behavior/search?q=${q}&role=${role}&start=${start}&end=${end}`);
            const dataTable = await resTable.json();
            
            tbody.innerHTML = dataTable.map(owner => `
                <tr class="owner-row" data-id="${owner.id}">
                    <td><i class="ph ph-caret-right chevron-icon" style="color:#94a3b8;"></i></td>
                    <td><strong>${owner.name}</strong></td>
                    <td><span class="chip" style="font-size:0.65rem; background: #f8fafc; border: 1px solid #e2e8f0;">${owner.role}</span></td>
                    <td class="text-right font-bold" style="color: #64748b;">${owner.vehicles}</td>
                    <td class="text-right">
                        <span class="badge-count scan-tooltip" data-detail="${owner.entries} Entry / ${owner.exits} Exit">
                            ${owner.activity} Scans
                        </span>
                    </td>
                    <td class="text-right">
                        <button class="btn-audit-trigger" data-id="${owner.id}">Audit</button>
                    </td>
                </tr>
                <tr class="expandable-row" id="exp-${owner.id}">
                    <td colspan="6">
                        <div class="expand-content">
                            <div style="margin-bottom:0.5rem; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase;">Presence Trend (Entire History)</div>
                            <div class="sparkline-box">
                                <canvas id="spark-${owner.id}"></canvas>
                            </div>
                        </div>
                    </td>
                </tr>
            `).join('') || '<tr><td colspan="6" class="text-center py-5">No behavioral data found for selected criteria.</td></tr>';

            // Update Sidebar Trend & Hotlist
            const resStats = await fetch(`/${rolePrefix}/stats/behavior?start=${start}&end=${end}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const dataStats = await resStats.json();

            // Refresh Trend Chart
            initGlobalChart(dataStats.labels, dataStats.activityCounts);

            // Update Summary Cards
            document.querySelector('#card-active-users .stat-card-value').textContent = dataStats.summary.activeUsers;
            document.querySelector('#card-peak-day .stat-card-value').textContent = dataStats.summary.peakDay;
            document.querySelector('#card-freq-role .stat-card-value').textContent = dataStats.summary.frequentRole;
            document.querySelector('#card-avg-scans .stat-card-value').textContent = dataStats.summary.avgScans;

            // Refresh Hotlist (Top 10)
            hotlistTbody.innerHTML = dataStats.frequentFlyers.map((f, i) => `
                <tr>
                    <td style="width: 30px; font-weight: 900; color: #94a3b8;">#${i + 1}</td>
                    <td>
                        <div style="font-weight: 800; font-size: 0.85rem;">${f.name}</div>
                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">${f.role}</div>
                    </td>
                    <td class="text-right">
                        <span class="badge-count">${f.count} Scans</span>
                    </td>
                </tr>
            `).join('');

            bindEvents();
        };

        const bindEvents = () => {
            document.querySelectorAll('.btn-audit-trigger').forEach(btn => {
                btn.onclick = (e) => { e.stopPropagation(); launchAudit(btn.dataset.id); };
            });
            document.querySelectorAll('.owner-row').forEach(row => {
                row.onclick = () => toggleRow(row.dataset.id, row);
            });
        };

        const toggleRow = async (id, rowEl) => {
            const expRow = document.getElementById(`exp-${id}`);
            const isVisible = expRow.style.display === 'table-row';
            document.querySelectorAll('.expandable-row').forEach(r => r.style.display = 'none');
            document.querySelectorAll('.owner-row').forEach(r => r.classList.remove('expanded'));
            if (!isVisible) {
                expRow.style.display = 'table-row';
                rowEl.classList.add('expanded');
                loadSparkline(id);
            }
        };

        const loadSparkline = async (id) => {
            if (sparklineCharts[id]) return;
            const res = await fetch(`/${rolePrefix}/stats/behavior/${id}/analyze`);
            const data = await res.json();
            const ctx = document.getElementById(`spark-${id}`).getContext('2d');
            sparklineCharts[id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.stats.labels,
                    datasets: [{ data: data.stats.entries, borderColor: '#741b1b', borderWidth: 2, fill: false, tension: 0.4, pointRadius: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { display: false }, y: { display: false, beginAtZero: true } }
                }
            });
        };

        const launchAudit = async (id) => {
            const res = await fetch(`/${rolePrefix}/stats/behavior/${id}/analyze`);
            const data = await res.json();
            document.getElementById('modalOwnerName').textContent = data.owner.name;
            document.getElementById('modalOwnerMeta').textContent = `${data.owner.role} • Joined ${data.owner.joined}`;
            document.getElementById('metricTotal').textContent = data.stats.total_activity;
            document.getElementById('metricPeak').textContent = data.stats.peak_hour;
            document.getElementById('metricVehicle').textContent = data.stats.most_used;
            document.getElementById('logsContainer').innerHTML = data.stats.latest_logs.map(log => `
                <div class="log-item">
                    <div class="l-top"><span class="l-time">${log.timestamp}</span><span class="l-type l-type-${log.type.toLowerCase()}">${log.type}</span></div>
                    <span class="l-plate">${log.plate}</span>
                </div>
            `).join('');
            const ctxIndiv = document.getElementById('individualBehaviorChart').getContext('2d');
            if(auditChart) auditChart.destroy();
            auditChart = new Chart(ctxIndiv, {
                type: 'line',
                data: {
                    labels: data.stats.labels,
                    datasets: [
                        { label: 'In', data: data.stats.entries, borderColor: '#741b1b', backgroundColor: 'rgba(116, 27, 27, 0.1)', fill: true, tension: 0.4 },
                        { label: 'Out', data: data.stats.exits, borderColor: '#94a3b8', backgroundColor: 'rgba(148, 163, 184, 0.1)', fill: true, tension: 0.3 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
                }
            });
            auditModal.style.display = 'flex';
        };

        // Date Selection Logic
        datePreset.addEventListener('change', function() {
            const val = this.value;
            const end = new Date();
            let start = new Date();

            if (val === 'today') {
                start = new Date();
            } else if (val === '7days') {
                start.setDate(end.getDate() - 6);
            } else if (val === '30days') {
                start.setDate(end.getDate() - 29);
            } else if (val === 'year') {
                start.setFullYear(end.getFullYear(), 0, 1);
            } else if (val === 'custom') {
                customRangeInputs.style.display = 'flex';
                return;
            }

            customRangeInputs.style.display = 'none';
            startDateInput.value = start.toISOString().split('T')[0];
            endDateInput.value = end.toISOString().split('T')[0];
            updateAnalytics();
        });

        btnApplyDate.onclick = updateAnalytics;
        searchInput.addEventListener('input', updateAnalytics);
        
        document.querySelectorAll('.chip[data-role]').forEach(chip => {
            chip.onclick = () => {
                document.querySelectorAll('.chip[data-role]').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                currentRole = chip.dataset.role;
                updateAnalytics();
            };
        });

        bindEvents(); // Bind initial static events
        document.querySelector('.close-audit').onclick = () => auditModal.style.display = 'none';
    });
</script>
@endsection
