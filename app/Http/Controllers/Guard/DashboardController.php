<?php

namespace App\Http\Controllers\Guard;

use App\Http\Controllers\Controller;
use App\Models\VehicleRegistration;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use App\Models\SystemSetting;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Aggregate Database Stats
        $stats = [
            'entries_today' => VehicleLog::where('type', 'entry')->whereDate('timestamp', $today)->count() + Visitor::whereDate('time_in', $today)->count(),
            'exits_today' => VehicleLog::where('type', 'exit')->whereDate('timestamp', $today)->count() + Visitor::where('status', 'left')->whereDate('time_out', $today)->count(),
            'visitors_inside' => Visitor::where('status', 'inside')->count(),
            'occupancy' => VehicleLog::dailyOccupancy(),
            'total_capacity' => (int)SystemSetting::get('total_parking_slots', 200),
            'guard_ticker' => SystemSetting::get('guard_ticker', 'Welcome to EVSU.')
        ];

        // Hourly Traffic Trend (Last 12 Hours) - Consistent with Analytics logic
        $hourlyTrends = [
            'labels' => [],
            'entries' => [],
            'exits' => []
        ];
        for ($i = 11; $i >= 0; $i--) {
            $time = now()->subHours($i);
            $start = $time->copy()->startOfHour();
            $end = $time->copy()->endOfHour();
            
            $hourlyTrends['labels'][] = $time->format('h A');
            $hourlyTrends['entries'][] = VehicleLog::where('type', 'entry')->whereBetween('timestamp', [$start, $end])->count() + 
                                         Visitor::whereBetween('time_in', [$start, $end])->count();
            $hourlyTrends['exits'][] = VehicleLog::where('type', 'exit')->whereBetween('timestamp', [$start, $end])->count() + 
                                        Visitor::whereBetween('time_out', [$start, $end])->count();
        }

        // Identify Overstaying Vehicles (> 12 Hours)
        // Find tags where the latest log is an 'entry' AND it happened > 12 hours ago
        $overstaying = VehicleLog::with('vehicleRegistration')
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('vehicle_logs')
                    ->groupBy('rfid_tag_id');
            })
            ->where('type', 'entry')
            ->where('timestamp', '<', now()->subHours(12))
            ->get();

        // Fetch recent logs (Filtered for Today only)
        $recentLogs = VehicleLog::with(['vehicleRegistration', 'vehicle'])
            ->whereDate('timestamp', $today)
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get();

        $visitors_inside = Visitor::where('status', 'inside')->orderByDesc('time_in')->get();

        return view('guard.dashboard', compact('stats', 'recentLogs', 'visitors_inside', 'hourlyTrends', 'overstaying'));
    }

    public function lookupTag(Request $request) {
        $tagId = $request->tagId;
        $vehicle = Vehicle::where('rfid_tag', $tagId)->first();
        
        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Tag not found in database']);
        }

        $registration = $vehicle->owner;

        // Check last log status
        $lastLog = VehicleLog::where('rfid_tag_id', $tagId)
            ->orderByDesc('timestamp')
            ->first();

        $status = 'out'; // Default
        if ($lastLog) {
            $status = ($lastLog->type === 'entry') ? 'in' : 'out';
        }

        $isExpired = ($vehicle->expiry_date && $vehicle->expiry_date->isPast()) || ($registration && $registration->status === 'expired');

        return response()->json([
            'success' => true,
            'data' => $registration,
            'vehicle' => $vehicle,
            'is_expired' => $isExpired,
            'expiry_date' => $vehicle->expiry_date ? $vehicle->expiry_date->format('F d, Y') : null,
            'current_status' => $status,
            'suggested_action' => ($status === 'in') ? 'exit' : 'entry'
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query)) {
            return response()->json([]);
        }

        // Search in VehicleRegistration (owners) and Vehicle tables
        $results = VehicleRegistration::with('vehicles')
            ->where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('plate_number', 'LIKE', "%{$query}%")
                  ->orWhere('university_id', 'LIKE', "%{$query}%")
                  ->orWhere('contact_number', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    public function logVehicle(Request $request) {
        $request->validate([
            'tagId' => 'required',
            'type' => 'required|in:entry,exit'
        ]);

        return $this->processLogging($request->tagId, $request->type);
    }

    /**
     * Manual Override: Virtual Scan via Plate Number
     */
    public function virtualScan(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20'
        ]);

        $plate = strtoupper($request->plate_number);
        $vehicle = Vehicle::where('plate_number', $plate)
            ->orWhere('plate_number', 'LIKE', '%' . $plate . '%')
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle with plate number ' . $plate . ' not found.'
            ], 404);
        }

        // Determine type based on last status
        $lastLog = VehicleLog::where('rfid_tag_id', $vehicle->rfid_tag)
            ->orderByDesc('timestamp')
            ->first();

        $type = ($lastLog && $lastLog->type === 'entry') ? 'exit' : 'entry';

        // Reuse the core logging logic
        return $this->processLogging($vehicle->rfid_tag, $type, true);
    }

    /**
     * Core logging logic shared by Hardware RFID and Virtual Scan
     */
    protected function processLogging($tagId, $type, $isManual = false)
    {
        // 5-second Duplicate Filter (Backend Protection) - Skip if manual override
        if (!$isManual) {
            $recentLog = VehicleLog::where('rfid_tag_id', $tagId)
                ->where('timestamp', '>=', now()->subSeconds(5))
                ->first();

            if ($recentLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate scan ignored (5s cooldown)',
                    'cooldown' => true
                ]);
            }
        }

        // L0. LOCKDOWN CHECK
        $lockdown = \Illuminate\Support\Facades\Cache::get('system_lockdown', ['active' => false]);
        if ($lockdown['active'] ?? false) {
            return response()->json([
                'success' => false,
                'message' => 'ACCESS DENIED: Emergency System Lockdown as of ' . now()->format('h:i A'),
                'lockdown' => true
            ], 403);
        }

        // C1. COOLDOWN CHECK (Skip if manual)
        if (!$isManual) {
            $cooldown = (int)SystemSetting::get('cooldown_interval', 3);
            $lastLog = VehicleLog::where('rfid_tag_id', $tagId)
                ->where('timestamp', '>', now()->subSeconds($cooldown))
                ->first();
            if ($lastLog) {
                return response()->json(['success' => false, 'message' => "SCAN BLOCKED: Cooldown active ({$cooldown}s)"], 429);
            }
        }

        // C2. STRICT LOGIC CHECK (Only for Entry)
        $logic = SystemSetting::get('tag_logic', 'flexible');
        if ($logic === 'strict' && $type === 'entry') {
             $lastEntryExit = VehicleLog::where('rfid_tag_id', $tagId)->orderByDesc('timestamp')->first();
             if ($lastEntryExit && $lastEntryExit->type === 'entry') {
                  return response()->json(['success' => false, 'message' => 'BLOCK: Vehicle is already INSIDE (Strict Logic)'], 400);
             }
        }

        $vehicle = Vehicle::where('rfid_tag', $tagId)->first();
        $registration = $vehicle ? $vehicle->owner : null;
        
        // 1. BLACKLIST CHECK: Hard block if registration is blacklisted/rejected
        $isBlacklisted = $registration && $registration->status === 'rejected';
        if ($isBlacklisted) {
            $this->recordActivity('BLACKLIST_ATTEMPT', "BLOCKED: Blacklisted tag [{$tagId}] attempted {$type} by {$registration->full_name}");
            return response()->json([
                'success'     => false,
                'message'     => 'ACCESS DENIED: This vehicle is BLACKLISTED. Do not allow entry.',
                'blacklisted' => true,
                'owner'       => $registration->full_name ?? 'Unknown',
                'plate'       => $vehicle?->plate_number ?? 'N/A',
            ], 403);
        }

        // 2. EXPIRY CHECK: Block entry if vehicle-specific tag or registration is expired
        $isVehicleExpired = $vehicle && $vehicle->expiry_date && $vehicle->expiry_date->isPast();
        $isRegistrationExpired = $registration && ($registration->status === 'expired' || ($registration->validity_to && $registration->validity_to->isPast()));

        if ($isVehicleExpired || $isRegistrationExpired) {
            $expiryMsg = $isVehicleExpired ? "DENIED: EXPIRED TAG (" . $vehicle->expiry_date->format('F d, Y') . ")" : "DENIED: EXPIRED REGISTRATION";
            return response()->json([
                'success' => false,
                'message' => $expiryMsg,
                'expired' => true,
                'owner' => $registration->full_name ?? 'Unknown'
            ], 403);
        }
        
        $log = VehicleLog::create([
            'vehicle_registration_id' => $registration ? $registration->id : null,
            'vehicle_id' => $vehicle ? $vehicle->id : null,
            'rfid_tag_id' => $tagId,
            'type' => $type,
            'timestamp' => now()
        ]);

        $info = $registration ? ($registration->full_name . " [{$tagId}] (" . ($vehicle->plate_number ?? 'No Plate') . ")") : "Unregistered tag [{$tagId}]";
        $actionType = $isManual ? 'MANUAL_OVERRIDE' : 'RFID_SCAN';
        $this->recordActivity($actionType, "Processed {$type} for {$info}");

        return response()->json([
            'success' => true,
            'message' => 'Vehicle logged as ' . $type . ($isManual ? ' (Manual Override)' : ''),
            'log' => $log->load('vehicleRegistration', 'vehicle'),
            'occupancy' => VehicleLog::dailyOccupancy()
        ]);
    }


    public function entry()
    {
        return view('guard.visitor-entry');
    }

    public function storeVisitor(Request $request)
    {
        $lockdown = \Illuminate\Support\Facades\Cache::get('system_lockdown', ['active' => false]);
        if ($lockdown['active'] ?? false) {
            return response()->json([
                'success' => false,
                'message' => 'Manual entry blocked: Emergency Lockdown Active'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'plate' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string',
            'purpose' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Visitor::create([
            'name' => $request->name,
            'plate' => $request->plate,
            'vehicle_type' => $request->vehicle_type,
            'purpose' => $request->purpose,
            'destination' => $request->destination,
            'time_in' => now(),
            'status' => 'inside',
        ]);

        $this->recordActivity('VISITOR_ENTRY', "Logged manual entry for visitor: {$request->name}");

        return redirect()->route('guard.dashboard')->with('success', 'Visitor entry logged successfully.');
    }

    public function exit()
    {
        $visitors = Visitor::where('status', 'inside')->orderByDesc('time_in')->get();
        return view('guard.visitor-exit', compact('visitors'));
    }

    public function exitVisitor($id)
    {
        $visitor = Visitor::findOrFail($id);
        $visitor->update([
            'status' => 'left',
            'time_out' => now(),
        ]);

        $this->recordActivity('VISITOR_EXIT', "Logged manual exit for visitor: {$visitor->name}");

        return redirect()->back()->with('success', 'Visitor exit logged successfully.');
    }

    public function toggleLogType($id) {
        $log = VehicleLog::findOrFail($id);
        $oldType = $log->type;
        $newType = ($oldType === 'entry') ? 'exit' : 'entry';
        
        $log->update(['type' => $newType]);
        
        $this->recordActivity('LOG_CORRECTION', "Manually toggled log #{$id} from {$oldType} to {$newType}");

        return response()->json([
            'success' => true,
            'new_type' => $newType
        ]);
    }

    public function analytics(Request $request)
    {
        $range = $request->query('range', 'today');
        $from = $request->query('from');
        $to = $request->query('to');
        
        $data = $this->getAnalyticsInternal($range, $from, $to);
        $overstaying = $this->getOverstaying();
        
        return view('guard.analytics', array_merge($data, [
            'overstaying' => $overstaying,
            'currentRange' => $range,
            'fromDate' => $from,
            'toDate' => $to,
            'title' => 'Traffic Analytics Hub',
            'subtitle' => 'Detailed flow analysis and security overstay reports.'
        ]));
    }

    public function fetchAnalyticsData(Request $request)
    {
        $range = $request->query('range', 'today');
        $from = $request->query('from');
        $to = $request->query('to');
        return response()->json($this->getAnalyticsInternal($range, $from, $to));
    }

    protected function getAnalyticsInternal($range, $from = null, $to = null)
    {
        $labels = [];
        $entries = [];
        $exits = [];
        
        $startTime = now()->startOfDay();
        $endTime = now()->endOfDay();
        $format = 'h A';
        $interval = 'hour';

        if ($from && $to) {
            $range = 'custom';
            $startTime = \Carbon\Carbon::parse($from)->startOfDay();
            $endTime = \Carbon\Carbon::parse($to)->endOfDay();
            $diffInDays = $startTime->diffInDays($endTime);
            if ($diffInDays > 1) {
                $format = 'M d';
                $interval = 'day';
            }
        } else {
            if ($range === '12h') {
                $startTime = now()->subHours(11)->startOfHour();
                $endTime = now();
            } elseif ($range === '7d') {
                $startTime = now()->subDays(6)->startOfDay();
                $endTime = now()->endOfDay();
                $format = 'M d';
                $interval = 'day';
            }
        }

        if ($interval === 'hour') {
            $diff = (int)$startTime->diffInHours($endTime);
            for ($i = 0; $i <= $diff; $i++) {
                $time = $startTime->copy()->addHours($i);
                $labels[] = $time->format($format);
                
                $entries[] = (int)VehicleLog::where('type', 'entry')->whereBetween('timestamp', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])->count() +
                             (int)Visitor::whereBetween('time_in', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])->count();
                
                $exits[] = (int)VehicleLog::where('type', 'exit')->whereBetween('timestamp', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])->count() +
                           (int)Visitor::whereBetween('time_out', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])->count();
            }
        } else {
            $diff = (int)$startTime->diffInDays($endTime);
             for ($i = 0; $i <= $diff; $i++) {
                $time = $startTime->copy()->addDays($i);
                $labels[] = $time->format($format);
                
                $entries[] = (int)VehicleLog::where('type', 'entry')->whereDate('timestamp', $time)->count() +
                             (int)Visitor::whereDate('time_in', $time)->count();
                
                $exits[] = (int)VehicleLog::where('type', 'exit')->whereDate('timestamp', $time)->count() +
                           (int)Visitor::whereDate('time_out', $time)->count();
            }
        }

        // Peak Hours Summary
        $peakHoursRaw = VehicleLog::select(\DB::raw('strftime("%H", timestamp) as hr'), \DB::raw('count(*) as count'))
            ->whereBetween('timestamp', [$startTime, $endTime])
            ->groupBy('hr')->orderByDesc('count')->limit(5)->get();
        
        $peakHours = $peakHoursRaw->map(fn($p) => [
            'hour' => \Carbon\Carbon::createFromTime((int)$p->hr, 0, 0)->format('g:i A'),
            'count' => (int)$p->count
        ]);

        return [
            'labels' => $labels,
            'entries' => $entries,
            'exits' => $exits,
            'peakHours' => $peakHours,
            'summary' => [
                'total_entries' => array_sum($entries),
                'total_exits' => array_sum($exits),
                'range_text' => $startTime->format('M d, Y') . ($range !== 'today' ? ' - ' . $endTime->format('M d, Y') : '')
            ]
        ];
    }

    protected function getOverstaying()
    {
        return VehicleLog::with(['vehicleRegistration', 'vehicle'])
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('vehicle_logs')
                    ->groupBy('rfid_tag_id');
            })
            ->where('type', 'entry')
            ->where('timestamp', '<', now()->subHours(12))
            ->get();
    }

    public function visitorAnalytics(Request $request)
    {
        $range = $request->query('range', '30d');
        
        // 1. Statistics Cards
        $startOfMonth = now()->startOfMonth();
        $totalUniqueMonth = Visitor::where('time_in', '>=', $startOfMonth)
            ->distinct('name')
            ->count('name');

        $allVisitors = Visitor::all();
        $visitorCounts = $allVisitors->groupBy('name')->map->count();
        $totalVisitorsCount = $visitorCounts->count();
        $returningCount = $visitorCounts->filter(fn($c) => $c > 1)->count();
        $returnRate = $totalVisitorsCount > 0 ? round(($returningCount / $totalVisitorsCount) * 100, 1) : 0;

        $daysOfWeek = ['Sundays', 'Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays'];
        $busiestDayRaw = Visitor::select(DB::raw('strftime("%w", time_in) as day_offset'), DB::raw('count(*) as count'))
            ->groupBy('day_offset')
            ->orderByDesc('count')
            ->first();
        $busiestDay = $busiestDayRaw ? $daysOfWeek[(int)$busiestDayRaw->day_offset] : 'N/A';

        // 2. Trend Data based on Range
        $trendLabels = [];
        $trendData = [];
        
        if ($range === 'daily') {
            // Last 24 Hours (Hourly resolution)
            for ($i = 23; $i >= 0; $i--) {
                $time = now()->subHours($i);
                $trendLabels[] = $time->format('h A');
                $trendData[] = Visitor::whereBetween('time_in', [$time->copy()->startOfHour(), $time->copy()->endOfHour()])->count();
            }
        } elseif ($range === 'custom' && $request->has('start') && $request->has('end')) {
            // Custom Range (Daily resolution)
            $start = Carbon::parse($request->start)->startOfDay();
            $end = Carbon::parse($request->end)->endOfDay();
            $diffInDays = (int)$start->diffInDays($end);
            
            for ($i = 0; $i <= $diffInDays; $i++) {
                $date = $start->copy()->addDays($i);
                $trendLabels[] = $date->format('M d');
                $trendData[] = Visitor::whereDate('time_in', $date)->count();
            }
        } else {
            // Weekly (7d) or Monthly (30d) (Daily resolution)
            $limit = ($range === '7d') ? 7 : 30;
            for ($i = $limit - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $trendLabels[] = $date->format('M d');
                $trendData[] = Visitor::whereDate('time_in', $date)->count();
            }
        }

        // 3. Peak Arrival Hours (All-time or range?)
        // Let's do all-time for better "heat" logic
        $peakHoursRaw = Visitor::select(DB::raw('strftime("%H", time_in) as hr'), DB::raw('count(*) as count'))
            ->groupBy('hr')
            ->orderBy('hr')
            ->get();
        
        $peakHoursLabels = [];
        $peakHoursData = [];
        for ($h = 0; $h < 24; $h++) {
            $hStr = sprintf('%02d', $h);
            $peakHoursLabels[] = Carbon::createFromTime($h, 0, 0)->format('g A');
            $match = $peakHoursRaw->firstWhere('hr', $hStr);
            $peakHoursData[] = $match ? $match->count : 0;
        }

        // 4. Top 10 Recurring
        $topVisitors = Visitor::select('name', 'plate', 'purpose', DB::raw('count(*) as count'))
            ->groupBy('name', 'plate', 'purpose')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'trendLabels' => $trendLabels,
                'trendData' => $trendData,
                'peakHoursLabels' => $peakHoursLabels,
                'peakHoursData' => $peakHoursData
            ]);
        }

        return view('guard.visitor-analytics', compact(
            'totalUniqueMonth', 'returnRate', 'busiestDay',
            'trendLabels', 'trendData',
            'peakHoursLabels', 'peakHoursData',
            'topVisitors', 'range'
        ));
    }

    private function recordActivity($action, $details)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip()
        ]);
    }

    public function checkLockdown()
    {
        $status = \Illuminate\Support\Facades\Cache::get('system_lockdown', ['active' => false, 'reason' => '']);
        $ticker = \App\Models\SystemSetting::get('guard_ticker', 'Welcome to EVSU.');
        
        return response()->json([
            'active' => $status['active'] ?? false,
            'reason' => $status['reason'] ?? '',
            'ticker' => $ticker
        ]);
    }

    public function showDetails($id)
    {
        $log = VehicleLog::with(['vehicleRegistration', 'vehicle'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'timestamp' => $log->timestamp->format('F d, Y h:i:s A'),
                'type' => strtoupper($log->type),
                'owner' => $log->vehicleRegistration ? $log->vehicleRegistration->full_name : 'Visitor/Unregistered',
                'plate' => $log->vehicle ? $log->vehicle->plate_number : ($log->vehicleRegistration ? $log->vehicleRegistration->plate_number : 'N/A'),
                'vehicle_details' => $log->vehicle ? $log->vehicle->vehicle_details : ($log->vehicleRegistration ? ($log->vehicleRegistration->make_brand . ' ' . $log->vehicleRegistration->model_name) : 'N/A'),
                'rfid_tag' => $log->rfid_tag_id ?? 'N/A',
                'vehicle_type' => $log->vehicle ? $log->vehicle->vehicle_type : ($log->vehicleRegistration ? $log->vehicleRegistration->vehicle_type : 'N/A'),
            ]
        ]);
    }
}
