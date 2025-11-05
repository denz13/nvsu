<?php

namespace App\Http\Controllers\permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\permission_settings;
use App\Models\permission_settings_list;
use App\Models\module;
use App\Models\User;
use App\Models\students;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function permission()
    {
        // Get all permission settings with related user/student
        $permissionSettings = permission_settings::with(['users', 'students'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($setting) {
                // Get modules for each setting
                $modules = permission_settings_list::where('permission_settings_id', $setting->id)
                    ->with('module')
                    ->get()
                    ->pluck('module.module')
                    ->filter()
                    ->toArray();
                
                return [
                    'id' => $setting->id,
                    'user_name' => $setting->users_id 
                        ? ($setting->users->name ?? 'N/A') 
                        : ($setting->students->student_name ?? 'N/A'),
                    'user_email' => $setting->users_id ? ($setting->users->email ?? '') : ($setting->students->id_number ?? ''),
                    'user_type' => $setting->users_id ? 'User' : 'Student',
                    'status' => $setting->status,
                    'modules' => $modules,
                    'modules_count' => count($modules),
                    'created_at' => $setting->created_at
                ];
            });
        
        // Get all users and students for dropdown
        $users = User::orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $studentsList = students::orderBy('student_name', 'asc')->get(['id', 'student_name', 'id_number']);
        
        // Get all modules
        $modules = module::where('status', 'active')->orderBy('module', 'asc')->get(['id', 'module']);
        
        return view('permission.permission', compact('permissionSettings', 'users', 'studentsList', 'modules'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'user_type' => 'required|in:user,student',
            'user_id' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:module,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Check if permission setting already exists for this user/student
            $permissionSetting = null;
            if ($request->user_type === 'user') {
                $permissionSetting = permission_settings::where('users_id', $request->user_id)
                    ->whereNull('students_id')
                    ->first();
            } else {
                $permissionSetting = permission_settings::where('students_id', $request->user_id)
                    ->whereNull('users_id')
                    ->first();
            }
            
            // Create or update permission setting
            if (!$permissionSetting) {
                $permissionSetting = new permission_settings();
                if ($request->user_type === 'user') {
                    $permissionSetting->users_id = $request->user_id;
                    $permissionSetting->students_id = null;
                } else {
                    $permissionSetting->students_id = $request->user_id;
                    $permissionSetting->users_id = null;
                }
            }
            
            $permissionSetting->status = $request->status;
            $permissionSetting->save();
            
            // Delete existing module assignments
            permission_settings_list::where('permission_settings_id', $permissionSetting->id)->delete();
            
            // Add new module assignments
            if ($request->has('modules') && !empty($request->modules)) {
                $moduleAssignments = [];
                foreach ($request->modules as $moduleId) {
                    $moduleAssignments[] = [
                        'permission_settings_id' => $permissionSetting->id,
                        'module_id' => $moduleId,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                permission_settings_list::insert($moduleAssignments);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Permission settings saved successfully!',
                'data' => $permissionSetting
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permission settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        try {
            $permissionSetting = permission_settings::with(['users', 'students'])
                ->findOrFail($id);
            
            // Get assigned module IDs - ensure they are integers
            $assignedModules = permission_settings_list::where('permission_settings_id', $id)
                ->where('status', 'active')
                ->pluck('module_id')
                ->map(function($id) {
                    return (int) $id;
                })
                ->toArray();
            
            // Determine user type
            $userType = $permissionSetting->users_id ? 'user' : 'student';
            $userId = $permissionSetting->users_id ?? $permissionSetting->students_id;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $permissionSetting->id,
                    'user_type' => $userType,
                    'user_id' => (int) $userId,
                    'status' => $permissionSetting->status,
                    'modules' => $assignedModules, // Array of integers
                    'user_name' => $permissionSetting->users_id 
                        ? ($permissionSetting->users->name ?? '') 
                        : ($permissionSetting->students->student_name ?? '')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load permission settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_type' => 'required|in:user,student',
            'user_id' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:module,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $permissionSetting = permission_settings::findOrFail($id);
            
            // Update permission setting
            $permissionSetting->status = $request->status;
            $permissionSetting->save();
            
            // Delete existing module assignments
            permission_settings_list::where('permission_settings_id', $permissionSetting->id)->delete();
            
            // Add new module assignments
            if ($request->has('modules') && !empty($request->modules)) {
                $moduleAssignments = [];
                foreach ($request->modules as $moduleId) {
                    $moduleAssignments[] = [
                        'permission_settings_id' => $permissionSetting->id,
                        'module_id' => $moduleId,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                permission_settings_list::insert($moduleAssignments);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Permission settings updated successfully!',
                'data' => $permissionSetting
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Delete module assignments
            permission_settings_list::where('permission_settings_id', $id)->delete();
            
            // Delete permission setting
            $permissionSetting = permission_settings::findOrFail($id);
            $permissionSetting->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Permission settings deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete permission settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
