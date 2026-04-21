<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\VehicleRegistration;
use App\Models\Vehicle;
use App\Models\VehicleLog;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = VehicleRegistration::count();
        $registeredToday = VehicleRegistration::whereDate('created_at', Carbon::today())->count();
        $activeVehicles = Vehicle::count();
        $pendingRegistrations = VehicleRegistration::where('status', 'pending')->count();
        
        $totalCapacity = (int)\App\Models\SystemSetting::get('total_parking_slots', 200);
        $currentOccupancy = VehicleLog::dailyOccupancy();

        // Quick summary by role (student, faculty, staff)
        $roleCounts = VehicleRegistration::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $summary = [
            'student' => [
                'label' => 'Students',
                'count' => $roleCounts['student'] ?? 0,
            ],
            'faculty' => [
                'label' => 'Personnel',
                'count' => $roleCounts['faculty'] ?? 0,
            ],
            'staff' => [
                'label' => 'Vendor',
                'count' => $roleCounts['staff'] ?? 0,
            ],
        ];

        // Compute percentages safely
        foreach ($summary as $key => $item) {
            $summary[$key]['percent'] = $totalUsers > 0
                ? round(($item['count'] / $totalUsers) * 100)
                : 0;
        }

        return view('office.dashboard', compact('totalUsers', 'registeredToday', 'activeVehicles', 'summary', 'pendingRegistrations', 'totalCapacity', 'currentOccupancy'));
    }

    public function registration(Request $request)
    {
        $registration = null;
        if ($request->has('id')) {
            $registration = VehicleRegistration::with('vehicles')->findOrFail($request->id);
        }
        $brands = \App\Models\VehicleBrand::with('models')->orderBy('name')->get();
        $categories = \App\Models\VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $colleges = \App\Models\College::where('category', 'academic')->with('courses')->orderBy('name')->get();
        $offices = \App\Models\College::where('category', 'administrative')->orderBy('name')->get();
        
        return view('office.registration', compact('registration', 'brands', 'categories', 'colleges', 'offices'));
    }

    public function store(Request $request)
    {
        // 1. Bridge multi-vehicle data to root fields for standard validation
        if (!$request->has('plate_number') && $request->has('vehicles')) {
            $first = $request->vehicles[0];
            $request->merge([
                'vehicle_type' => $request->vehicle_type ?: ($first['vehicle_type'] ?? null),
                'make_brand'   => $request->make_brand   ?: ($first['make_brand'] ?? null),
                'model_name'   => $request->model_name   ?: ($first['model_name'] ?? null),
                'plate_number' => $request->plate_number ?: ($first['plate_number'] ?? null),
                'rfid_tag_id'  => $request->rfid_tag_id  ?: ($first['rfid_tag'] ?? null),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,faculty,staff',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'contact_number' => $request->role === 'faculty' ? 'nullable|string|max:20' : 'required|string|max:20',
            'email_address' => 'nullable|email|max:255',
            'university_id' => $request->role === 'faculty' ? 'nullable|string|max:255' : ($request->role === 'student' ? 'required|string|max:255' : 'nullable'),
            'vehicle_type' => 'required|string|max:100', 
            'make_brand' => 'required|string|max:255',
            'model_name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:20',
            'validity_from' => 'nullable|date',
            'validity_to' => 'nullable|date',
            'rfid_tag_id' => [
                'required',
                'string',
                'unique:vehicle_registrations,rfid_tag_id',
                'unique:vehicles,rfid_tag'
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax() || $request->hasHeader('X-Requested-With')) {
                // Get the first specific error message
                $firstError = $validator->errors()->first();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error: ' . $firstError,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
        
        // Extract vehicles info from array or single fields (fallback)
        $vehicles = $request->vehicles ?? [[
            'vehicle_type' => $request->vehicle_type,
            'make_brand'   => $request->make_brand,
            'model_name'   => $request->model_name,
            'plate_number' => $request->plate_number,
            'rfid_tag'     => $request->rfid_tag_id,
        ]];

        $firstVehicle = $vehicles[0] ?? [];

            $vPeriod = (int)\App\Models\SystemSetting::get('validity_period', 1);
            $data = [
                'role'              => $request->role,
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'middle_name'       => $request->middle_name,
                'full_name'         => $fullName,
                'contact_number'    => $request->contact_number,
                'email_address'     => $request->email_address ?? 'N/A',
                'vehicle_type'      => $firstVehicle['vehicle_type'] ?? 'N/A',
                'make_brand'        => $firstVehicle['make_brand'] ?? 'N/A',
                'model_name'        => $firstVehicle['model_name'] ?? 'N/A',
                'plate_number'      => $firstVehicle['plate_number'] ?? 'N/A',
                'registered_owner'  => $request->registered_owner ?? $fullName,
                'validity_from'     => $request->validity_from ?: now()->toDateString(),
                'validity_to'       => $request->validity_to ?: now()->addYears($vPeriod)->toDateString(),
                'rfid_tag_id'       => $firstVehicle['rfid_tag'] ?? null,
                'status'            => 'approved',
                'office_user_id'    => Auth::id(),
                'sticker_classification' => [$request->role],
            ];

        // Role-based specific mappings
        if ($request->role === 'student') {
            $data['university_id'] = $request->university_id;
            $data['course'] = $request->course;
            $data['college_dept'] = $request->college_dept;
            $data['year_level'] = $request->year_level;
        } elseif ($request->role === 'faculty') {
            $data['university_id'] = $request->university_id;
            $data['college_dept'] = $request->college_dept;
            $data['office'] = $request->college_dept; // Map dept to office for faculty
        } elseif ($request->role === 'staff') {
            $data['business_stall_name'] = $request->business_stall_name;
            $data['vendor_address'] = $request->vendor_address;
            $data['university_id'] = 'N/A';
        }

        $registration = VehicleRegistration::create($data);

        // Process all vehicles in the array
        foreach ($vehicles as $vData) {
            if (empty($vData['plate_number'])) continue;

            // 1. Create Vehicle record (Gate Access)
            \App\Models\Vehicle::create([
                'user_id'         => $registration->id,
                'plate_number'    => strtoupper($vData['plate_number']),
                'vehicle_details' => trim(($vData['make_brand'] ?? '') . ' ' . ($vData['model_name'] ?? '')),
                'vehicle_type'    => $vData['vehicle_type'],
                'rfid_tag'        => $vData['rfid_tag'],
                'expiry_date'     => $registration->validity_to,
            ]);

            // 2. Record payment if tag is assigned
            if (!empty($vData['rfid_tag'])) {
                $rfid_fee = (float)\App\Models\SystemSetting::get('rfid_fee', 100);
                \App\Models\Payment::create([
                    'vehicle_registration_id' => $registration->id,
                    'amount' => $rfid_fee,
                    'or_number' => 'REG-' . strtoupper(bin2hex(random_bytes(4))),
                    'paid_at' => now()
                ]);
            }
        }

        // Mark as ACTIVE
        $registration->update(['status' => 'ACTIVE']);

        if ($request->expectsJson() || $request->ajax() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => true,
                'message' => 'Registration completed successfully and is now active.'
            ]);
        }

        return redirect()->route('office.registration')
            ->with('success', 'Registration completed successfully and is now active.');
    }

    public function users()
    {
        $registrations = VehicleRegistration::with(['vehicles', 'payments'])->orderByDesc('created_at')->get();
        
        $totalUsers = $registrations->count();
        $activeTags = $registrations->whereNotNull('rfid_tag_id')->count();
        $pendingReg = $registrations->where('status', 'pending')->count();
        $verifiedReg = $registrations->where('status', 'verified')->whereNull('rfid_tag_id')->count();

        $roleCounts = $registrations->groupBy('role')->map->count();
        $summary = [
            'student' => ['label' => 'Students', 'count' => $roleCounts['student'] ?? 0],
            'faculty' => ['label' => 'Personnel', 'count' => $roleCounts['faculty'] ?? 0],
            'staff' => ['label' => 'Vendor', 'count' => $roleCounts['staff'] ?? 0],
        ];
        foreach ($summary as $key => $item) {
            $summary[$key]['percent'] = $totalUsers > 0 ? round(($item['count'] / $totalUsers) * 100) : 0;
        }

        $brands = \App\Models\VehicleBrand::with('models')->orderBy('name')->get();
        $categories = \App\Models\VehicleCategory::where('is_active', true)->orderBy('name')->get();

        return view('office.users', compact('registrations', 'totalUsers', 'activeTags', 'pendingReg', 'verifiedReg', 'summary', 'brands', 'categories'));
    }

    /**
     * Show a single registration (JSON).
     */
    public function show($id)
    {
        $registration = VehicleRegistration::findOrFail($id);
        return response()->json(['success' => true, 'data' => $registration]);
    }

    public function fetchUserByUnivId($searchValue)
    {
        // 1. Try to find by University ID
        $registration = VehicleRegistration::with('vehicles')
            ->where('university_id', $searchValue)
            ->latest()
            ->first();

        // 2. If not found, try to find by Plate Number
        if (!$registration) {
            $vehicle = \App\Models\Vehicle::where('plate_number', $searchValue)->first();
            if ($vehicle) {
                $registration = VehicleRegistration::with('vehicles')
                    ->where('id', $vehicle->user_id)
                    ->latest()
                    ->first();
            }
        }

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'No prior registration found for this ID or Plate Number.'
            ], 404);
        }

        // If we have an owner, we want to fetch ALL vehicles linked to them.
        // We can find them either by university_id (if exists) or just by the owner id records.
        $allVehicles = collect();
        if ($registration->university_id && $registration->university_id !== 'N/A') {
            $allRegIds = VehicleRegistration::where('university_id', $registration->university_id)->pluck('id');
            $allVehicles = \App\Models\Vehicle::whereIn('user_id', $allRegIds)->get();
        } else {
            // Find by owner name matching? No, let's just stick to the specific owner's records we found
            // Actually, if they have no university_id, we just fetch vehicles matching the same owner name/details or just this registration's vehicles.
            // For now, let's fetch all vehicles across all registrations with the same full_name and contact_number
            $allRegIds = VehicleRegistration::where('full_name', $registration->full_name)
                ->where('contact_number', $registration->contact_number)
                ->pluck('id');
            $allVehicles = \App\Models\Vehicle::whereIn('user_id', $allRegIds)->get();
        }

        $registration->setRelation('vehicles', $allVehicles);

        return response()->json([
            'success' => true,
            'data'    => $registration,
        ]);
    }

    /**
     * Update a registration using the same fields as create.
     */
    public function update(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        // 1. Bridge multi-vehicle data to root fields for standard validation
        if (!$request->has('plate_number') && $request->has('vehicles')) {
            $first = $request->vehicles[0];
            $request->merge([
                'vehicle_type' => $request->vehicle_type ?? ($first['vehicle_type'] ?? null),
                'make_brand'   => $request->make_brand   ?? ($first['make_brand'] ?? null),
                'model_name'   => $request->model_name   ?? ($first['model_name'] ?? null),
                'plate_number' => $request->plate_number ?? ($first['plate_number'] ?? null),
                'rfid_tag_id'  => $request->rfid_tag_id  ?? ($first['rfid_tag'] ?? null),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,faculty,staff',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'full_name' => 'nullable|string|max:255',
            'university_id' => 'nullable|string|max:255',
            'college_dept' => 'nullable|string|max:255',
            'contact_number' => $request->role === 'faculty' ? 'nullable|string|max:20' : 'required|string|max:20',
            'email_address' => 'nullable|email|max:255',
            'vehicle_type' => 'nullable|string|max:100', 
            'make_brand' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:20',
            'validity_from' => 'nullable|date',
            'validity_to' => 'nullable|date',
            'rfid_tag_id' => [
                'nullable',
                'string',
                'unique:vehicle_registrations,rfid_tag_id,' . $id,
                function($attribute, $value, $fail) use ($id) {
                    // Check vehicles table, excluding vehicles owned by this registration
                    $exists = \App\Models\Vehicle::where('rfid_tag', $value)
                        ->where('user_id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('The selected RFID tag is already assigned to another vehicle.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $fullName = $request->full_name;
        if ($request->has('first_name') && $request->has('last_name')) {
            $fullName = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->last_name);
            $fullName = preg_replace('/\s+/', ' ', $fullName);
        }

        // Extract vehicles from array or root fallback
        $vehicles = $request->vehicles ?? [[
            'id'           => $registration->vehicles()->first()->id ?? null,
            'vehicle_type' => $request->vehicle_type,
            'make_brand'   => $request->make_brand,
            'model_name'   => $request->model_name,
            'plate_number' => $request->plate_number,
            'rfid_tag'     => $request->rfid_tag_id,
        ]];
        
        $firstVehicle = $vehicles[0] ?? [];

        $vPeriod = (int)\App\Models\SystemSetting::get('validity_period', 1);
        $data = [
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'full_name' => $fullName ?? $registration->full_name,
            'university_id' => $request->university_id ?? 'N/A',
            'college_dept' => $request->college_dept ?? 'N/A',
            'contact_number' => $request->contact_number,
            'email_address' => $request->email_address ?? 'N/A',
            'course' => $request->course,
            'year_level' => $request->year_level,
            'rank' => $request->rank,
            'office' => $request->office,
            'business_stall_name' => $request->business_stall_name,
            'vendor_address' => $request->vendor_address,
            'vehicle_type' => $firstVehicle['vehicle_type'] ?? $registration->vehicle_type,
            'registered_owner' => $request->registered_owner ?? 'N/A',
            'make_brand' => $firstVehicle['make_brand'] ?? $registration->make_brand,
            'model_name' => $firstVehicle['model_name'] ?? $registration->model_name,
            'model_year' => $request->model_year ?? 'N/A',
            'color' => $request->color ?? 'N/A',
            'plate_number' => $firstVehicle['plate_number'] ?? $registration->plate_number,
            'engine_number' => $request->engine_number ?? 'N/A',
            'sticker_classification' => $request->stickerClassification ?? [],
            'requirements' => $request->requirements ?? [],
            'validity_from' => $request->validity_from ?: now()->toDateString(),
            'validity_to' => $request->validity_to ?: now()->addYears($vPeriod)->toDateString(),
            'rfid_tag_id' => $firstVehicle['rfid_tag'] ?? $registration->rfid_tag_id,
            'status' => ($registration->status === 'expired' && Carbon::parse($request->validity_to)->isFuture()) ? 'approved' : $registration->status,
            'office_user_id' => Auth::id(),
        ];

        $wasTagged = !empty($registration->rfid_tag_id);
        $registration->update($data);
        $isTagged = !empty($registration->rfid_tag_id);

        // SYNC Multi-Vehicles
        $submittedIds = [];
        foreach ($vehicles as $vData) {
            if (empty($vData['plate_number'])) continue;

            $vRecord = null;
            if (!empty($vData['id'])) {
                $vRecord = \App\Models\Vehicle::find($vData['id']);
            }

            $updateData = [
                'user_id'         => $registration->id,
                'plate_number'    => strtoupper($vData['plate_number']),
                'vehicle_details' => trim(($vData['make_brand'] ?? '') . ' ' . ($vData['model_name'] ?? '')),
                'vehicle_type'    => $vData['vehicle_type'],
                'rfid_tag'        => $vData['rfid_tag'],
                'expiry_date'     => $registration->validity_to,
            ];

            if ($vRecord) {
                $vRecord->update($updateData);
                $submittedIds[] = $vRecord->id;
            } else {
                $newV = \App\Models\Vehicle::create($updateData);
                $submittedIds[] = $newV->id;
            }
        }

        // Cleanup: Delete vehicles that were NOT in the submission (removed by user in UI)
        // We find all registration IDs linked to this owner to ensure we clean up orphans correctly
        $relatedRegIds = [$registration->id];
        if ($registration->university_id && $registration->university_id !== 'N/A') {
            $relatedRegIds = \App\Models\VehicleRegistration::where('university_id', $registration->university_id)->pluck('id')->toArray();
        } else {
            $relatedRegIds = \App\Models\VehicleRegistration::where('full_name', $registration->full_name)
                ->where('contact_number', $registration->contact_number)
                ->pluck('id')->toArray();
        }

        \App\Models\Vehicle::whereIn('user_id', $relatedRegIds)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        // Record payment for any NEW tags found (simplified: track if count increased or tag changed)
        if (!$wasTagged && $isTagged) {
            $rfid_fee = (float)\App\Models\SystemSetting::get('rfid_fee', 100);
            \App\Models\Payment::create([
                'vehicle_registration_id' => $registration->id,
                'amount' => $rfid_fee,
                'or_number' => 'RENEW-' . strtoupper(bin2hex(random_bytes(4))),
                'paid_at' => now()
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration updated and gate access synchronized.',
                'data' => $registration,
            ]);
        }

        return redirect()->route('office.registration', ['id' => $registration->id])
            ->with('success', 'Registration updated and gate access synchronized.');
    }

    /**
     * One-Click Renewal Logic from Stats Dashboard
     */
    public function renewTag(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);
        
        $request->validate([
            'new_expiry' => 'nullable|date'
        ]);

        $currentExpiry = Carbon::parse($registration->validity_to);
        
        // If provided, use manual date; otherwise, add 1 year to current expiry (or now if already expired)
        if ($request->new_expiry) {
            $newExpiryDate = Carbon::parse($request->new_expiry);
        } else {
            $baseDate = $currentExpiry->isFuture() ? $currentExpiry : now();
            $newExpiryDate = $baseDate->addYear();
        }

        // 1. Update Registration Record
        $registration->update([
            'validity_to' => $newExpiryDate->toDateString(),
            'status'      => 'ACTIVE'
        ]);

        // 2. Sync Vehicle Records (Gate Hardware Logic)
        \App\Models\Vehicle::where('user_id', $registration->id)->update([
            'expiry_date' => $newExpiryDate->toDateString()
        ]);

        // 3. Record Renewal Payment
        $renewalFee = (float)\App\Models\SystemSetting::get('rfid_fee', 100);
        \App\Models\Payment::create([
            'vehicle_registration_id' => $registration->id,
            'amount' => $renewalFee,
            'or_number' => 'RENEW-' . strtoupper(bin2hex(random_bytes(4))),
            'paid_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration renewed and gate access synchronized successfully!',
            'new_date' => $newExpiryDate->format('M d, Y')
        ]);
    }

    /**
     * Delete a registration.
     */
    public function destroy($id)
    {
        $registration = VehicleRegistration::findOrFail($id);
        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Registration deleted.',
        ]);
    }

    public function stats()
    {
        // 1. Total Entries and Exits
        $totalEntries = VehicleLog::where('type', 'entry')->count();
        $totalExits = VehicleLog::where('type', 'exit')->count();

        // 2. Peak Hour
        $driver = \DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite' ? "strftime('%H', timestamp)" : "HOUR(timestamp)";

        $peakMatch = VehicleLog::selectRaw("$hourExpr as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        $peakHour = 'N/A';
        if ($peakMatch) {
            $peakHour = Carbon::createFromTime($peakMatch->hour, 0)->format('h:i A');
        }

        // 3. Monthly Registration Trends (Last 6 Months)
        $months = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $count = VehicleRegistration::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            
            $months[] = $monthName;
            $counts[] = $count;
        }

        return view('office.stats', compact('totalEntries', 'totalExits', 'peakHour', 'months', 'counts'));
    }

    public function checkTag(Request $request)
    {
        $tagId = $request->query('tagId');
        $excludeId = $request->query('excludeId');
        
        $query = VehicleRegistration::where('rfid_tag_id', $tagId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $registration = $query->first();

        if ($registration) {
            return response()->json([
                'exists' => true,
                'message' => 'This tag is already assigned to ' . $registration->full_name . '.',
                'owner' => $registration->full_name,
                'registration_id' => $registration->id
            ]);
        }

        return response()->json(['exists' => false]);
    }
    public function verify($id)
    {
        return \DB::transaction(function () use ($id) {
            $registration = VehicleRegistration::lockForUpdate()->findOrFail($id);
            
            // If already verified, we allow it to proceed to re-send the email if needed, 
            // but we stop if it's rejected or something else.
            if ($registration->status !== 'pending' && $registration->status !== 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'This registration is already ' . $registration->status . '.'
                ], 400);
            }

            if ($registration->status === 'pending') {
                $registration->update(['status' => 'verified']);
                
                // Log the review
                \App\Models\RegistrationReview::create([
                    'vehicle_registration_id' => $registration->id,
                    'admin_id' => Auth::id(),
                    'action' => 'approved',
                    'admin_notes' => 'Requirements verified and approved.',
                    'reviewed_at' => now(),
                ]);
            }
     
            // Send Email
            if ($registration->email_address && filter_var($registration->email_address, FILTER_VALIDATE_EMAIL)) {
                try {
                    // Increase timeout for slow SMTP connections
                    set_time_limit(180);
                    
                    \Illuminate\Support\Facades\Mail::to($registration->email_address)
                        ->send(new \App\Mail\RegistrationVerified($registration));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send verification email to {$registration->email_address}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration verified! An email notification has been dispatched.'
            ]);
        });
    }

    public function reject(Request $request, $id)
    {
        return \DB::transaction(function () use ($request, $id) {
            $registration = VehicleRegistration::lockForUpdate()->findOrFail($id);
            
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            // Allow re-rejecting if it was already rejected (maybe to update reason or re-send mail)
            if ($registration->status !== 'pending' && $registration->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'This registration is already ' . $registration->status . '.'
                ], 400);
            }

            $registration->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);
     
            // Log the review
            \App\Models\RegistrationReview::create([
                'vehicle_registration_id' => $registration->id,
                'admin_id' => Auth::id(),
                'action' => 'rejected',
                'admin_notes' => $request->reason,
                'reviewed_at' => now(),
            ]);
     
            // Send Email
            if ($registration->email_address && filter_var($registration->email_address, FILTER_VALIDATE_EMAIL)) {
                try {
                    // Increase timeout for slow SMTP connections
                    set_time_limit(180);

                    \Illuminate\Support\Facades\Mail::to($registration->email_address)
                        ->send(new \App\Mail\RegistrationRejected($registration));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send rejection email to {$registration->email_address}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration rejected and notification dispatched.'
            ]);
        });
    }

    public function validateStoredDocument($id, $type)
    {
        $registration = VehicleRegistration::findOrFail($id);
        
        $pathMap = [
            'cr_file' => 'cr_path',
            'or_file' => 'or_path',
            'license_file' => 'license_path',
            'cor_file' => 'cor_path',
            'student_id_file' => 'student_id_path',
            'employee_id_file' => 'employee_id_path',
            'extra' => ['cor_path', 'employee_id_path', 'student_id_path']
        ];

        $path = null;
        if ($type === 'extra') {
            foreach ($pathMap['extra'] as $f) {
                if ($registration->$f) {
                    $path = $registration->$f;
                    $type = str_replace('_path', '', $f);
                    break;
                }
            }
        } else {
            $column = $pathMap[$type] ?? null;
            $path = $column ? $registration->$column : null;
        }

        if (!$path || !\Storage::exists($path)) {
            return response()->json(['success' => false, 'message' => 'File not found.']);
        }

        $validator = new \App\Services\DocumentValidationService();
        $fullPath = storage_path('app/' . $path);
        
        $result = $validator->validate($fullPath, $type);
        return response()->json($result);
    }

    // ─────────────────────────────────────────────────────
    // BRIDGE AUTO-LAUNCH
    // ─────────────────────────────────────────────────────

    /**
     * Check if bridge_service.py is already listening on port 8080.
     * If not, launch it in the background automatically.
     *
     * Called by both Office and Guard frontends before opening WebSocket.
     */
    public function startBridge()
    {
        $port       = 8080;
        $scriptPath = base_path('bridge_service.py');

        // 1. Check if the port is already open (bridge already running)
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.5);
        if ($connection) {
            fclose($connection);
            return response()->json([
                'status'  => 'already_running',
                'message' => 'Bridge is already active on port ' . $port . '.'
            ]);
        }

        // 2. Validate that the script exists
        if (!$scriptPath || !file_exists($scriptPath)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'bridge_service.py not found at: ' . $scriptPath
            ], 404);
        }

        // 3. Launch in background (Windows: start /B keeps it detached)
        $cmd = 'start /B python "' . $scriptPath . '" > NUL 2>&1';
        pclose(popen($cmd, 'r'));

        // 4. Give the process up to 3 s to bind the port
        $started = false;
        for ($i = 0; $i < 6; $i++) {
            usleep(500000);   // 0.5 s
            $check = @fsockopen('127.0.0.1', $port, $e, $s, 1);
            if ($check) {
                fclose($check);
                $started = true;
                break;
            }
        }

        if ($started) {
            return response()->json([
                'status'  => 'started',
                'message' => 'Bridge launched successfully.'
            ]);
        }

        return response()->json([
            'status'  => 'timeout',
            'message' => 'Bridge was launched but did not respond within 3 s. Try clicking Connect again.'
        ], 202);
    }

    /**
     * Terminate the bridge service.
     */
    public function stopBridge()
    {
        $port = 8080;
        $success = false;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows logic to find PID and kill it
            $output = shell_exec("netstat -ano | findstr :$port");
            if ($output) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    if (strpos($line, 'LISTENING') !== false) {
                        $parts = preg_split('/\s+/', trim($line));
                        $pid = end($parts);
                        if (is_numeric($pid)) {
                            shell_exec("taskkill /F /PID $pid");
                            $success = true;
                        }
                    }
                }
            }
        } else {
            // Linux/Unix
            $output = shell_exec("fuser -k $port/tcp 2>&1");
            $success = true;
        }

        if ($success) {
            return response()->json(['success' => true, 'message' => 'RFID Bridge stopped successfully.']);
        }
        
        return response()->json(['success' => false, 'message' => 'RFID Bridge was not running.']);
    }

    public function bridgeStatus()
    {
        // NO MORE SOCKET PROBING: Check Cache populated by heartbeat
        $lastHeartbeat = Cache::get('bridge_last_heartbeat');
        if ($lastHeartbeat) {
            return response()->json([
                'online' => true,
                'last_seen' => Carbon::parse($lastHeartbeat)->diffForHumans(),
                'port' => Cache::get('bridge_com_port', 'Unknown')
            ]);
        }
        return response()->json(['online' => false]);
    }

    public function demographics()
    {
        $roles = ['student', 'faculty', 'staff'];
        $stats = [];
        $pieData = [
            'outer' => [], // Roles
            'inner' => []  // Vehicle Types
        ];

        $roleLabels = [
            'student' => 'Student',
            'faculty' => 'Personnel',
            'staff' => 'Vendor',
        ];

        foreach ($roles as $role) {
            $owners = VehicleRegistration::where('role', $role)->get();
            $ownerCount = $owners->count();
            
            $vehicles = \App\Models\Vehicle::whereIn('user_id', $owners->pluck('id'))->get();
            $vehicleCount = $vehicles->count();
            
            $vTypes = $vehicles->groupBy('vehicle_type')->map->count();
            
            $stats[$role] = [
                'owners' => $ownerCount,
                'vehicles' => $vehicleCount,
                'ratio' => $ownerCount > 0 ? round($vehicleCount / $ownerCount, 1) : 0,
                'breakdown' => $vTypes,
                'top_multi' => VehicleRegistration::withCount('vehicles')
                    ->where('role', $role)
                    ->orderByDesc('vehicles_count')
                    ->take(3)
                    ->get()
            ];

            $label = $roleLabels[$role] ?? ucfirst($role);
            $pieData['outer'][] = ['label' => $label, 'value' => $ownerCount];
            foreach($vTypes as $type => $count) {
                $pieData['inner'][] = ['label' => $label . ' ' . ucfirst($type), 'value' => $count];
            }
        }

        // Summary Stats (Fleet)
        $totalOwners = VehicleRegistration::count();
        $totalVehicles = \App\Models\Vehicle::count();

        $popularCategoryRaw = VehicleRegistration::select('vehicle_type', \DB::raw('count(*) as total'))
            ->groupBy('vehicle_type')
            ->orderByDesc('total')
            ->first();
        $popularCategory = $popularCategoryRaw 
            ? $popularCategoryRaw->vehicle_type . ' (' . round(($popularCategoryRaw->total / max($totalOwners, 1)) * 100) . '%)' 
            : 'N/A';

        $popularBrandRaw = VehicleRegistration::select('make_brand', \DB::raw('count(*) as total'))
            ->groupBy('make_brand')
            ->orderByDesc('total')
            ->first();
        $popularBrand = $popularBrandRaw ? $popularBrandRaw->make_brand : 'N/A';

        $summary = [
            'popularCategory' => $popularCategory,
            'popularBrand' => $popularBrand,
            'totalVehicles' => $totalVehicles
        ];

        // Real-Time Occupancy Analysis
        $today = now()->toDateString();
        $latestLogsIds = VehicleLog::whereDate('timestamp', $today)
            ->select(\DB::raw('MAX(id) as id'))
            ->groupBy('vehicle_id')
            ->pluck('id');

        $insideLogs = VehicleLog::with(['vehicle', 'vehicleRegistration'])
            ->whereIn('id', $latestLogsIds)
            ->where('type', 'entry')
            ->get();

        $occupancyBreakdown = [];
        foreach($roles as $r) {
            $roleLogs = $insideLogs->filter(function($l) use ($r) {
                return $l->vehicleRegistration && $l->vehicleRegistration->role === $r;
            });
            $occupancyBreakdown[$r] = [
                'total' => $roleLogs->count(),
                'types' => $roleLogs->groupBy(fn($l) => $l->vehicle->vehicle_type ?? 'Other')->map->count()
            ];
        }

        return view('office.stats.demographics', compact('stats', 'pieData', 'occupancyBreakdown', 'totalOwners', 'totalVehicles', 'summary'));
    }

    public function expiry()
    {
        $activeRegistrations = VehicleRegistration::whereNotNull('rfid_tag_id')
            ->orderBy('validity_to', 'asc')
            ->get();

        $today = now()->startOfDay();
        $target = now()->addDays(30)->endOfDay();
        
        $expired = $activeRegistrations->filter(fn($reg) => Carbon::parse($reg->validity_to)->isBefore($today))->count();
        $critical = $activeRegistrations->filter(fn($reg) => 
            Carbon::parse($reg->validity_to)->isBetween($today, $target)
        )->count();
        $healthy = $activeRegistrations->filter(fn($reg) => Carbon::parse($reg->validity_to)->isAfter($target))->count();
            
        $total = $activeRegistrations->count();
        $expiredPerc = $total > 0 ? round(($expired / $total) * 100) : 0;
        $criticalPerc = $total > 0 ? round(($critical / $total) * 100) : 0;
        $healthyPerc = $total > 0 ? round(($healthy / $total) * 100) : 0;

        return view('office.stats.expiry', compact('expired', 'critical', 'healthy', 'total', 'expiredPerc', 'criticalPerc', 'healthyPerc', 'activeRegistrations'));
    }

    public function sendExpiryAlerts()
    {
        $now = now();
        $target = now()->addDays(30);
        
        $registrations = \App\Models\VehicleRegistration::whereNotNull('email_address')
            ->where('status', 'ACTIVE')
            ->where('validity_to', '>', $now->toDateString())
            ->where('validity_to', '<=', $target->toDateString())
            ->get();

        $count = 0;
        foreach ($registrations as $reg) {
            if (filter_var($reg->email_address, FILTER_VALIDATE_EMAIL)) {
                \Illuminate\Support\Facades\Mail::to($reg->email_address)
                    ->send(new \App\Mail\TagExpiringReminder($reg));
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Renewal notifications successfully dispatched to $count vehicle owners."
        ]);
    }

    public function behavior(Request $request)
    {
        $start = $request->query('start', now()->subDays(6)->toDateString());
        $end = $request->query('end', now()->toDateString());

        $logsQuery = VehicleLog::whereBetween('timestamp', [$start . ' 00:00:00', $end . ' 23:59:59']);

        // 1. Total Active Users (Filtered)
        $activeUserIds = (clone $logsQuery)->whereNotNull('vehicle_registration_id')
            ->distinct('vehicle_registration_id')
            ->pluck('vehicle_registration_id');
        $totalActiveUsers = $activeUserIds->count();

        // 2. Peak Activity Day
        $peakDayRaw = (clone $logsQuery)->select(\DB::raw('DATE(timestamp) as date, count(*) as total'))
            ->groupBy('date')
            ->orderByDesc('total')
            ->first();
        $peakActivityDay = $peakDayRaw ? Carbon::parse($peakDayRaw->date)->format('M d, Y') : 'N/A';

        // 3. Most Active Role
        $mostFreqRoleRaw = (clone $logsQuery)
            ->join('vehicle_registrations', 'vehicle_logs.vehicle_registration_id', '=', 'vehicle_registrations.id')
            ->select('vehicle_registrations.role', \DB::raw('count(*) as total'))
            ->groupBy('vehicle_registrations.role')
            ->orderByDesc('total')
            ->first();

        $roleLabels = [
            'student' => 'Student',
            'faculty' => 'Personnel',
            'staff' => 'Vendor',
        ];
        $mostFrequentRole = 'N/A';
        if ($mostFreqRoleRaw) {
            $mostFrequentRole = $roleLabels[$mostFreqRoleRaw->role] ?? ucfirst($mostFreqRoleRaw->role);
        }

        // 4. Average Scans per Day
        $diffDays = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $totalScans = (clone $logsQuery)->count();
        $avgScansPerDay = $diffDays > 0 ? round($totalScans / $diffDays, 1) : $totalScans;

        // Frequent Explorers: Top 10
        $frequentFlyersRaw = VehicleLog::select(\DB::raw('vehicle_registration_id, count(*) as total'))
            ->whereNotNull('vehicle_registration_id')
            ->whereBetween('timestamp', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->groupBy('vehicle_registration_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
            
        $frequentFlyers = $frequentFlyersRaw->map(function($f) {
            $reg = VehicleRegistration::find($f->vehicle_registration_id);
            return (object)[
                'name' => $reg->full_name ?? 'Unknown Owner',
                'role' => ucfirst($reg->role ?? 'N/A'),
                'count' => $f->total
            ];
        });

        // Multi-vehicle owners
        $multiOwners = VehicleRegistration::withCount('vehicles')
            ->get()
            ->filter(function($reg) { return $reg->vehicles_count > 1; })
            ->sortByDesc('vehicles_count')
            ->take(5);

        $period = $this->calculateTrendData($start, $end);

        if ($request->ajax()) {
            return response()->json([
                'frequentFlyers' => $frequentFlyers,
                'labels' => $period['labels'],
                'activityCounts' => $period['counts'],
                'summary' => [
                    'activeUsers' => $totalActiveUsers,
                    'peakDay' => $peakActivityDay,
                    'frequentRole' => $mostFrequentRole,
                    'avgScans' => $avgScansPerDay
                ]
            ]);
        }

        return view('office.stats.behavior', [
            'frequentFlyers' => $frequentFlyers,
            'multiOwners' => $multiOwners,
            'labels' => $period['labels'],
            'activityCounts' => $period['counts'],
            'startDate' => $start,
            'endDate' => $end,
            'summary' => [
                'activeUsers' => $totalActiveUsers,
                'peakDay' => $peakActivityDay,
                'frequentRole' => $mostFrequentRole,
                'avgScans' => $avgScansPerDay
            ]
        ]);
    }

    private function calculateTrendData($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);
        $diffDays = $startDate->diffInDays($endDate);

        $labels = [];
        $counts = [];

        if ($diffDays <= 1) { // Today/Single Day - Hourly view
            for ($i = 0; $i < 24; $i++) {
                $labels[] = Carbon::createFromTime($i, 0)->format('ga');
                $counts[] = VehicleLog::whereBetween('timestamp', [
                    $startDate->copy()->startOfDay()->addHours($i),
                    $startDate->copy()->startOfDay()->addHours($i)->addMinutes(59)->addSeconds(59)
                ])->count();
            }
        } elseif ($diffDays <= 31) { // Up to a month - Daily view
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $labels[] = $current->format('M d');
                $counts[] = VehicleLog::whereDate('timestamp', $current->toDateString())->count();
                $current->addDay();
            }
        } elseif ($diffDays <= 365) { // Up to a year - Weekly/Monthly view
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $monthLabel = $current->format('M Y');
                $labels[] = $monthLabel;
                $counts[] = VehicleLog::whereMonth('timestamp', $current->month)
                    ->whereYear('timestamp', $current->year)
                    ->count();
                $current->addMonth();
            }
        } else { // Over a year
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $labels[] = $current->format('Y');
                $counts[] = VehicleLog::whereYear('timestamp', $current->year)->count();
                $current->addYear();
            }
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    /**
     * AJAX Search for Behavior Analysis Table
     */
    public function behaviorSearch(Request $request)
    {
        $q = $request->query('q');
        $role = $request->query('role');
        $start = $request->query('start', now()->subDays(6)->toDateString());
        $end = $request->query('end', now()->toDateString());

        $owners = VehicleRegistration::withCount('vehicles')
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere('university_id', 'like', "%{$q}%")
                        ->orWhere('plate_number', 'like', "%{$q}%")
                        ->orWhere('college_dept', 'like', "%{$q}%")
                        ->orWhereHas('vehicles', function($v) use ($q) {
                            $v->where('plate_number', 'like', "%{$q}%");
                        });
                });
            })
            ->when($role && $role !== 'all', function($query) use ($role) {
                $query->where('role', $role);
            })
            ->get();
            
        $results = $owners->map(function($owner) use ($start, $end) {
            $entries = VehicleLog::where('vehicle_registration_id', $owner->id)
                ->where('type', 'entry')
                ->whereBetween('timestamp', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count();
            
            $exits = VehicleLog::where('vehicle_registration_id', $owner->id)
                ->where('type', 'exit')
                ->whereBetween('timestamp', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count();

            $total = $entries + $exits;

            return [
                'id' => $owner->id,
                'name' => $owner->full_name,
                'role' => [
                    'student' => 'Student',
                    'faculty' => 'Personnel',
                    'staff' => 'Vendor',
                ][$owner->role] ?? ucfirst($owner->role),
                'vehicles' => $owner->vehicles_count,
                'activity' => $total,
                'entries' => $entries,
                'exits' => $exits
            ];
        })->sortByDesc('activity')->values();

        return response()->json($results);
    }

    /**
     * Deep Audit for a specific owner
     */
    public function analyzeOwner($id)
    {
        $owner = VehicleRegistration::with('vehicles')->findOrFail($id);
        
        // 30-Day Trend (Daily Entries vs Exits)
        $labels = [];
        $entries = [];
        $exits = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $entries[] = VehicleLog::where('vehicle_registration_id', $id)
                ->where('type', 'entry')
                ->whereDate('timestamp', $date)
                ->count();
                
            $exits[] = VehicleLog::where('vehicle_registration_id', $id)
                ->where('type', 'exit')
                ->whereDate('timestamp', $date)
                ->count();
        }

        // Peak Hours Calculation (0-23)
        $freq = array_fill(0, 24, 0);
        $logs = VehicleLog::where('vehicle_registration_id', $id)->get();
        foreach($logs as $log) {
            $h = (int)$log->timestamp->format('H');
            $freq[$h]++;
        }
        
        arsort($freq);
        $peakHour = array_key_first($freq);
        $peakLabel = \Carbon\Carbon::createFromTime($peakHour, 0)->format('g:i A');

        // Most Used Vehicle
        $mostUsedRaw = VehicleLog::select(\DB::raw('vehicle_id, count(*) as total'))
            ->where('vehicle_registration_id', $id)
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->first();
            
        $mostUsedPlate = 'N/A';
        if ($mostUsedRaw) {
            $v = \App\Models\Vehicle::find($mostUsedRaw->vehicle_id);
            if ($v) $mostUsedPlate = $v->plate_number;
        }

        // Latest Logs (Latest 10)
        $latestLogs = VehicleLog::with('vehicle')
            ->where('vehicle_registration_id', $id)
            ->latest('timestamp')
            ->limit(10)
            ->get()
            ->map(function($log) {
                return [
                    'timestamp' => $log->timestamp->format('M d, Y h:i A'),
                    'type' => strtoupper($log->type),
                    'plate' => $log->vehicle->plate_number ?? 'N/A'
                ];
            });

        return response()->json([
            'success' => true,
            'owner' => [
                'name' => $owner->full_name,
                'role' => [
                    'student' => 'Student',
                    'faculty' => 'Personnel',
                    'staff' => 'Vendor',
                ][$owner->role] ?? ucfirst($owner->role),
                'joined' => $owner->created_at->format('M Y'),
                'vehicles_count' => $owner->vehicles->count()
            ],
            'stats' => [
                'labels' => $labels,
                'entries' => $entries,
                'exits' => $exits,
                'peak_hour' => $peakLabel,
                'most_used' => $mostUsedPlate,
                'total_activity' => $logs->count(),
                'latest_logs' => $latestLogs
            ]
        ]);
    }

    public function globalSearch(Request $request)
    {
        $q = $request->query('q');
        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $results = VehicleRegistration::with('vehicles')
            ->where(function($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                    ->orWhere('university_id', 'like', "%{$q}%")
                    ->orWhere('rfid_tag_id', 'like', "%{$q}%")
                    ->orWhere('plate_number', 'like', "%{$q}%")
                    ->orWhereHas('vehicles', function($v) use ($q) {
                        $v->where('plate_number', 'like', "%{$q}%")
                          ->orWhere('rfid_tag', 'like', "%{$q}%");
                    });
            })
            ->limit(10)
            ->get()
            ->map(function($reg) {
                $roleLabels = [
                    'student' => 'Student',
                    'faculty' => 'Faculty',
                    'staff' => 'Non-Teaching',
                ];
                
                $plates = [$reg->plate_number];
                foreach($reg->vehicles as $v) {
                    $plates[] = $v->plate_number;
                }
                $plates = array_unique(array_filter($plates));

                // Context-aware routing
                $userRole = auth()->user()->role;
                $url = '#';
                
                if ($userRole === 'admin') {
                    $url = route('admin.rfid.show', $reg->id);
                } elseif ($userRole === 'office') {
                    $url = route('office.registration.show', $reg->id);
                }

                return [
                    'id' => $reg->id,
                    'name' => $reg->full_name,
                    'role' => $roleLabels[$reg->role] ?? ucfirst($reg->role),
                    'university_id' => $reg->university_id,
                    'plates' => $plates,
                    'status' => $reg->status,
                    'url' => $url
                ];
            });

        return response()->json($results);
    }

    /**
     * Add a new vehicle to an existing registration.
     */
    public function addVehicle(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'plate_number'    => 'required|string|unique:vehicles,plate_number',
            'rfid_tag'        => 'required|string|unique:vehicles,rfid_tag',
            'vehicle_details' => 'nullable|string|max:255',
            'vehicle_type'    => 'required|string|max:100', // dynamic category name
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $vehicle = new Vehicle();
        $vehicle->user_id         = $registration->id;
        $vehicle->plate_number    = $request->plate_number;
        // Construct detailed string from dynamic components
        $details = trim(($registration->make_brand ?? '') . ' ' . ($registration->model_name ?? '') . ' ' . ($registration->model_year ?? ''));
        $vehicle->vehicle_details = $request->vehicle_details ?: $details; 
        $vehicle->vehicle_type    = $request->vehicle_type ?: $registration->vehicle_type;
        $vehicle->rfid_tag        = $request->rfid_tag;
        $vehicle->expiry_date     = $registration->validity_to; 
        $vehicle->save();

        // Update registration: set RFID tag and activate
        $registration->update([
            'rfid_tag_id'  => $request->rfid_tag,
            'status'       => 'ACTIVE',
            'validity_from' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle linked! Account for ' . $registration->full_name . ' is now ACTIVE.'
        ]);
    }
}
