<?php

namespace App\Http\Controllers\college;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\college;

class CollegeController extends Controller
{
    public function addCollege()
    {
        $colleges = college::orderBy('created_at', 'desc')->paginate(10);
        return view('college.add-college', compact('colleges'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'college_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $college = new college();
            $college->college_name = $request->college_name;
            $college->status = $request->status;
            
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/colleges');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $college->photo = 'storage/colleges/' . $photoName;
            }
            
            $college->save();
            
            return response()->json([
                'success' => true,
                'message' => 'College added successfully!',
                'data' => $college
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add college: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $college = college::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $college
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'college_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $college = college::findOrFail($id);
            $college->college_name = $request->college_name;
            $college->status = $request->status;
            
            if ($request->hasFile('photo')) {
                if ($college->photo && file_exists(public_path($college->photo))) {
                    unlink(public_path($college->photo));
                }
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/colleges');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $college->photo = 'storage/colleges/' . $photoName;
            }
            
            $college->save();
            
            return response()->json([
                'success' => true,
                'message' => 'College updated successfully!',
                'data' => $college
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update college: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $college = college::findOrFail($id);
            if ($college->photo && file_exists(public_path($college->photo))) {
                unlink(public_path($college->photo));
            }
            $college->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'College deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete college: ' . $e->getMessage()
            ], 500);
        }
    }
}
