<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Receptionist;
use App\Models\Pharmacist;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DoctorMessagesController extends Controller
{
    use \App\Traits\MessagePollingTrait;

    protected function getParticipantColumn(): string { return 'doctor_id'; }
    protected function getParticipantId(): int
    {
        return \App\Models\Doctor::where('user_id', auth()->id())->value('doctor_id');
    }

    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found');
        }

        $type   = $request->get('type', 'all');
        $search = $request->get('search');

        $conversationsQuery = Conversation::where(function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->doctor_id);
            })
            ->where('status', 'active')
            ->with(['latestMessage', 'patient.user', 'admin', 'nurse.user', 'receptionist.user', 'pharmacist.user']);

        if ($type === 'admin') {
            $conversationsQuery->where('conversation_type', 'doctor_admin');
        } elseif ($type === 'nurse') {
            $conversationsQuery->whereIn('conversation_type', ['doctor_nurse', 'nurse_doctor']);
        } elseif ($type === 'patient') {
            $conversationsQuery->where('conversation_type', 'doctor_patient');
        } elseif ($type === 'receptionist') {
            $conversationsQuery->whereIn('conversation_type', ['doctor_receptionist', 'receptionist_doctor']);
        } elseif ($type === 'pharmacist') {
            $conversationsQuery->whereIn('conversation_type', ['doctor_pharmacist', 'pharmacist_doctor']);
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
                ->where('doctor_id', $doctor->doctor_id)
                ->with(['patient.user', 'admin', 'nurse.user', 'receptionist.user', 'pharmacist.user'])
                ->first();
            if ($selectedConversation) {
                $messages = $selectedConversation->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();

                $selectedConversation->markAllAsRead(auth()->id());
            }
        }

        $myPatients = Patient::whereHas('appointments', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->doctor_id);
        })
            ->with('user')
            ->distinct()
            ->get();

        $myNurses = $doctor->assignedNurses()->with('user')->get();

        $receptionists = Receptionist::where('availability_status', 'Available')
            ->with('user')
            ->get();

        $pharmacists = Pharmacist::where('availability_status', 'Available')
            ->with('user')
            ->get();

        $templates = MessageTemplate::where('is_active', true)->get();

        $unreadCount = Conversation::where('doctor_id', $doctor->doctor_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($conv) => $conv->getUnreadCount(auth()->id()));

        return view('doctor.doctor_messages', [
            'doctor'               => $doctor,
            'conversations'        => $conversations,
            'selectedConversation' => $selectedConversation,
            'messages'             => $messages,
            'myPatients'           => $myPatients,
            'myNurses'             => $myNurses,
            'receptionists'        => $receptionists,
            'pharmacists'          => $pharmacists,
            'templates'            => $templates,
            'unreadCount'          => $unreadCount,
            'type'                 => $type,
        ]);
    }

    /**
     * Returns unread message count for sidebar badge polling.
     */
    public function unreadCount()
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        if (!$doctor) {
            return response()->json(['count' => 0, 'severity' => 'normal']);
        }

        $count = Conversation::where('doctor_id', $doctor->doctor_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($conv) => $conv->getUnreadCount(auth()->id()));

        return response()->json(['count' => $count, 'severity' => 'normal']);
    }

    public function create(Request $request)
    {
        try {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
            }

            $validated = $request->validate([
                'recipient_type'  => 'required|in:admin,nurse,patient,receptionist,pharmacist',
                'nurse_id'        => 'required_if:recipient_type,nurse|nullable|exists:nurses,nurse_id',
                'patient_id'      => 'required_if:recipient_type,patient|nullable|exists:patients,patient_id',
                'receptionist_id' => 'required_if:recipient_type,receptionist|nullable|exists:receptionists,receptionist_id',
                'pharmacist_id'   => 'required_if:recipient_type,pharmacist|nullable|exists:pharmacists,pharmacist_id',
                'subject'         => 'required|string|max:255',
                'message_content' => 'required|string|max:5000',
            ]);

            $newSubject   = trim($validated['subject']);
            $conversation = null;

            switch ($validated['recipient_type']) {

                case 'admin':
                    $conversation = Conversation::where('doctor_id', $doctor->doctor_id)
                        ->where('conversation_type', 'doctor_admin')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->latest('last_message_at')
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $assignedAdmin = $this->resolveAdminForNewConversation();

                        if (!$assignedAdmin) {
                            return response()->json([
                                'success' => false,
                                'message' => 'No admin staff are currently available.',
                            ], 503);
                        }

                        $conversation = Conversation::create([
                            'doctor_id'         => $doctor->doctor_id,
                            'admin_id'          => $assignedAdmin->id,
                            'conversation_type' => 'doctor_admin',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'nurse':
                    $nurseId = $validated['nurse_id'];

                    $isAssigned = $doctor->assignedNurses()
                        ->where('nurses.nurse_id', $nurseId)
                        ->exists();

                    if (!$isAssigned) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This nurse is not assigned to your team.',
                        ], 403);
                    }

                    $conversation = Conversation::where('doctor_id', $doctor->doctor_id)
                        ->where('nurse_id', $nurseId)
                        ->where('conversation_type', 'doctor_nurse')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'doctor_id'         => $doctor->doctor_id,
                            'nurse_id'          => $nurseId,
                            'conversation_type' => 'doctor_nurse',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'patient':
                    $conversation = Conversation::where('doctor_id', $doctor->doctor_id)
                        ->where('patient_id', $validated['patient_id'])
                        ->where('conversation_type', 'doctor_patient')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'doctor_id'         => $doctor->doctor_id,
                            'patient_id'        => $validated['patient_id'],
                            'conversation_type' => 'doctor_patient',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'receptionist':
                    $conversation = Conversation::where('doctor_id', $doctor->doctor_id)
                        ->where('receptionist_id', $validated['receptionist_id'])
                        ->where('conversation_type', 'doctor_receptionist')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'doctor_id'         => $doctor->doctor_id,
                            'receptionist_id'   => $validated['receptionist_id'],
                            'conversation_type' => 'doctor_receptionist',
                            'subject'           => $newSubject,
                            'status'            => 'active',
                            'last_message_at'   => now(),
                        ]);
                    }
                    break;

                case 'pharmacist':
                    $conversation = Conversation::where('doctor_id', $doctor->doctor_id)
                        ->where('pharmacist_id', $validated['pharmacist_id'])
                        ->where('conversation_type', 'doctor_pharmacist')
                        ->where('status', 'active')
                        ->whereRaw('LOWER(TRIM(subject)) = ?', [strtolower($newSubject)])
                        ->first();

                    if ($conversation) {
                        $conversation->update(['last_message_at' => now()]);
                    } else {
                        $conversation = Conversation::create([
                            'doctor_id'         => $doctor->doctor_id,
                            'pharmacist_id'     => $validated['pharmacist_id'],
                            'conversation_type' => 'doctor_pharmacist',
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
                'sender_type'     => 'doctor',
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

            return redirect()->route('doctor.messages', [
                'conversation_id' => $conversation->conversation_id,
            ])->with('success', 'Message sent successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in create message:', ['errors' => $e->errors()]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . json_encode($e->errors()),
                    'errors'  => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error creating message: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message: ' . $e->getMessage(),
                ], 500);
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
            $doctor = Doctor::where('user_id', auth()->id())->first();

            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
            }

            $validated = $request->validate([
                'conversation_id' => 'required|exists:conversations,conversation_id',
                'message_content' => 'required|string|max:5000',
                'attachment'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            ]);

            $conversation = Conversation::where('conversation_id', $validated['conversation_id'])
                ->where('doctor_id', $doctor->doctor_id)
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
                'sender_type'     => 'doctor',
                'message_content' => $validated['message_content'],
                'attachment_path' => $attachmentPath,
                'priority'        => $priority,
                'is_read'         => false,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json(['success' => true, 'message' => $message->load('sender')]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . json_encode($e->errors()),
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()], 500);
        }
    }

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
                    'success'    => true,
                    'is_starred' => $conversation->is_starred,
                    'message'    => $conversation->is_starred ? 'Conversation starred' : 'Conversation unstarred',
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);

        } catch (\Exception $e) {
            Log::error('Error toggling star: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to toggle star'], 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $message = Message::find($id);

            if ($message && $message->sender_id != auth()->id()) {
                $message->update(['is_read' => true, 'read_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Message marked as read']);
            }

            return response()->json(['success' => false, 'message' => 'Message not found'], 404);

        } catch (\Exception $e) {
            Log::error('Error marking message as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark as read'], 500);
        }
    }

    public function getTemplates()
    {
        try {
            $templates = MessageTemplate::where('is_active', true)->orderBy('template_name')->get();
            return response()->json(['success' => true, 'templates' => $templates]);
        } catch (\Exception $e) {
            Log::error('Error fetching templates: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch templates'], 500);
        }
    }
}