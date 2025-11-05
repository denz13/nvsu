<?php

namespace App\Http\Controllers\profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\students;

class ProfileController extends Controller
{
    public function index()
    {
        $currentUser = null;
        $userType = null;
        
        // Check if student is logged in
        if (Auth::guard('students')->check()) {
            $currentUser = Auth::guard('students')->user();
            $userType = 'student';
            // Load relationships using with() if available, otherwise load() will work
            if ($currentUser instanceof \Illuminate\Database\Eloquent\Model) {
                $currentUser = students::with(['college', 'program', 'organization'])->find($currentUser->id);
            }
        } 
        // Check if admin/user is logged in
        elseif (Auth::check()) {
            $currentUser = Auth::user();
            $userType = 'admin';
        }
        
        return view('profile.profile', [
            'currentUser' => $currentUser,
            'userType' => $userType
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $userType = null;
            /** @var \App\Models\User|\App\Models\students|null $currentUser */
            $currentUser = null;
            
            // Check which guard is authenticated
            if (Auth::guard('students')->check()) {
                $currentUser = Auth::guard('students')->user();
                $userType = 'student';
                
                $request->validate([
                    'student_name' => 'required|string|max:255',
                    'address' => 'nullable|string',
                    'year_level' => 'nullable|string|max:255',
                ]);
                
                $currentUser->student_name = $request->student_name;
                if ($request->has('address')) {
                    $currentUser->address = $request->address;
                }
                if ($request->has('year_level')) {
                    $currentUser->year_level = $request->year_level;
                }
                
            } elseif (Auth::check()) {
                $currentUser = Auth::user();
                $userType = 'admin';
                
                // Ensure user is not null
                if (!$currentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
                
                $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email,' . $currentUser->id,
                    'gender' => 'required|in:male,female,other',
                ]);
                
                // Use User model to update directly
                $user = User::find($currentUser->id);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
                
                $user->name = $request->name;
                $user->email = $request->email;
                $user->gender = $request->gender;
                $user->save();
                
                $currentUser = $user; // Update for consistency
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // For students, save directly
            if ($userType === 'student' && $currentUser instanceof students) {
                $currentUser->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:new_password',
            ]);
            
            $userType = null;
            /** @var \App\Models\User|\App\Models\students|null $currentUser */
            $currentUser = null;
            
            // Check which guard is authenticated
            if (Auth::guard('students')->check()) {
                $currentUser = Auth::guard('students')->user();
                $userType = 'student';
            } elseif (Auth::check()) {
                $currentUser = Auth::user();
                $userType = 'admin';
                
                // Ensure user is not null
                if (!$currentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Verify current password
            $currentPassword = $currentUser->password;
            $isValid = Hash::check($request->current_password, $currentPassword) || $currentPassword === $request->current_password;
            
            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            
            // Update password based on user type
            if ($userType === 'student' && $currentUser instanceof students) {
                $currentUser->password = Hash::make($request->new_password);
                $currentUser->save();
            } elseif ($userType === 'admin' && $currentUser instanceof User) {
                // Use User model to update directly
                $user = User::find($currentUser->id);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
                $user->password = Hash::make($request->new_password);
                $user->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user object'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePhoto(Request $request)
    {
        try {
            // Check authentication first
            $userType = null;
            /** @var \App\Models\User|\App\Models\students|null $currentUser */
            $currentUser = null;
            
            // Check which guard is authenticated
            if (Auth::guard('students')->check()) {
                $currentUser = Auth::guard('students')->user();
                $userType = 'student';
                $uploadPath = public_path('storage/students');
                $storagePath = 'storage/students';
            } elseif (Auth::check()) {
                $currentUser = Auth::user();
                $userType = 'admin';
                $uploadPath = public_path('storage/users');
                $storagePath = 'storage/users';
                
                // Ensure user is not null
                if (!$currentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Debug: Log what we received
            Log::info('Photo upload request', [
                'user_type' => $userType,
                'has_file' => $request->hasFile('photo'),
                'all_files' => $request->allFiles(),
                'all_input' => array_keys($request->all()),
            ]);
            
            // Validate photo upload
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ], [
                'photo.required' => 'Please select an image file. No file received.',
                'photo.image' => 'The file must be an image',
                'photo.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp',
                'photo.max' => 'The image must not be larger than 2MB'
            ]);
            
            // Delete old photo if exists
            if ($currentUser->photo && file_exists(public_path($currentUser->photo))) {
                unlink(public_path($currentUser->photo));
            }
            
            // Upload new photo
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = time() . '_' . $photo->getClientOriginalName();
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $photo->move($uploadPath, $photoName);
                $photoPath = $storagePath . '/' . $photoName;
                
                // Update photo based on user type
                if ($userType === 'student' && $currentUser instanceof students) {
                    $currentUser->photo = $photoPath;
                    $currentUser->save();
                } elseif ($userType === 'admin' && $currentUser instanceof User) {
                    // Use User model to update directly
                    $user = User::find($currentUser->id);
                    if ($user) {
                        $user->photo = $photoPath;
                        $user->save();
                        $currentUser = $user; // Update for response
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'User not found'
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid user object'
                    ], 500);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully!',
                'photo_url' => asset($currentUser->photo)
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update photo: ' . $e->getMessage()
            ], 500);
        }
    }
}
