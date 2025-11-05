<?php

namespace App\Http\Controllers\students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\students;
use App\Models\college;
use App\Models\program;
use App\Models\organization;

class StudentsController extends Controller
{
    public function addStudents()
    {
        $students = students::with(['college', 'program', 'organization'])->orderBy('created_at', 'desc')->paginate(10);
        $colleges = college::orderBy('college_name', 'asc')->get();
        $programs = program::orderBy('program_name', 'asc')->get();
        $organizations = organization::orderBy('organization_name', 'asc')->get();
        return view('students.add-students', compact('students', 'colleges', 'programs', 'organizations'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'id_number' => 'required|string|max:255|unique:students,id_number',
            'student_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'year_level' => 'required|string|max:255',
            'college_id' => 'required|exists:college,id',
            'program_id' => 'required|exists:program,id',
            'organization_id' => 'nullable|exists:organization,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $student = new students();
            $student->id_number = $request->id_number;
            $student->student_name = $request->student_name;
            $student->address = $request->address;
            $student->year_level = $request->year_level;
            $student->college_id = $request->college_id;
            $student->program_id = $request->program_id;
            $student->organization_id = $request->organization_id;
            $student->password = bcrypt($request->password ?? 'default123');
            $student->status = $request->status;
            
            // Use custom barcode or generate one
            $student->barcode = $request->barcode ?? 'STU' . time() . rand(1000, 9999);
            
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/students');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $student->photo = 'storage/students/' . $photoName;
            }
            
            $student->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Student added successfully!',
                'data' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add student: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $student = students::with(['college', 'program', 'organization'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_number' => 'required|string|max:255|unique:students,id_number,' . $id,
            'student_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'year_level' => 'required|string|max:255',
            'college_id' => 'required|exists:college,id',
            'program_id' => 'required|exists:program,id',
            'organization_id' => 'nullable|exists:organization,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $student = students::findOrFail($id);
            $student->id_number = $request->id_number;
            $student->student_name = $request->student_name;
            $student->address = $request->address;
            $student->year_level = $request->year_level;
            $student->college_id = $request->college_id;
            $student->program_id = $request->program_id;
            $student->organization_id = $request->organization_id;
            $student->status = $request->status;
            
            if ($request->password) {
                $student->password = bcrypt($request->password);
            }
            
            if ($request->hasFile('photo')) {
                if ($student->photo && file_exists(public_path($student->photo))) {
                    unlink(public_path($student->photo));
                }
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/students');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $student->photo = 'storage/students/' . $photoName;
            }
            
            $student->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully!',
                'data' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $student = students::findOrFail($id);
            if ($student->photo && file_exists(public_path($student->photo))) {
                unlink(public_path($student->photo));
            }
            $student->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateBarcode(Request $request, $id)
    {
        try {
            $student = students::findOrFail($id);
            $student->barcode = $request->barcode;
            $student->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Barcode updated successfully!',
                'data' => $student
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barcode: ' . $e->getMessage()
            ], 500);
        }
    }
}
