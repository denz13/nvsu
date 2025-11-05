<?php

namespace App\Http\Controllers\announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\announcement;

class AnnouncementController extends Controller
{
    public function announcement()
    {
        $announcements = announcement::orderBy('created_at', 'desc')->paginate(10);
        return view('announcement.announcement', compact('announcements'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:active,inactive'
        ]);
        
        try {
            $announcement = new announcement();
            $announcement->title = $request->title;
            $announcement->description = $request->description;
            // Status is always 'active' on create
            $announcement->status = 'active';
            
            $announcement->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Announcement added successfully!',
                'data' => $announcement
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add announcement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $announcement = announcement::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $announcement
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $announcement = announcement::findOrFail($id);
            $announcement->title = $request->title;
            $announcement->description = $request->description;
            // Status is editable on update
            $announcement->status = $request->status;
            
            $announcement->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully!',
                'data' => $announcement
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $announcement = announcement::findOrFail($id);
            $announcement->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement: ' . $e->getMessage()
            ], 500);
        }
    }
}
