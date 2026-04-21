@extends('layouts.app')

@section('title', 'Campus Fleet & Role Analytics')
@section('subtitle', 'Analyzing user types, vehicle concentrations, and live campus occupancy.')

@section('content')
<div class="stats-container">
    @include('partials.report-header')
    <!-- Top Summary Tier -->
    @php
        $fleetStats = [
            ['label' => 'Most Popular Category', 'value' => $summary['popularCategory'], 'icon' => 'ph ph-car-profile'],
            ['label' => 'Leading Vehicle Brand', 'value' => $summary['popularBrand'], 'icon' => 'ph ph-medal-military'],
            ['label' => 'Total Registered Vehicles', 'value' => $totalVehicles, 'icon' => 'ph ph-gauge'],
            ['label' => 'Real-Time Occupancy', 'value' => array_sum(array_column($occupancyBreakdown, 'total')), 'icon' => 'ph ph-buildings-three'],
        ];
    @endphp
    @include('partials.stats-overview', ['stats' => $fleetStats])

    <div class="stats-main-grid">
        <!-- New Primary Visualization: User Role Distribution -->
        <div class="stats-card pie-card">
            <div class="card-title">
                <h3><i class="ph ph-users-three" style="color: #741b1b;"></i> User Role Distribution</h3>
                <p>Overall percentage breakdown of Students, Personnel, and Vendors.</p>
            </div>
            <div class="chart-box" style="min-height: 300px;">
                <canvas id="roleDistributionChart"></canvas>
            </div>
        </div>

        <!-- Role Drill-down Column -->
        <div class="role-drilldown-list">
            @foreach(['student', 'faculty', 'staff'] as $r)
                <div class="drilldown-card" onclick="toggleDrill('{{ $r }}')">
                    <div class="d-header">
                        <div class="d-title">
                            <div class="icon-circle {{ $r }}">
                                <i class="ph ph-{{ $r === 'student' ? 'graduation-cap' : ($r === 'faculty' ? 'chalkboard-teacher' : 'identification-badge') }}"></i>
                            </div>
                            @php
                                $rolePercent = $totalOwners > 0 ? round(($stats[$r]['owners'] / $totalOwners) * 100) : 0;
                                $roleDisplayNames = [
                                    'student' => 'Student Analytics',
                                    'faculty' => 'Personnel Analytics',
                                    'staff' => 'Vendor Analytics'
                                ];
                                $roleTitle = $roleDisplayNames[$r] ?? ucfirst($r) . ' Analytics';
                            @endphp
                            <div>
                                <h4>{{ $roleTitle }} <span style="color: #741b1b;">({{ $rolePercent }}%)</span></h4>
                                <p>{{ number_format($stats[$r]['owners']) }} Registered Persons</p>
                            </div>
                        </div>
                        <div class="d-metric" style="display: flex; align-items: center; gap: 1.5rem;">
                            <!-- Task: Small dynamic chart now visible on header -->
                            <div style="height: 48px; width: 48px; position: relative;">
                                <canvas id="chart-{{ $r }}"></canvas>
                            </div>
                            <span class="v-count"><i class="ph-bold ph-car"></i> {{ $stats[$r]['vehicles'] }}</span>
                        </div>
                    </div>
                    
                    <div class="drill-details" id="drill-box-{{ $r }}" style="display: none;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="breakdown-grid" style="justify-content: flex-start; margin-bottom: 0;">
                                @foreach($stats[$r]['breakdown'] as $type => $count)
                                    <div class="type-pill">
                                        <span class="t-label">{{ ucfirst($type) }}</span>
                                        <span class="t-count">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="multi-owners-box">
                                <h5>Top Multi-Vehicle Owners</h5>
                                @foreach($stats[$r]['top_multi'] as $owner)
                                    <div class="m-owner">
                                        <span>{{ $owner->full_name }}</span>
                                        <strong>{{ $owner->vehicles_count }} Units</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="occupancy-subset">
                                <h5>Live Inside Campus</h5>
                                <div class="occupancy-report-box">
                                    <div style="font-size: 1.25rem; font-weight: 900; color: #741b1b;">{{ $occupancyBreakdown[$r]['total'] }}</div>
                                    <div style="font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Active Units</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Fleet Composition Delta Moved Below -->
            <div class="stats-card pie-card" style="margin-top: 1rem; padding: 2rem;">
                <div class="card-title">
                    <h3 style="font-size: 1rem;"><i class="ph ph-chart-pie" style="color: #741b1b;"></i> Fleet Composition Delta</h3>
                    <p style="font-size: 0.75rem;">Deep-dive: Role vs. Vehicle Category</p>
                </div>
                <div class="chart-box" style="min-height: 250px;">
                    <canvas id="nestedFleetChart"></canvas>
                </div>
            </div>
        </div>
    </div>
        <div class="no-print mt-6" style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="ph ph-printer"></i> Print Official Report
        </button>
    </div>

    @include('partials.report-signatories')
</div>
</div>

<style>
    .stats-container { display: flex; flex-direction: column; gap: 1.5rem; }
    
    /* Summary Cards */
    .stats-summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .summary-stat-card { background: white; padding: 1.5rem 2rem; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .s-info label { font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.5rem; display: block; }
    .s-info h2 { margin: 0; font-size: 1.75rem; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 12px; }
    .s-info h2 i { color: #741b1b; }
    .s-ratio { text-align: right; }
    .ratio-val { font-size: 1rem; font-weight: 800; color: #741b1b; }
    .s-ratio p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 700; }

    .occupancy-live { background: #fdf2f2; border-color: #fee2e2; }
    .occupancy-pill { padding: 4px 12px; border-radius: 99px; background: #741b1b; color: #fff; font-size: 0.65rem; font-weight: 800; display: flex; align-items: center; gap: 6px; }
    .pulse-dot { width: 6px; height: 6px; background: #fff; border-radius: 50%; box-shadow: 0 0 0 0 rgba(255, 255, 255, 1); animation: pulse 1.5s infinite; }

    /* Main Grid */
    .stats-main-grid { display: grid; grid-template-columns: 1fr 450px; gap: 1.5rem; }
    .stats-card { background: white; padding: 2.5rem; border-radius: 28px; border: 1px solid #e2e8f0; }
    .pie-card { display: flex; flex-direction: column; }
    .chart-box { flex: 1; min-height: 400px; display: flex; align-items: center; justify-content: center; }

    /* Drilldown Cards */
    .role-drilldown-list { display: flex; flex-direction: column; gap: 1rem; }
    .drilldown-card { background: white; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; cursor: pointer; transition: 0.2s; }
    .drilldown-card:hover { transform: translateX(8px); border-color: #741b1b; }
    .drilldown-card.active-drill { border-color: #741b1b; background: #fffcfc; box-shadow: 0 10px 25px -5px rgba(116, 27, 27, 0.1); transform: translateX(10px); }
    .d-header { display: flex; justify-content: space-between; align-items: center; }
    .d-title { display: flex; align-items: center; gap: 1rem; }
    .icon-circle { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .icon-circle.student { background: #eff6ff; color: #3b82f6; }
    .icon-circle.faculty { background: #fef2f2; color: #ef4444; }
    .icon-circle.staff { background: #f0fdf4; color: #22c55e; }
    .d-title h4 { margin: 0; font-size: 1rem; font-weight: 800; color: #1e293b; }
    .d-title p { margin: 0; font-size: 0.8rem; color: #94a3b8; font-weight: 600; }
    .v-count { font-size: 0.9rem; font-weight: 800; color: #741b1b; background: #fdf2f2; padding: 4px 12px; border-radius: 8px; }

    .drill-details { padding-top: 1.5rem; margin-top: 1.5rem; border-top: 1px dashed #e2e8f0; animation: slideDown 0.3s ease-out; }
    .breakdown-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 1.5rem; }
    .type-pill { background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; display: flex; gap: 10px; }
    .t-label { color: #64748b; font-weight: 700; }
    .t-count { color: #1e293b; font-weight: 800; }

    .multi-owners-box h5, .occupancy-subset h5 { font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 0.75rem; }
    .m-owner { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 6px 0; border-bottom: 1px solid #f8fafc; }
    .occupancy-subset p { margin: 0; font-size: 0.9rem; color: #1e293b; }
    .sub-types { font-size: 0.75rem; color: #64748b; margin-left: 8px; }

    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.register(ChartDataLabels);
        Chart.defaults.plugins.datalabels.display = false;

        const pieData = @json($pieData);

        // 1. Role Distribution Chart (New)
        const roleCtx = document.getElementById('roleDistributionChart').getContext('2d');
        const roleLabels = pieData.outer.map(d => d.label);
        const roleValues = pieData.outer.map(d => d.value);
        
        new Chart(roleCtx, {
            type: 'pie',
            data: {
                labels: roleLabels,
                datasets: [{
                    data: roleValues,
                    backgroundColor: ['#3b82f6', '#ef4444', '#22c55e'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    datalabels: {
                        display: true,
                        color: '#ffffff',
                        font: {
                            weight: 'bold',
                            size: 16
                        },
                        formatter: (value, context) => {
                            const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                            if (total === 0 || value === 0) return '';
                            const percentage = Math.round((value / total) * 100) + '%';
                            return percentage;
                        }
                    }
                }
            }
        });

        // 2. Role-Specific Charts (Dynamic Inline)
        const roleStats = @json($stats);
        ['student', 'faculty', 'staff'].forEach(role => {
            const rCtx = document.getElementById(`chart-${role}`);
            if (!rCtx) return;

            const breakdown = roleStats[role].breakdown;
            const labels = Object.keys(breakdown);
            const values = Object.values(breakdown);

            if (values.length === 0) {
                new Chart(rCtx, {
                    type: 'doughnut',
                    data: { labels: ['No Vehicles'], datasets: [{ data: [1], backgroundColor: ['#f1f5f9'] }] },
                    options: { cutout: '80%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
                return;
            }

            const roleBaseHue = role === 'student' ? 214 : (role === 'faculty' ? 0 : 142);

            new Chart(rCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#3b82f6', // Blue
                            '#ef4444', // Red
                            '#f59e0b', // Amber
                            '#10b981', // Emerald
                            '#8b5cf6', // Violet
                            '#06b6d4', // Cyan
                            '#ec4899'  // Pink
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        });

        // 3. Dynamic Fleet Composition Delta Chart (Updated per User Request)
        const ctx = document.getElementById('nestedFleetChart').getContext('2d');
        let currentChart = null;

        const chartData = {
            overall: {
                labels: pieData.outer.map(d => d.label),
                datasets: [
                    {
                        label: 'Vehicle Categories',
                        data: pieData.inner.map(d => d.value),
                        backgroundColor: pieData.inner.map((d, i) => {
                            const colors = ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#06b6d4', '#ec4899'];
                            return colors[i % colors.length];
                        }),
                        weight: 0.5
                    },
                    {
                        label: 'User Roles',
                        data: pieData.outer.map(d => d.value),
                        backgroundColor: ['#3b82f6', '#ef4444', '#22c55e'],
                        weight: 1
                    }
                ]
            },
            student: getRoleChartData('student'),
            faculty: getRoleChartData('faculty'),
            staff: getRoleChartData('staff')
        };

        function getRoleChartData(role) {
            const breakdown = roleStats[role].breakdown;
            const hue = role === 'student' ? 214 : (role === 'faculty' ? 0 : 142);
            return {
                labels: Object.keys(breakdown).map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                datasets: [{
                    data: Object.values(breakdown),
                    backgroundColor: [
                        '#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#06b6d4', '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 12
                }]
            };
        }

        const initialOptions = {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '40%',
            animation: { animateRotate: true, duration: 800 },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: '700' } } },
                datalabels: {
                    display: true,
                    color: '#ffffff',
                    font: { weight: 'bold', size: 11 },
                    formatter: (value, context) => {
                        const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                        if (total === 0 || value === 0) return '';
                        const percentage = Math.round((value / total) * 100);
                        if (percentage < 4) return ''; // Hide on too-small slices
                        return percentage + '%';
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        };

        const filteredOptions = {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            animation: { animateRotate: true, duration: 800 },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: '700' } } },
                datalabels: {
                    display: true,
                    color: '#ffffff',
                    font: { weight: 'bold', size: 14 },
                    formatter: (value, context) => {
                        const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                        if (total === 0 || value === 0) return '';
                        const percentage = Math.round((value / total) * 100);
                        if (percentage < 4) return '';
                        return percentage + '%';
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        };

        currentChart = new Chart(ctx, {
            type: 'doughnut',
            data: chartData.overall,
            options: initialOptions
        });

        window.toggleDrill = (role) => {
            const el = document.getElementById(`drill-box-${role}`);
            const isHidden = el.style.display === 'none';
            
            // UI Toggle Logic
            document.querySelectorAll('.drill-details').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.drilldown-card').forEach(c => c.classList.remove('active-drill'));

            if(isHidden) {
                el.style.display = 'block';
                el.closest('.drilldown-card').classList.add('active-drill');
                updateMainFleetChart(role);
            } else {
                updateMainFleetChart('overall');
            }
        };

        function updateMainFleetChart(filter) {
            currentChart.data = chartData[filter];
            currentChart.options = filter === 'overall' ? initialOptions : filteredOptions;
            
            // Fade effect for chart update
            const parent = document.getElementById('nestedFleetChart').parentElement;
            parent.style.opacity = '0.4';
            setTimeout(() => {
                currentChart.update();
                parent.style.opacity = '1';
            }, 150);
        }
    });
</script>
@endsection
