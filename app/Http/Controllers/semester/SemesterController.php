<?php

namespace App\Http\Controllers\semester;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\semester;

class SemesterController extends Controller
{
    public function addSemester()
    {
        $semesters = semester::orderBy('created_at', 'desc')->paginate(10);
        return view('semester.add-semester', compact('semesters'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'school_year' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            // If setting as active, deactivate all other semesters
            if ($request->status === 'active') {
                semester::where('status', 'active')->update(['status' => 'inactive']);
            }
            
            $semester = new semester();
            $semester->school_year = $request->school_year;
            $semester->semester = $request->semester;
            $semester->status = $request->status;
            $semester->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Semester added successfully!',
                'data' => $semester
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add semester: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $semester = semester::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $semester
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'school_year' => 'required|string|max:255',
            'semester' => 'required|string|max:255',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $semester = semester::findOrFail($id);
            
            // If setting as active, deactivate all other semesters except current one
            if ($request->status === 'active') {
                semester::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'inactive']);
            }
            
            $semester->school_year = $request->school_year;
            $semester->semester = $request->semester;
            $semester->status = $request->status;
            $semester->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Semester updated successfully!',
                'data' => $semester
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update semester: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $semester = semester::findOrFail($id);
            $semester->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Semester deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete semester: ' . $e->getMessage()
            ], 500);
        }
    }
}
