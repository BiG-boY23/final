<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Course;
use Illuminate\Http\Request;

class AcademicDataController extends Controller
{
    public function index()
    {
        $colleges = College::where('category', 'academic')->withCount('courses')->orderBy('name')->get();
        $offices = College::where('category', 'administrative')->orderBy('name')->get();
        $courses = Course::with('college')->orderBy('name')->get();
        
        return view('admin.manage.academic', compact('colleges', 'offices', 'courses'));
    }

    public function storeCollege(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:colleges,name',
            'code' => 'nullable|string',
            'category' => 'required|string|in:academic,administrative'
        ]);
        College::create($request->all());
        return back()->with('success', ($request->category === 'academic' ? 'Department' : 'Office') . ' added successfully.');
    }

    public function updateCollege(Request $request, $id)
    {
        $college = College::findOrFail($id);
        $request->validate([
            'name' => 'required|string|unique:colleges,name,' . $id,
            'code' => 'nullable|string',
            'category' => 'required|string|in:academic,administrative'
        ]);
        $college->update($request->all());
        return back()->with('success', ($request->category === 'academic' ? 'Department' : 'Office') . ' updated.');
    }

    public function destroyCollege($id)
    {
        College::findOrFail($id)->delete();
        return back()->with('success', 'College deleted.');
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required', 
                'string',
                \Illuminate\Validation\Rule::unique('courses')->where(fn($q) => $q->where('college_id', $request->college_id))
            ],
            'code' => 'nullable|string'
        ], [
            'name.unique' => 'This course already exists in the selected college.'
        ]);
        
        Course::create($request->all());
        return back()->with('success', 'Course added.');
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required', 
                'string',
                \Illuminate\Validation\Rule::unique('courses')->where(fn($q) => $q->where('college_id', $request->college_id))->ignore($id)
            ],
            'code' => 'nullable|string'
        ], [
            'name.unique' => 'This course already exists in the selected college.'
        ]);
        
        $course->update($request->all());
        return back()->with('success', 'Course updated.');
    }

    public function destroyCourse($id)
    {
        Course::findOrFail($id)->delete();
        return back()->with('success', 'Course deleted.');
    }
}
