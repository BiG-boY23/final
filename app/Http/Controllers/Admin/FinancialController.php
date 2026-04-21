<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleRegistration;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function pendingPayments(Request $request)
    {
        $query = VehicleRegistration::where('status', '!=', 'rejected')
            ->with(['payments', 'vehicles']);

        // Filter by Program (Course)
        if ($request->filled('program')) {
            $programStr = $request->program;
            $query->where(function($q) use ($programStr) {
                $q->where('course', 'like', "%{$programStr}%");
                
                // Extract code from parentheses if it exists, eg: "Bachelor of Science in Information Technology (BSIT)"
                if (preg_match('/\(([^)]+)\)/', $programStr, $matches)) {
                    $code = $matches[1];
                    $q->orWhere('course', 'like', "%{$code}%");
                }
            });
        }

        // Filter by Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('university_id', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhereHas('vehicles', function($v) use ($search) {
                      $v->where('plate_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            if ($request->status === 'PAID') {
                $query->whereNotNull('rfid_tag_id');
            } elseif ($request->status === 'UNPAID') {
                $query->whereNull('rfid_tag_id');
            }
        }

        // Sort by
        if ($request->sort === 'alphabetical') {
            $query->orderBy('full_name', 'asc');
        } elseif ($request->sort === 'date_asc') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $registrations = $query->get();
        $rfid_fee = \App\Models\SystemSetting::get('rfid_fee', 100);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.manage.payments.partials._pending_table_rows', compact('registrations', 'rfid_fee'))->render(),
                'unpaid_count' => $registrations->whereNull('rfid_tag_id')->count(),
                'total_count' => $registrations->count()
            ]);
        }

        $courses = \App\Models\Course::orderBy('name')->get();
        $unpaidWaiting = VehicleRegistration::where('status', '!=', 'rejected')->whereNull('rfid_tag_id')->count();

        return view('admin.manage.payments.pending', compact('registrations', 'rfid_fee', 'courses', 'unpaidWaiting'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'vehicle_registration_id' => 'required|exists:vehicle_registrations,id',
            'or_number' => 'required|string|unique:payments,or_number',
            'amount' => 'required|numeric|min:0',
            'rfid_tag' => 'required|string'
        ]);

        DB::transaction(function() use ($request) {
            $reg = VehicleRegistration::findOrFail($request->vehicle_registration_id);

            // 1. Record Payment
            Payment::create([
                'vehicle_registration_id' => $reg->id,
                'or_number' => $request->or_number,
                'amount' => $request->amount,
                'paid_at' => now(),
            ]);

            $validityPeriod = (int)\App\Models\SystemSetting::get('validity_period', 1);
            $validityTo = now()->addYears($validityPeriod);

            // 2. Update Registration Status & Set Validity
            $reg->update([
                'status' => 'ACTIVE',
                'rfid_tag_id' => $request->rfid_tag,
                'validity_from' => now(),
                'validity_to' => $validityTo,
            ]);

            // 3. Ensure Vehicle record exists/updated
            Vehicle::updateOrCreate(
                ['plate_number' => $reg->plate_number],
                [
                    'user_id' => $reg->id, // Logic from vehicles migration: links to registrations.id
                    'vehicle_details' => trim(($reg->make_brand ?? '') . ' ' . ($reg->model_year ?? '')),
                    'rfid_tag' => $request->rfid_tag,
                    'expiry_date' => $validityTo,
                    'vehicle_type' => $reg->vehicle_type,
                ]
            );
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Payment processed and RFID issued successfully.']);
        }

        return back()->with('success', 'Payment processed and RFID issued successfully.');
    }

    public function financialLedger(Request $request)
    {
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->toDateString());
        $rfid_fee = \App\Models\SystemSetting::get('rfid_fee', 100);

        $issuanceRecords = [];
        $totalCollections = 0;
        $transactionCount = 0;

        // 1. Get all documented payments in range
        $payments = \App\Models\Payment::with('registration')
            ->whereBetween('paid_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->orderByDesc('paid_at')
            ->get();

        $processedRegIds = [];

        $dailyStats = [];
        $current = \Carbon\Carbon::parse($start);
        $last = \Carbon\Carbon::parse($end);
        while ($current <= $last) {
            $dailyStats[$current->format('M d')] = 0;
            $current->addDay();
        }

        foreach ($payments as $pay) {
            if (!$pay->registration) continue;
            
            $issuanceRecords[] = [
                'date' => $pay->paid_at->format('M d, Y'),
                'name' => $pay->registration->full_name,
                'tag' => $pay->registration->rfid_tag_id,
                'amount' => $pay->amount,
                'role' => $pay->registration->role,
                'id' => $pay->registration->id
            ];
            $totalCollections += $pay->amount;
            $transactionCount++;
            $processedRegIds[] = $pay->vehicle_registration_id;

            $dayKey = $pay->paid_at->format('M d');
            if (isset($dailyStats[$dayKey])) {
                $dailyStats[$dayKey] += $pay->amount;
            }
        }

        // 2. Get legacy/manual issuances (Records with tags that were updated in range but have NO payment record in range)
        $legacy = VehicleRegistration::whereNotNull('rfid_tag_id')
            ->whereBetween('updated_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->whereNotIn('id', $processedRegIds)
            ->get();

        foreach ($legacy as $reg) {
            $issuanceRecords[] = [
                'date' => $reg->updated_at->format('M d, Y'),
                'name' => $reg->full_name,
                'tag' => $reg->rfid_tag_id,
                'amount' => (float)$rfid_fee,
                'role' => $reg->role,
                'id' => $reg->id
            ];
            $totalCollections += (float)$rfid_fee;
            $transactionCount++;
            
            $dayKey = $reg->updated_at->format('M d');
            if (isset($dailyStats[$dayKey])) {
                $dailyStats[$dayKey] += (float)$rfid_fee;
            }
        }

        // 3. Statistical Breakdown by Role
        $roleBreakdown = [];
        foreach ($issuanceRecords as $rec) {
            $r = strtolower($rec['role'] ?? 'other');
            if (!isset($roleBreakdown[$r])) {
                $roleBreakdown[$r] = ['label' => ucfirst(str_replace('_', ' ', $r)), 'count' => 0, 'total' => 0];
            }
            $roleBreakdown[$r]['count']++;
            $roleBreakdown[$r]['total'] += $rec['amount'];
        }

        // Sort combined records by date desc
        usort($issuanceRecords, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Projected Revenue (only verified who are NOT active)
        $pendingCount = VehicleRegistration::whereNull('rfid_tag_id')
            ->where('status', 'verified')
            ->count();
        $projectedRevenue = $pendingCount * $rfid_fee;

        // For the user, "Average Fee" means the Current System Fee
        $summary = [
            'totalRevenue' => $totalCollections,
            'transactionCount' => $transactionCount,
            'avgFee' => (float)$rfid_fee, // Show current price instead of math average
            'projectedRevenue' => $projectedRevenue,
            'roleBreakdown' => $roleBreakdown,
            'dailyStats' => $dailyStats
        ];

        // Top 10 Payors
        $topPayors = VehicleRegistration::whereNotNull('rfid_tag_id')
            ->with('payments')
            ->get()
            ->map(function($r) use ($rfid_fee) {
                $pTotal = $r->payments->sum('amount');
                // If NO recorded payment, assume at least the current fee (legacy handling)
                if ($pTotal <= 0) $pTotal = (float)$rfid_fee; 
                $r->calculated_total = $pTotal;
                return $r;
            })
            ->sortByDesc('calculated_total')
            ->take(10);

        return view('admin.manage.payments.ledger', compact(
            'issuanceRecords', 
            'totalCollections', 
            'transactionCount', 
            'summary', 
            'topPayors',
            'start',
            'end'
        ));
    }
}
