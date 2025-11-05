<?php

namespace App\Http\Controllers\chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\message;
use App\Models\User;
use App\Models\students;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function chat()
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser || !isset($currentUser['id'])) {
                // Redirect to login if not authenticated
                return redirect()->route('login.index');
            }
            
            // Get list of conversations (people the current user has messaged with)
            $conversations = [];
            try {
                $conversations = $this->getConversations($currentUser);
            } catch (\Exception $e) {
                Log::error('Error getting conversations: ' . $e->getMessage());
            }
            
            // Get all users (both User and Student models) for contact list
            $allUsers = [];
            try {
                $allUsers = $this->getAllUsers($currentUser);
            } catch (\Exception $e) {
                Log::error('Error getting all users: ' . $e->getMessage());
            }
            
            return view('chat.chat', [
                'conversations' => $conversations,
                'currentUser' => $currentUser,
                'allUsers' => $allUsers
            ]);
        } catch (\Exception $e) {
            Log::error('Error in chat view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load chat page');
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser || !isset($currentUser['id'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }
            
            $request->validate([
                'to_id' => 'required|integer',
                'message_content' => 'required|string|max:1000',
            ]);

            $message = message::create([
                'from_id' => $currentUser['id'],
                'to_id' => $request->to_id,
                'message_content' => $request->message_content,
                'status' => 'sent'
            ]);

            // Add sender and receiver info manually
            try {
                $sender = User::find($message->from_id);
                if (!$sender) {
                    $sender = students::find($message->from_id);
                }
                $message->sender_data = $sender ? [
                    'id' => $sender->id,
                    'name' => $sender instanceof User ? $sender->name : $sender->student_name,
                    'photo' => $this->getUserPhoto($sender)
                ] : null;
                
                $receiver = User::find($message->to_id);
                if (!$receiver) {
                    $receiver = students::find($message->to_id);
                }
                $message->receiver_data = $receiver ? [
                    'id' => $receiver->id,
                    'name' => $receiver instanceof User ? $receiver->name : $receiver->student_name,
                    'photo' => $this->getUserPhoto($receiver)
                ] : null;
            } catch (\Exception $e) {
                Log::warning('Failed to load message user data: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages between current user and another user
     */
    public function getMessages(Request $request, $userId)
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser || !isset($currentUser['id'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }
            
            // Get limit from request, default to 15
            $limit = $request->input('limit', 15);
            $offset = $request->input('offset', 0);
            
            // Get total count for pagination
            $totalMessages = message::where(function($query) use ($currentUser, $userId) {
                    $query->where('from_id', $currentUser['id'])
                          ->where('to_id', $userId);
                })
                ->orWhere(function($query) use ($currentUser, $userId) {
                    $query->where('from_id', $userId)
                          ->where('to_id', $currentUser['id']);
                })
                ->count();
            
            // Get messages (latest first, then reverse for display)
            $messages = message::where(function($query) use ($currentUser, $userId) {
                    $query->where('from_id', $currentUser['id'])
                          ->where('to_id', $userId);
                })
                ->orWhere(function($query) use ($currentUser, $userId) {
                    $query->where('from_id', $userId)
                          ->where('to_id', $currentUser['id']);
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->reverse()
                ->values(); // Re-index array

            // Add sender and receiver info manually to avoid relationship errors
            foreach ($messages as $message) {
                try {
                    // Get sender
                    $sender = User::find($message->from_id);
                    if (!$sender) {
                        $sender = students::find($message->from_id);
                    }
                    $message->sender_data = $sender ? [
                        'id' => $sender->id,
                        'name' => $sender instanceof User ? $sender->name : $sender->student_name,
                        'photo' => $this->getUserPhoto($sender)
                    ] : null;
                    
                    // Get receiver
                    $receiver = User::find($message->to_id);
                    if (!$receiver) {
                        $receiver = students::find($message->to_id);
                    }
                    $message->receiver_data = $receiver ? [
                        'id' => $receiver->id,
                        'name' => $receiver instanceof User ? $receiver->name : $receiver->student_name,
                        'photo' => $this->getUserPhoto($receiver)
                    ] : null;
                } catch (\Exception $e) {
                    Log::warning('Failed to load user data for message ' . $message->id . ': ' . $e->getMessage());
                }
            }

            // Mark messages as read
            try {
                message::where('from_id', $userId)
                       ->where('to_id', $currentUser['id'])
                       ->where('status', 'sent')
                       ->update(['status' => 'read']);
            } catch (\Exception $e) {
                Log::warning('Failed to mark messages as read: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'total' => $totalMessages,
                'has_more' => ($offset + $limit) < $totalMessages,
                'offset' => $offset,
                'limit' => $limit
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of conversations
     */
    public function getConversationsList()
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if (!$currentUser || !isset($currentUser['id'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated',
                    'conversations' => []
                ], 401);
            }
            
            $conversations = $this->getConversations($currentUser);
            
            return response()->json([
                'success' => true,
                'conversations' => $conversations
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to load conversations: ' . $e->getMessage(),
                'conversations' => []
            ], 500);
        }
    }

    /**
     * Get conversations for current user
     */
    private function getConversations($currentUser)
    {
        // Get distinct users the current user has messaged with
        $messageUserIds = message::where('from_id', $currentUser['id'])
            ->orWhere('to_id', $currentUser['id'])
            ->select('from_id', 'to_id', DB::raw('MAX(created_at) as last_message_at'))
            ->groupBy('from_id', 'to_id')
            ->get();

        $conversations = [];
        
        foreach ($messageUserIds as $msg) {
            $otherUserId = $msg->from_id == $currentUser['id'] ? $msg->to_id : $msg->from_id;
            
            // Get the other user (could be User or Student)
            // CRITICAL: If both User and Student exist with same ID, we need to determine which one
            // The rule: If current user is User type, pick Student. If current user is Student type, pick User.
            $otherUser = null;
            $otherUserType = null;
            
            // Check if both User and Student exist with same ID
            $userFound = User::find($otherUserId);
            $studentFound = students::find($otherUserId);
            
            if ($userFound && $studentFound) {
                // Both exist with same ID - pick the one that's NOT the current user type
                if ($currentUser['type'] === 'user') {
                    // Current user is User, so other must be Student
                    $otherUser = $studentFound;
                    $otherUserType = 'student';
                } else {
                    // Current user is Student, so other must be User
                    $otherUser = $userFound;
                    $otherUserType = 'user';
                }
            } elseif ($userFound) {
                // Only User exists - check if it's the current user
                if ($currentUser['type'] === 'user' && $userFound->id == $currentUser['id']) {
                    continue; // Skip - same person
                }
                $otherUser = $userFound;
                $otherUserType = 'user';
            } elseif ($studentFound) {
                // Only Student exists - check if it's the current user
                if ($currentUser['type'] === 'student' && $studentFound->id == $currentUser['id']) {
                    continue; // Skip - same person
                }
                $otherUser = $studentFound;
                $otherUserType = 'student';
            }
            
            if ($otherUser) {
                // Get last message
                $lastMessage = message::where(function($query) use ($currentUser, $otherUserId) {
                        $query->where('from_id', $currentUser['id'])
                              ->where('to_id', $otherUserId);
                    })
                    ->orWhere(function($query) use ($currentUser, $otherUserId) {
                        $query->where('from_id', $otherUserId)
                              ->where('to_id', $currentUser['id']);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                // Count unread messages
                $unreadCount = message::where('from_id', $otherUserId)
                    ->where('to_id', $currentUser['id'])
                    ->where('status', 'sent')
                    ->count();
                
                $conversations[] = [
                    'user_id' => $otherUser->id,
                    'user_type' => $otherUserType ?? ($otherUser instanceof User ? 'user' : 'student'),
                    'name' => $this->getUserName($otherUser),
                    'photo' => $this->getUserPhoto($otherUser),
                    'last_message' => $lastMessage ? $lastMessage->message_content : '',
                    'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                    'unread_count' => $unreadCount
                ];
            }
        }
        
        // Sort by last message time
        usort($conversations, function($a, $b) {
            if (!$a['last_message_time'] && !$b['last_message_time']) return 0;
            if (!$a['last_message_time']) return 1;
            if (!$b['last_message_time']) return -1;
            return $b['last_message_time'] <=> $a['last_message_time'];
        });
        
        return $conversations;
    }

    /**
     * Get current authenticated user
     */
    private function getCurrentUser()
    {
        try {
            // Check if user is authenticated via web guard
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                if ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? 'User',
                        'type' => 'user'
                    ];
                }
            }
            
            // Check if user is authenticated via students guard
            if (Auth::guard('students')->check()) {
                $student = Auth::guard('students')->user();
                if ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->student_name ?? 'Student',
                        'type' => 'student'
                    ];
                }
            }
            
            Log::warning('No authenticated user found');
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting current user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user name (works for both User and Student)
     */
    private function getUserName($user)
    {
        if ($user instanceof User) {
            return $user->name;
        } elseif ($user instanceof students) {
            return $user->student_name;
        }
        return 'Unknown';
    }

    /**
     * Get user photo (works for both User and Student)
     * Uses same logic as topbar.blade.php
     */
    private function getUserPhoto($user)
    {
        $defaultPhoto = asset('dist/images/profile-5.jpg');
        
        if ($user && $user->photo) {
            // Use same photo path logic as topbar
            $photoPath = str_replace('storage/', '', $user->photo);
            return asset('storage/' . $photoPath);
        }
        
        return $defaultPhoto;
    }

    /**
     * Get all users (User and Student) for contact list
     */
    private function getAllUsers($currentUser)
    {
        $users = [];
        
        // Get all Users
        $userModels = User::all();
        foreach ($userModels as $user) {
            // Skip current user
            if ($currentUser['type'] === 'user' && $user->id == $currentUser['id']) {
                continue;
            }
            $users[] = [
                'id' => $user->id,
                'name' => $user->name,
                'photo' => $this->getUserPhoto($user),
                'type' => 'user'
            ];
        }
        
        // Get all Students
        $studentModels = students::all();
        foreach ($studentModels as $student) {
            // Skip current user
            if ($currentUser['type'] === 'student' && $student->id == $currentUser['id']) {
                continue;
            }
            $users[] = [
                'id' => $student->id,
                'name' => $student->student_name,
                'photo' => $this->getUserPhoto($student),
                'type' => 'student'
            ];
        }
        
        // Sort by name
        usort($users, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $users;
    }
}
