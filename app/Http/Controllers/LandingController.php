<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleRegistration;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function showRegistrationForm()
    {
        $brands = \App\Models\VehicleBrand::with('models')->orderBy('name')->get();
        $categories = \App\Models\VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $colleges = \App\Models\College::where('category', 'academic')->with('courses')->orderBy('name')->get();
        $offices = \App\Models\College::where('category', 'administrative')->orderBy('name')->get();

        return view('online-registration', compact('brands', 'categories', 'colleges', 'offices'));
    }

    public function validateDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
            'type' => 'required|string'
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        // Store temporarily for validation
        $tempPath = $file->store('temp_validation', 'public');
        $fullPath = storage_path('app/public/' . $tempPath);

        $validator = new \App\Services\DocumentValidationService();
        $result = $validator->validate($fullPath, $type);

        // Delete temp file
        \Illuminate\Support\Facades\Storage::disk('public')->delete($tempPath);

        return response()->json($result);
    }

    public function submitRegistration(Request $request)
    {
        // ... (Existing validation logic remains the same for role-specific fields)
        // ... (I will keep the existing logic and just add the validation call)
        
        // Let's assume for Capstone we do the "Quick scan" on the final submission too
        // To save time, I'll just keep the existing code but ensure we can call it.
        
        // (For brevity in the replacement chunk, I will just append the method)
        // (Wait, I need to make sure I don't break the existing submitRegistration)
        
        // Actually, I'll just add the validateDocument method as requested.
        return $this->processSubmission($request);
    }

    protected function processSubmission(Request $request)
    {
        // 1. Base Validation
        $rules = [
            'role'             => 'required|in:student,faculty,staff',
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'middle_name'      => 'nullable|string|max:100',
            'contact_number'   => $request->role === 'faculty' ? 'nullable|string|max:20' : 'required|string|max:20',
            'email_address'    => 'nullable|email|max:255',
            'vehicle_type'     => 'required|string|max:100',   // now dynamic category names
            'make_brand'       => 'required|string|max:255',
            'model_name'       => 'nullable|string|max:255',
            'plate_number'     => 'required|string|max:20',
            // File validation
            'cr_file'          => 'required|image|max:5120',
            'or_file'          => 'required|image|max:5120',
            'license_file'     => 'required|image|max:5120',
        ];

        // 2. Role-specific Validation
        if ($request->role === 'student') {
            $rules['student_id'] = 'required|string|max:50';
            $rules['course'] = 'required|string|max:100';
            $rules['college_dept'] = 'required|string|max:100';
            $rules['year_level'] = 'required|string|max:10';
            $rules['access_classification'] = 'required|string';
            $rules['cor_file'] = 'required|image|max:5120';
            $rules['student_id_file'] = 'required|image|max:5120';
        } elseif ($request->role === 'faculty') {
            $rules['faculty_id'] = 'nullable|string|max:50';
            $rules['college_dept_faculty'] = 'required|string|max:100';
            $rules['address'] = 'required|string|max:255';
            $rules['access_classification_faculty'] = 'required|string';
            $rules['employee_id_file'] = 'required|image|max:5120';
        } elseif ($request->role === 'staff') {
            $rules['business_stall_name'] = 'required|string|max:255';
            $rules['access_classification_staff'] = 'required|string';
            $rules['employee_id_file'] = 'required|image|max:5120';
        }

        // 2.1 Email Domain Validation (Required for Students ONLY)
        if ($request->role === 'student') {
            $rules['email_address'] = 'required|email|regex:/@evsu\.edu\.ph$/i';
        } elseif ($request->role === 'faculty') {
            $rules['email_address'] = 'nullable|email';
        } else {
            $rules['email_address'] = 'required|email';
        }

        $request->validate($rules, [
            'email_address.regex' => 'Students must use their official @evsu.edu.ph email address.'
        ]);

        // 3. Handle File Uploads
        $paths = [];
        $files = [
            'cr_file' => 'cr_path',
            'or_file' => 'or_path',
            'license_file' => 'license_path',
            'cor_file' => 'cor_path',
            'student_id_file' => 'student_id_path',
            'employee_id_file' => 'employee_id_path',
            'payment_receipt_file' => 'payment_receipt_path',
        ];

        foreach ($files as $input => $column) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $filename = time() . '_' . $input . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('registrations', $filename, 'public');
                $paths[$column] = $path;
            }
        }

        // 4. Prepare Database Entry
        $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
        
        $data = [
            'role'              => $request->role,
            'full_name'         => $fullName,
            'contact_number'    => $request->contact_number,
            'email_address'     => $request->email_address,
            'vehicle_type'      => $request->vehicle_type,
            'make_brand'        => $request->make_brand,
            'model_name'        => $request->model_name,
            'plate_number'      => $request->plate_number,
            'status'            => 'pending',
            'registered_owner'  => $fullName,
        ];

        // Specific mappings
        if ($request->role === 'student') {
            $data['university_id'] = $request->student_id;
            $data['course'] = $request->course;
            $data['college_dept'] = $request->college_dept;
            $data['year_level'] = $request->year_level;
            $data['sticker_classification'] = [$request->access_classification];
        } elseif ($request->role === 'faculty') {
            $data['university_id'] = $request->faculty_id;
            $data['college_dept'] = $request->college_dept_faculty;
            $data['office'] = $request->address;
            $data['sticker_classification'] = [$request->access_classification_faculty];
        } elseif ($request->role === 'staff') {
            $data['university_id'] = 'N/A';
            $data['business_stall_name'] = $request->business_stall_name;
            $data['sticker_classification'] = [$request->access_classification_staff];
        }

        // Merge file paths
        $data = array_merge($data, $paths);
        
        $registration = VehicleRegistration::create($data);

        // Increase timeout for slow SMTP connections
        set_time_limit(180);

        // Notify Admin directly using the dedicated email address
        \Illuminate\Support\Facades\Notification::route('mail', 'skeptron1973darkrai@gmail.com')
            ->notify(new \App\Notifications\NewOnlineRegistration($registration));

        return redirect()->route('landing')->with('success', 'Application submitted! Please wait for an email verification before visiting the office for your RFID tag.');
    }
}
