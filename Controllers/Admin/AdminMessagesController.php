<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Doctor;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Auth;

class AdminMessagesController extends Controller
{

    use \App\Traits\MessagePollingTrait;

protected function getParticipantColumn(): string
{
    return 'admin_id';
}

protected function getParticipantId(): int
{
    return auth()->id();
}

    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $search = $request->get('search');

        $conversationsQuery = Conversation::where('conversation_type', 'doctor_admin')
            ->where('admin_id', auth()->id());

        if ($type === 'archived') {
            $conversationsQuery->where('status', 'archived');
        } else {
            $conversationsQuery->where('status', '!=', 'archived');
        }

        $conversationsQuery->with(['latestMessage', 'doctor.user', 'doctor']);

        if ($type === 'starred' && $type !== 'archived') {
            $conversationsQuery->where('is_starred', true);
        } elseif ($type === 'urgent' && $type !== 'archived') {
            $conversationsQuery->whereHas('messages', function ($q) {
                $q->where('priority', 'urgent')
                    ->where('is_read', false);
            });
        }

        if ($search) {
            $conversationsQuery->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('message_content', 'like', "%{$search}%");
                    })
                    ->orWhereHas('doctor.user', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $conversations = $conversationsQuery
            ->orderBy('last_message_at', 'desc')
            ->get();

        $conversationId = $request->get('conversation_id');
        $selectedConversation = null;
        $messages = collect();

        if ($conversationId) {
            $selectedConversation = Conversation::where('conversation_id', $conversationId)
                ->where('admin_id', auth()->id())
                ->with(['doctor.user', 'doctor'])
                ->first();

            if ($selectedConversation) {
                $messages = $selectedConversation->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get();

                $selectedConversation->markAllAsRead(auth()->id());
            }
        }

        $allDoctors = Doctor::with('user')->get();
        $templates = MessageTemplate::where('is_active', true)->get();

        $unreadCount = Conversation::where('admin_id', auth()->id())
            ->where('conversation_type', 'doctor_admin')
            ->where('status', '!=', 'archived')
            ->get()
            ->sum(function ($conv) {
                return $conv->getUnreadCount(auth()->id());
            });

        return view('admin.admin_messages', compact(
            'conversations',
            'selectedConversation',
            'messages',
            'allDoctors',
            'templates',
            'unreadCount',
            'type'
        ));
    }

    /**
     * Returns unread message count for sidebar badge polling.
     */
    public function unreadCount()
    {
        $count = Conversation::where('admin_id', auth()->id())
            ->where('conversation_type', 'doctor_admin')
            ->where('status', '!=', 'archived')
            ->get()
            ->sum(fn($conv) => $conv->getUnreadCount(auth()->id()));

        return response()->json(['count' => $count, 'severity' => 'normal']);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'subject' => 'required|string|max:255',
            'message_content' => 'required|string',
            'priority' => 'nullable|in:urgent',
        ]);

        $existingConversation = Conversation::where('conversation_type', 'doctor_admin')
            ->where('doctor_id', $validated['doctor_id'])
            ->where('admin_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($existingConversation) {
            Message::create([
                'conversation_id' => $existingConversation->conversation_id,
                'sender_id' => auth()->id(),
                'sender_type' => 'admin',
                'message_content' => $validated['message_content'],
                'priority' => $validated['priority'] ?? 'normal',
                'is_read' => false,
            ]);

            $existingConversation->update([
                'last_message_at' => now(),
                'subject' => $validated['subject']
            ]);

            return redirect()->route('admin.messages', ['conversation_id' => $existingConversation->conversation_id])
                ->with('success', 'Message sent successfully');
        }

        $conversation = Conversation::create([
            'conversation_type' => 'doctor_admin',
            'doctor_id' => $validated['doctor_id'],
            'admin_id' => auth()->id(),
            'subject' => $validated['subject'],
            'status' => 'active',
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->conversation_id,
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'message_content' => $validated['message_content'],
            'priority' => $validated['priority'] ?? 'normal',
            'is_read' => false,
        ]);

        return redirect()->route('admin.messages', ['conversation_id' => $conversation->conversation_id])
            ->with('success', 'Conversation started successfully');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,conversation_id',
            'message_content' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'priority' => 'nullable|in:urgent',
        ]);

        $conversation = Conversation::where('conversation_id', $validated['conversation_id'])
            ->where('admin_id', auth()->id())
            ->firstOrFail();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $attachmentPath = $file->storeAs('message_attachments', $fileName, 'public');
        }

        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => auth()->id(),
            'sender_type' => 'admin',
            'message_content' => $validated['message_content'],
            'attachment_path' => $attachmentPath,
            'priority' => $validated['priority'] ?? 'normal',
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    public function archive($id)
    {
        $conversation = Conversation::where('conversation_id', $id)
            ->where('admin_id', auth()->id())
            ->first();

        if ($conversation) {
            $conversation->update(['status' => 'archived']);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function toggleStar($id)
    {
        $conversation = Conversation::where('conversation_id', $id)
            ->where('admin_id', auth()->id())
            ->first();

        if ($conversation) {
            $conversation->update(['is_starred' => !$conversation->is_starred]);
            return response()->json([
                'success' => true,
                'is_starred' => $conversation->is_starred
            ]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAsRead($id)
    {
        $message = Message::find($id);

        if ($message && $message->sender_id != auth()->id()) {
            $message->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function getTemplates()
    {
        $templates = MessageTemplate::where('is_active', true)->get();
        return response()->json($templates);
    }
}