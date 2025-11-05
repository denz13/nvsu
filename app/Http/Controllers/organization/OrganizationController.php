<?php

namespace App\Http\Controllers\organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\organization;

class OrganizationController extends Controller
{
    public function addOrganization()
    {
        $organizations = organization::orderBy('created_at', 'desc')->paginate(10);
        return view('organization.add-organization', compact('organizations'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $organization = new organization();
            $organization->organization_name = $request->organization_name;
            $organization->organization_description = $request->organization_description;
            $organization->status = $request->status;
            
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/organizations');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $organization->photo = 'storage/organizations/' . $photoName;
            }
            
            $organization->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Organization added successfully!',
                'data' => $organization
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add organization: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $organization = organization::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $organization
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        try {
            $organization = organization::findOrFail($id);
            $organization->organization_name = $request->organization_name;
            $organization->organization_description = $request->organization_description;
            $organization->status = $request->status;
            
            if ($request->hasFile('photo')) {
                if ($organization->photo && file_exists(public_path($organization->photo))) {
                    unlink(public_path($organization->photo));
                }
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                $uploadPath = public_path('storage/organizations');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $photo->move($uploadPath, $photoName);
                $organization->photo = 'storage/organizations/' . $photoName;
            }
            
            $organization->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Organization updated successfully!',
                'data' => $organization
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update organization: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $organization = organization::findOrFail($id);
            if ($organization->photo && file_exists(public_path($organization->photo))) {
                unlink(public_path($organization->photo));
            }
            $organization->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Organization deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete organization: ' . $e->getMessage()
            ], 500);
        }
    }
}
