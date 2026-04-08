<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Receptionist;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReceptionistMessagesController extends Controller
{
    use \App\Traits\MessagePollingTrait;

    protected function getParticipantColumn(): string
    {
        return 'receptionist_id';
    }

    protected function getParticipantId(): int
    {
        return \App\Models\Receptionist::where('user_id', auth()->id())->value('receptionist_id');
    }

    public function index(Request $request)
    {
        $receptionist = Receptionist::where('user_id', auth()->id())->first();

        if (!$receptionist) {
            return redirect()->route('receptionist.dashboard')->with('error', 'Receptionist profile not found');
        }

        $type   = $request->get('type', 'all');
        $search = $request->get('search');

        $conversationsQuery = Conversation::where('receptionist_id', $receptionist->receptionist_id)
            ->where('status', 'active')
            ->with(['latestMessage', 'doctor.user', 'nurse.user', 'patient.user', 'admin']);

        if ($type === 'admin') {
            $conversationsQuery->where('conversation_type', 'receptionist_admin');
        } elseif ($type === 'doctors') {
            $conversationsQuery->whereIn('conversation_type', ['doctor_receptionist', 'receptionist_doctor']);
        } elseif ($type === 'nurses') {
            $conversationsQuery->whereIn('conversation_type', ['nurse_receptionist', 'receptionist_nurse']);
        } elseif ($type === 'patients') {
            $conversationsQuery->where('conversation_type', 'receptionist_patient');
        } elseif ($type === 'starred') {
            $conversationsQuery->where('is_starred', true);
        }

        if ($search) {
            $conversationsQuery->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('message_content', 'like', "%{$search}%");
                    });
            });
        }

        $conversations = $conversationsQuery
            ->orderBy('last_message_at', 'desc')
            ->get();

        $conversationId       = $request->get('conversation_id');
        $selectedConversation = null;
        $messages             = collect();

        if ($conversationId) {
            $selectedConversation = Conversation::where('conversation_id', $conversationId)
                ->where('receptionist_id', $receptionist->receptionist_id)
                ->with(['doctor.user', 'nurse.user', 'patient.user', 'admin'])
                ->first();

            if ($selectedConversation) {
                $messages = $selectedConversation->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();

                $selectedConversation->markAllAsRead(auth()->id());
            }
        }

        $templates = MessageTemplate::where('is_active', true)->get();

        $doctors = Doctor::with('user')
            ->where('availability_status', 'Available')
            ->get();

        $nurses = Nurse::with('user')
            ->where('availability_status', 'Available')
            ->get();

        $patients = Patient::with('user')->get();

        $unreadCount = Conversation::where('receptionist_id', $receptionist->receptionist_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($conv) => $conv->getUnreadCount(auth()->id()));

        return view('receptionist.receptionist_messages', [
            'receptionist'         => $receptionist,
            'conversations'        => $conversations,
            'selectedConversation' => $selectedConversation,
            'messages'             => $messages,
            'templates'            => $templates,
            'doctors'              => $doctors,
            'nurses'               => $nurses,
            'patients'             => $patients,
            'unreadCount'          => $unreadCount,
            'type'                 => $type,
        ]);
    }

    /**
     * Returns unread message count for sidebar badge polling.
     */
    public function unreadCount()
    {
        $receptionist = Receptionist::where('user_id', auth()->id())->first();

        if (!$receptionist) {
            return response()->json(['count' => 0, 'severity' => 'normal']);
        }

        $count = Conversation::where('receptionist_id', $receptionist->receptionist_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($conv) => $conv->getUnreadCount(auth()->id()));

        return response()->json(['count' => $count, 'severity' => 'normal']);
    }

    public function create(Request $request)
    {
        try {
            $receptionist = Receptionist::where('user_id', auth()->id())->first();

            if (!$receptionist) {
                return response()->json(['success' => false, 'message' => 'Receptionist profile not found'], 404);
            }

            $validated = $request->validate([
                'recipient_type' => 'required|in:admin,doctor,nurse,patient',
                'doctor_id'      => 'required_if:recipient_type,doctor|nullable|exists:doctors,doctor_id',
                'nurse_id'       => 'required_if:recipient_type,nurse|nullable|exists:nurses,nurse_id',
                'patient_id'     => 'required_if:recipient_type,patient|nullable|exists:patients,patient_id',
                'subject'        => 'required|string|max:255',
                'message_content' => 'required|string|max:5000',
            ]);

            $newSubject   = trim($validated['subject']);
            $conversation = null;

            switch ($validated['recipient_type']) {

                case 'admin':
                    $conversation = Conversation::where('receptionist_id', $receptionist->receptionist_id)
                        ->where('conversation_type', 'receptionist_admin')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->latest('last_message_at')
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $assignedAdmin = $this->resolveAdminForNewConversation();
                        if (!$assignedAdmin) {
                            return response()->json(['success' => false, 'message' => 'No admin staff are currently available.'], 503);
                        }
                        $conversation = Conversation::create([
                            'receptionist_id'   => $receptionist->receptionist_id,
                            'admin_id'          => $assignedAdmin->id,
                            'conversation_type' => 'receptionist_admin',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'doctor':
                    $conversation = Conversation::where('receptionist_id', $receptionist->receptionist_id)
                        ->where('doctor_id', $validated['doctor_id'])
                        ->where('conversation_type', 'receptionist_doctor')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'receptionist_id'   => $receptionist->receptionist_id,
                            'doctor_id'         => $validated['doctor_id'],
                            'conversation_type' => 'receptionist_doctor',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'nurse':
                    $conversation = Conversation::where('receptionist_id', $receptionist->receptionist_id)
                        ->where('nurse_id', $validated['nurse_id'])
                        ->where('conversation_type', 'receptionist_nurse')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'receptionist_id'   => $receptionist->receptionist_id,
                            'nurse_id'          => $validated['nurse_id'],
                            'conversation_type' => 'receptionist_nurse',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'patient':
                    $conversation = Conversation::where('receptionist_id', $receptionist->receptionist_id)
                        ->where('patient_id', $validated['patient_id'])
                        ->where('conversation_type', 'receptionist_patient')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'receptionist_id'   => $receptionist->receptionist_id,
                            'patient_id'        => $validated['patient_id'],
                            'conversation_type' => 'receptionist_patient',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;
            }

            $priority = ($request->has('priority') && $request->priority === 'urgent') ? 'urgent' : 'normal';

            Message::create([
                'conversation_id' => $conversation->conversation_id,
                'sender_id'       => auth()->id(),
                'sender_type'     => 'receptionist',
                'message_content' => $validated['message_content'],
                'priority'        => $priority,
                'is_read'         => false,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'         => true,
                    'conversation_id' => $conversation->conversation_id,
                    'message'         => 'Message sent successfully',
                ]);
            }

            return redirect()->route('receptionist.messages', ['conversation_id' => $conversation->conversation_id])
                ->with('success', 'Message sent successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Validation failed: ' . json_encode($e->errors()), 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Receptionist create message error: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to send message. Please try again.');
        }
    }

    private function resolveAdminForNewConversation(): ?User
    {
        $activeAdminUserIds = Admin::where('status', 'Active')->pluck('user_id');

        if ($activeAdminUserIds->isEmpty()) {
            return User::where('role', 'admin')->first();
        }

        $adminUserId = DB::table('users')
            ->select('users.id', DB::raw('COUNT(conversations.conversation_id) as open_conversations'))
            ->leftJoin('conversations', function ($join) {
                $join->on('conversations.admin_id', '=', 'users.id')
                    ->where('conversations.status', '=', 'active');
            })
            ->where('users.role', 'admin')
            ->whereIn('users.id', $activeAdminUserIds)
            ->groupBy('users.id')
            ->orderBy('open_conversations', 'asc')
            ->value('users.id');

        return $adminUserId ? User::find($adminUserId) : User::where('role', 'admin')->first();
    }

    public function send(Request $request)
    {
        try {
            $receptionist = Receptionist::where('user_id', auth()->id())->first();

            if (!$receptionist) {
                return response()->json(['success' => false, 'message' => 'Receptionist profile not found'], 404);
            }

            $validated = $request->validate([
                'conversation_id' => 'required|exists:conversations,conversation_id',
                'message_content' => 'required|string|max:5000',
                'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            ]);

            $conversation = Conversation::where('conversation_id', $validated['conversation_id'])
                ->where('receptionist_id', $receptionist->receptionist_id)
                ->first();

            if (!$conversation) {
                return response()->json(['success' => false, 'message' => 'Conversation not found or access denied'], 404);
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file           = $request->file('attachment');
                $fileName       = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $attachmentPath = $file->storeAs('message_attachments', $fileName, 'public');
            }

            $priority = ($request->has('priority') && $request->priority === 'urgent') ? 'urgent' : 'normal';

            $message = Message::create([
                'conversation_id' => $validated['conversation_id'],
                'sender_id'       => auth()->id(),
                'sender_type'     => 'receptionist',
                'message_content' => $validated['message_content'],
                'attachment_path' => $attachmentPath,
                'priority'        => $priority,
                'is_read'         => false,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json(['success' => true, 'message' => $message->load('sender')]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed: ' . json_encode($e->errors()), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Receptionist send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStar($id)
    {
        try {
            $receptionist = Receptionist::where('user_id', auth()->id())->first();
            if (!$receptionist) return response()->json(['success' => false, 'message' => 'Receptionist not found'], 404);

            $conversation = Conversation::where('conversation_id', $id)->where('receptionist_id', $receptionist->receptionist_id)->first();

            if ($conversation) {
                $conversation->update(['is_starred' => !$conversation->is_starred]);
                return response()->json(['success' => true, 'is_starred' => $conversation->is_starred]);
            }

            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed'], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $message = Message::find($id);
            if ($message && $message->sender_id != auth()->id()) {
                $message->update(['is_read' => true, 'read_at' => now()]);
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed'], 500);
        }
    }

    public function getTemplates()
    {
        try {
            $templates = MessageTemplate::where('is_active', true)->orderBy('template_name')->get();
            return response()->json(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch templates'], 500);
        }
    }
}