<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DoctorMessagesController extends Controller
{
    /**
     * Display messages page with conversations list
     */
    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();
        
        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }
        
        // Get filter and search parameters
        $type = $request->get('type', 'all');
        $search = $request->get('search');
        
        // Build conversations query
        $conversationsQuery = Conversation::where('doctor_id', $doctor->doctor_id);
        
        // ✅ REMOVED ARCHIVED FILTER - Only show active conversations
        $conversationsQuery->where('status', 'active');
        
        // Load relationships
        $conversationsQuery->with(['latestMessage', 'patient.user', 'admin']);
        
        // Apply type filter
        if ($type === 'admin') {
            $conversationsQuery->where('conversation_type', 'doctor_admin');
        } elseif ($type === 'patient') {
            $conversationsQuery->where('conversation_type', 'doctor_patient');
        } elseif ($type === 'starred') {
            $conversationsQuery->where('is_starred', true);
        }
        
        // Apply search filter
        if ($search) {
            $conversationsQuery->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('messages', function($mq) use ($search) {
                      $mq->where('message_content', 'like', "%{$search}%");
                  });
            });
        }
        
        // Get conversations ordered by latest message
        $conversations = $conversationsQuery
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Get selected conversation
        $conversationId = $request->get('conversation_id');
        $selectedConversation = null;
        $messages = collect();
        
        if ($conversationId) {
            $selectedConversation = Conversation::where('conversation_id', $conversationId)
                ->where('doctor_id', $doctor->doctor_id)
                ->with(['patient.user', 'admin'])
                ->first();
            
            if ($selectedConversation) {
                // Get all messages in conversation
                $messages = $selectedConversation->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Mark messages as read for current user
                $selectedConversation->markAllAsRead(auth()->id());
            }
        }
        
        // Get doctor's patients (from appointments)
        $myPatients = Patient::whereHas('appointments', function($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })
        ->with('user')
        ->distinct()
        ->get();
        
        // Get message templates
        $templates = MessageTemplate::where('is_active', true)->get();

        // Get all admins for messaging
        $admins = User::where('role', 'admin')->get();
        
        // Calculate total unread count (only active conversations)
        $unreadCount = Conversation::where('doctor_id', $doctor->doctor_id)
            ->where('status', 'active')
            ->get()
            ->sum(function($conv) {
                return $conv->getUnreadCount(auth()->id());
            });
        
        return view('doctor.doctor_messages', [
            'doctor' => $doctor,
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'myPatients' => $myPatients,
            'templates' => $templates,
            'admins' => $admins,
            'unreadCount' => $unreadCount,
            'type' => $type
        ]);
    }
    
    /**
     * Create a new conversation and first message
     */
    public function create(Request $request)
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            
            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }
            
            // ✅ FIXED VALIDATION - Removed fields that aren't in the form
            $validated = $request->validate([
                'recipient_type' => 'required|in:admin,patient',
                'patient_id' => 'required_if:recipient_type,patient|nullable|exists:patients,patient_id',
                'admin_id' => 'required_if:recipient_type,admin|nullable|exists:users,id',
                'subject' => 'required|string|max:255',
                'message_content' => 'required|string|max:5000',
            ]);
            
            // Check for existing conversation
            $existingConversation = null;
            
            if ($validated['recipient_type'] === 'admin') {
                $existingConversation = Conversation::where('doctor_id', $doctor->doctor_id)
                    ->where('admin_id', $validated['admin_id'])
                    ->where('conversation_type', 'doctor_admin')
                    ->where('status', 'active')
                    ->first();
            } else {
                $existingConversation = Conversation::where('doctor_id', $doctor->doctor_id)
                    ->where('patient_id', $validated['patient_id'])
                    ->where('conversation_type', 'doctor_patient')
                    ->where('status', 'active')
                    ->first();
            }
            
            // If conversation exists, use it
            if ($existingConversation) {
                $conversation = $existingConversation;
                
                // Update subject if provided
                if ($validated['subject']) {
                    $conversation->update([
                        'subject' => $validated['subject'],
                        'last_message_at' => now()
                    ]);
                }
            } else {
                // Create new conversation based on recipient type
                if ($validated['recipient_type'] === 'admin') {
                    $conversation = Conversation::create([
                        'doctor_id' => $doctor->doctor_id,
                        'admin_id' => $validated['admin_id'],
                        'conversation_type' => 'doctor_admin',
                        'subject' => $validated['subject'],
                        'status' => 'active',
                        'last_message_at' => now(),
                    ]);
                } else {
                    $conversation = Conversation::create([
                        'doctor_id' => $doctor->doctor_id,
                        'patient_id' => $validated['patient_id'],
                        'conversation_type' => 'doctor_patient',
                        'subject' => $validated['subject'],
                        'status' => 'active',
                        'last_message_at' => now(),
                    ]);
                }
            }
            
            // ✅ FIXED - Check for priority checkbox properly
            $priority = $request->has('priority') && $request->priority === 'urgent' ? 'urgent' : 'normal';
            
            // Create first message
            $message = Message::create([
                'conversation_id' => $conversation->conversation_id,
                'sender_id' => auth()->id(),
                'sender_type' => 'doctor',
                'message_content' => $validated['message_content'],
                'priority' => $priority,
                'is_read' => false,
            ]);
            
            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'conversation_id' => $conversation->conversation_id,
                    'message' => 'Message sent successfully'
                ]);
            }
            
            // Fallback for regular form submission
            return redirect()->route('doctor.messages', ['conversation_id' => $conversation->conversation_id])
                ->with('success', 'Message sent successfully');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in create message:', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . json_encode($e->errors()),
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            Log::error('Error creating message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to send message. Please try again.');
        }
    }
    
    /**
     * Send a message in an existing conversation
     */
    public function send(Request $request)
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            
            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }
            
            // ✅ FIXED VALIDATION
            $validated = $request->validate([
                'conversation_id' => 'required|exists:conversations,conversation_id',
                'message_content' => 'required|string|max:5000',
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            ]);
            
            // Verify conversation belongs to this doctor
            $conversation = Conversation::where('conversation_id', $validated['conversation_id'])
                ->where('doctor_id', $doctor->doctor_id)
                ->first();
            
            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found or access denied'
                ], 404);
            }
            
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $attachmentPath = $file->storeAs('message_attachments', $fileName, 'public');
            }
            
            // ✅ FIXED - Check for priority checkbox properly
            $priority = $request->has('priority') && $request->priority === 'urgent' ? 'urgent' : 'normal';
            
            // Create message
            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'sender_id' => auth()->id(),
                'sender_type' => 'doctor',
                'message_content' => $validated['message_content'],
                'attachment_path' => $attachmentPath,
                'priority' => $priority,
                'is_read' => false,
            ]);
            
            // Update conversation's last message timestamp
            $conversation->update(['last_message_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => $message->load('sender')
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in send message:', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . json_encode($e->errors()),
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle star status on a conversation
     */
    public function toggleStar($id)
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'Doctor not found'], 404);
            }
            
            $conversation = Conversation::where('conversation_id', $id)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();
            
            if ($conversation) {
                $conversation->update(['is_starred' => !$conversation->is_starred]);
                return response()->json([
                    'success' => true,
                    'is_starred' => $conversation->is_starred,
                    'message' => $conversation->is_starred ? 'Conversation starred' : 'Conversation unstarred'
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
            
        } catch (\Exception $e) {
            Log::error('Error toggling star: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to toggle star'], 500);
        }
    }
    
    /**
     * Mark a specific message as read
     */
    public function markAsRead($id)
    {
        try {
            $message = Message::find($id);
            
            if ($message && $message->sender_id != auth()->id()) {
                $message->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
                
                return response()->json(['success' => true, 'message' => 'Message marked as read']);
            }
            
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
            
        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }
    
    /**
     * Get active message templates
     */
    public function getTemplates()
    {
        try {
            $templates = MessageTemplate::where('is_active', true)
                ->orderBy('template_name')
                ->get();
                
            return response()->json([
                'success' => true,
                'templates' => $templates
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching templates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch templates'
            ], 500);
        }
    }
}