<?php

namespace App\Http\Controllers\program;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\program;
use App\Models\college;

class ProgramController extends Controller
{
    //
    public function addProgram()
    {
        $programs = program::with('college')->orderBy('created_at', 'desc')->paginate(10);
        $colleges = college::orderBy('college_name', 'asc')->get();
        return view('program.add-program', compact('programs', 'colleges'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'college_id' => 'required|exists:college,id',
            'program_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $program = new program();
            $program->college_id = $request->college_id;
            $program->program_name = $request->program_name;
            $program->status = $request->status;

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('storage/programs');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $photo->move($uploadPath, $photoName);
                $program->photo = 'storage/programs/' . $photoName;
            }

            $program->save();

            return response()->json([
                'success' => true,
                'message' => 'Program added successfully!',
                'data' => $program
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add program: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $program = program::with('college')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $program
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'college_id' => 'required|exists:college,id',
            'program_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $program = program::findOrFail($id);
            $program->college_id = $request->college_id;
            $program->program_name = $request->program_name;
            $program->status = $request->status;

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($program->photo && file_exists(public_path($program->photo))) {
                    unlink(public_path($program->photo));
                }

                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('storage/programs');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $photo->move($uploadPath, $photoName);
                $program->photo = 'storage/programs/' . $photoName;
            }

            $program->save();

            return response()->json([
                'success' => true,
                'message' => 'Program updated successfully!',
                'data' => $program
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update program: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $program = program::findOrFail($id);
            
            // Delete photo if exists
            if ($program->photo && file_exists(public_path($program->photo))) {
                unlink(public_path($program->photo));
            }

            $program->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete program: ' . $e->getMessage()
            ], 500);
        }
    }
}
