<!--admin_messages.blade.php-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Admin Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_messages.css'])
</head>

<body>

    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-envelope"></i> Messages
                @if($unreadCount > 0)
                <span class="unread-count-badge">{{ $unreadCount }}</span>
                @endif
            </h1>
            <div class="header-actions">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search conversations..." onkeyup="searchMessages(this.value)">
                    <span class="search-icon">🔍</span>
                </div>
                <button class="btn-new-message" onclick="openNewMessageModal()">
                    <i class="fas fa-plus"></i> New Message
                </button>
            </div>
        </div>

        <div class="messages-container">
            <!-- Inbox List -->
            <div class="inbox-panel">
                <div class="panel-header">
                    <h2><i class="fas fa-inbox"></i> Conversations</h2>
                    <button class="btn-filter" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="message-tabs">
                    <button class="tab-btn {{ $type == 'all' ? 'active' : '' }}" data-type="all">
                        All
                        @if($unreadCount > 0)
                        <span class="tab-badge">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <button class="tab-btn {{ $type == 'doctors' ? 'active' : '' }}" data-type="doctors">
                        <i class="fas fa-user-md"></i> Doctors
                    </button>
                    <button class="tab-btn {{ $type == 'starred' ? 'active' : '' }}" data-type="starred">
                        <i class="fas fa-star"></i> Starred
                    </button>
                    <button class="tab-btn {{ $type == 'urgent' ? 'active' : '' }}" data-type="urgent">
                        <i class="fas fa-exclamation-triangle"></i> Urgent
                    </button>
                </div>

                <div class="conversations-list" id="conversationsList">
                    @forelse($conversations as $conversation)
                    <div class="conversation-item {{ $selectedConversation && $selectedConversation->conversation_id == $conversation->conversation_id ? 'active' : '' }}"
                        data-conversation-id="{{ $conversation->conversation_id }}"
                        onclick="selectConversation({{ $conversation->conversation_id }})">
                        <div class="conversation-avatar">
                            <span class="avatar-text">{{ substr($conversation->doctor->user->name ?? 'D', 0, 1) }}</span>
                            <span class="online-status {{ $conversation->doctor->user->isOnline() ? 'online' : 'offline' }}"></span>
                        </div>
                        <div class="conversation-details">
                            <div class="conversation-header">
                                <h4>
                                    <i class="fas fa-user-md"></i>
                                    {{ $conversation->doctor->user->name ?? 'Doctor' }}
                                </h4>
                                <span class="time">{{ $conversation->last_message_at?->diffForHumans() ?? 'New' }}</span>
                            </div>
                            <p class="last-message">
                                @if($conversation->latestMessage)
                                @if($conversation->latestMessage->priority == 'urgent')
                                🚨
                                @endif
                                {{ Str::limit($conversation->latestMessage->message_content, 45) }}
                                @else
                                <em>No messages yet</em>
                                @endif
                            </p>
                            @if($conversation->getUnreadCount(auth()->id()) > 0)
                            <span class="unread-badge">{{ $conversation->getUnreadCount(auth()->id()) }}</span>
                            @endif
                            @if($conversation->subject)
                            <small class="subject-tag"><i class="fas fa-tag"></i> {{ $conversation->subject }}</small>
                            @endif
                        </div>
                        <button class="btn-star {{ $conversation->is_starred ?? false ? 'starred' : '' }}"
                            onclick="event.stopPropagation(); toggleStar({{ $conversation->conversation_id }})">
                            <i class="fas fa-star"></i>
                        </button>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px;"></i>
                        <p>No conversations yet</p>
                        <small>Start a new conversation with a doctor</small>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Chat Window -->
            <div class="chat-panel">
                @if($selectedConversation)
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="doctor-info">
                            <h3>
                                <i class="fas fa-user-md"></i>
                                {{ $selectedConversation->doctor->user->name ?? 'Doctor' }}
                            </h3>
                            <span class="status-badge {{ $selectedConversation->doctor->user->isOnline() ? 'online' : 'offline' }}">
                                <i class="fas fa-circle"></i>
                                {{ $selectedConversation->doctor->user->isOnline() ? 'Online' : 'Offline' }}
                            </span>

                            @if(!$selectedConversation->doctor->user->isOnline() && $selectedConversation->doctor->user->last_seen_at)
                            <small class="last-seen-text">
                                Last seen {{ $selectedConversation->doctor->user->last_seen_at->diffForHumans() }}
                            </small>
                            @endif
                        </div>

                        @if($selectedConversation->subject)
                        <small class="subject-line">
                            <i class="fas fa-tag"></i> {{ $selectedConversation->subject }}
                        </small>
                        @endif
                        @if($selectedConversation->doctor->specialization)
                        <small class="specialization">
                            <i class="fas fa-stethoscope"></i> {{ $selectedConversation->doctor->specialization }}
                        </small>
                        @endif
                    </div>
                    <div class="chat-header-actions">
                        <button class="btn-icon" onclick="toggleStar({{ $selectedConversation->conversation_id }})" title="Star conversation">
                            <i class="fas fa-star {{ $selectedConversation->is_starred ?? false ? 'starred' : '' }}"></i>
                        </button>
                        <button class="btn-icon" onclick="viewDoctorProfile({{ $selectedConversation->doctor->doctor_id }})" title="View Profile">
                            <i class="fas fa-id-card"></i>
                        </button>
                        <button class="btn-icon" onclick="toggleMoreOptions()" title="More">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Actions Bar -->
                <div class="quick-actions">
                    <button class="action-btn" onclick="openTemplatesModal()">
                        <i class="fas fa-file-alt"></i> Templates
                    </button>
                    <button class="action-btn" onclick="insertTemplate('Thank you for reaching out. Your request has been noted.')">
                        <i class="fas fa-check"></i> Acknowledge
                    </button>
                    <button class="action-btn" onclick="insertTemplate('Your request has been approved. You may proceed.')">
                        <i class="fas fa-thumbs-up"></i> Approve
                    </button>
                    <button class="action-btn" onclick="insertTemplate('Please provide more details regarding your request.')">
                        <i class="fas fa-question-circle"></i> Request Info
                    </button>
                </div>

                <!-- Chat Messages -->
                <div class="chat-messages" id="chatWindow" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                    @foreach($messages as $message)
                    <div class="message-wrapper {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}" data-message-id="{{ $message->message_id }}">
                        <div class="message-bubble {{ $message->priority == 'urgent' ? 'urgent' : '' }}">
                            <div class="message-header">
                                <span class="sender-name">
                                    @if($message->sender_id == auth()->id())
                                    <i class="fas fa-user-shield"></i> You (Admin)
                                    @else
                                    <i class="fas fa-user-md"></i> {{ $message->sender->name }}
                                    @endif
                                </span>
                                @if($message->priority == 'urgent')
                                <span class="urgent-badge"><i class="fas fa-exclamation-triangle"></i> Urgent</span>
                                @endif
                            </div>
                            <p>{{ $message->message_content }}</p>
                            @if($message->attachment_path)
                            <a href="{{ asset('storage/' . $message->attachment_path) }}" class="message-attachment" target="_blank">
                                <i class="fas fa-paperclip"></i> {{ basename($message->attachment_path) }}
                            </a>
                            @endif
                            <div class="message-footer">
                                <span class="message-time">
                                    <i class="far fa-clock"></i>
                                    {{ $message->created_at->format('M d, g:i A') }}
                                </span>
                                @if($message->sender_id == auth()->id())
                                <span class="message-status">
                                    @if($message->is_read)
                                    <span class="status-read"><i class="fas fa-check-double"></i> Read</span>
                                    @else
                                    <span class="status-sent"><i class="fas fa-check"></i> Sent</span>
                                    @endif
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Typing Indicator -->
                    <div class="typing-indicator" id="typingIndicator" style="display: none;">
                        <div class="typing-dots">
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                        </div>
                        <span class="typing-text">Doctor is typing...</span>
                    </div>
                </div>

                <!-- Input Form -->
                <form class="chat-input-form" id="messageForm" onsubmit="sendMessage(event)">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $selectedConversation->conversation_id }}">

                    <div class="input-actions">
                        <input type="file" name="attachment" id="attachment" style="display: none;" onchange="showAttachmentPreview(this)">

                        <button type="button" class="btn-icon" onclick="document.getElementById('attachment').click()" title="Attach file">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <button type="button" class="btn-icon" onclick="openEmojiPicker()" title="Add emoji">
                            <i class="far fa-smile"></i>
                        </button>

                        <button type="button" class="btn-icon" onclick="openTemplatesModal()" title="Use template">
                            <i class="fas fa-file-alt"></i>
                        </button>

                        <label class="priority-toggle">
                            <input type="checkbox" name="priority" id="urgentCheckbox" value="urgent">
                            <span class="urgent-label" title="Mark as urgent">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                        </label>
                    </div>

                    <div class="input-wrapper">
                        <textarea
                            name="message_content"
                            id="messageInput"
                            class="message-input"
                            placeholder="Type your message... (Shift+Enter for new line)"
                            rows="1"
                            onkeydown="handleKeyPress(event)"
                            oninput="autoResize(this); showTyping()"
                            required></textarea>
                        <button type="submit" class="btn-send" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                            <span class="send-text">Send</span>
                        </button>
                    </div>

                    <div id="attachmentPreview" class="attachment-preview" style="display: none;">
                        <span class="preview-text"></span>
                        <button type="button" class="btn-remove" onclick="removeAttachment()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>
                @else
                <div class="empty-chat-state">
                    <i class="fas fa-comments" style="font-size: 80px; opacity: 0.2; margin-bottom: 20px;"></i>
                    <h3>Select a conversation</h3>
                    <p>Choose a conversation from the list or start a new one</p>
                    <button class="btn-primary" onclick="openNewMessageModal()">
                        <i class="fas fa-plus"></i> Start New Conversation
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Drag & Drop Overlay -->
    <div id="dropOverlay" class="drop-overlay" style="display: none;">
        <div class="drop-content">
            <div class="drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <h3>Drop file to attach</h3>
            <p>PDF, JPG, PNG, DOC (Max 5MB)</p>
        </div>
    </div>

    <!-- New Message Modal -->
    <div id="newMessageModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('newMessageModal')">
                <i class="fas fa-times"></i>
            </button>
            <h2><i class="fas fa-envelope"></i> New Message to Doctor</h2>
            <form action="{{ route('admin.messages.create') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label><i class="fas fa-user-md"></i> Select Doctor</label>
                    <select name="doctor_id" required>
                        <option value="">Choose a doctor</option>
                        @foreach($allDoctors as $doctor)
                        <option value="{{ $doctor->doctor_id }}">
                            {{ $doctor->user->name }} - {{ $doctor->specialization }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Subject</label>
                    <input type="text" name="subject" placeholder="e.g., Leave Request Inquiry" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Message</label>
                    <textarea name="message_content" rows="5" placeholder="Type your message..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="priority" value="urgent">
                        <span><i class="fas fa-exclamation-triangle"></i> Mark as Urgent</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Templates Modal -->
    <div id="templatesModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('templatesModal')">
                <i class="fas fa-times"></i>
            </button>
            <h2><i class="fas fa-file-alt"></i> Message Templates</h2>
            <div class="templates-list">
                @foreach($templates as $template)
                <div class="template-item" onclick="useTemplate('{{ addslashes($template->template_content) }}')">
                    <h4><i class="fas fa-clipboard"></i> {{ $template->template_name }}</h4>
                    <p>{{ Str::limit($template->template_content, 80) }}</p>
                    <button class="btn-use">Use Template</button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Emoji Picker -->
    <div id="emojiPicker" class="emoji-picker" style="display: none;">
        <div class="emoji-grid">
            <span onclick="insertEmoji('😊')">😊</span>
            <span onclick="insertEmoji('👍')">👍</span>
            <span onclick="insertEmoji('❤️')">❤️</span>
            <span onclick="insertEmoji('✅')">✅</span>
            <span onclick="insertEmoji('🙏')">🙏</span>
            <span onclick="insertEmoji('👏')">👏</span>
            <span onclick="insertEmoji('💪')">💪</span>
            <span onclick="insertEmoji('🎉')">🎉</span>
            <span onclick="insertEmoji('⚠️')">⚠️</span>
            <span onclick="insertEmoji('📅')">📅</span>
            <span onclick="insertEmoji('💊')">💊</span>
            <span onclick="insertEmoji('🏥')">🏥</span>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom
        const chatWindow = document.getElementById('chatWindow');
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        // Handle keyboard shortcuts
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                document.getElementById('sendBtn').click();
            }
        }

        // Send message with proper AJAX
        function sendMessage(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const sendBtn = document.getElementById('sendBtn');

            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            fetch('{{ route("admin.messages.send") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        addMessageToChat(data.message);
                        form.reset();
                        document.getElementById('messageInput').style.height = 'auto';
                        document.getElementById('attachmentPreview').style.display = 'none';
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    } else {
                        alert(data.message || 'Failed to send message. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Failed to send message. Please refresh the page and try again.');
                })
                .finally(() => {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span class="send-text">Send</span>';
                });
        }

        // Add message to chat
        function addMessageToChat(message) {
            const messageHtml = `
        <div class="message-wrapper sent" data-message-id="${message.message_id}">
          <div class="message-bubble ${message.priority === 'urgent' ? 'urgent' : ''}">
            <div class="message-header">
              <span class="sender-name"><i class="fas fa-user-shield"></i> You (Admin)</span>
              ${message.priority === 'urgent' ? '<span class="urgent-badge"><i class="fas fa-exclamation-triangle"></i> Urgent</span>' : ''}
            </div>
            <p>${message.message_content}</p>
            ${message.attachment_path ? `<a href="/storage/${message.attachment_path}" class="message-attachment" target="_blank"><i class="fas fa-paperclip"></i> ${message.attachment_path.split('/').pop()}</a>` : ''}
            <div class="message-footer">
              <span class="message-time"><i class="far fa-clock"></i> Just now</span>
              <span class="message-status"><span class="status-sent"><i class="fas fa-check"></i> Sent</span></span>
            </div>
          </div>
        </div>
      `;

            chatWindow.insertAdjacentHTML('beforeend', messageHtml);
        }

        // ✅ FIXED: Select conversation with tab preservation
        function selectConversation(id) {
            const currentType = '{{ $type }}';
            window.location.href = `{{ route('admin.messages') }}?conversation_id=${id}&type=${currentType}`;
        }

        // Toggle star
        function toggleStar(id) {
            fetch(`/admin/messages/${id}/toggle-star`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        // Search messages
        function searchMessages(query) {
            const url = new URL(window.location.href);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            window.location.href = url;
        }

        // Template functions
        function insertTemplate(text) {
            const input = document.getElementById('messageInput');
            input.value = text;
            input.focus();
            autoResize(input);
        }

        function useTemplate(text) {
            insertTemplate(text);
            closeModal('templatesModal');
        }

        function openTemplatesModal() {
            openModal('templatesModal');
        }

        // Emoji functions
        function openEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
        }

        function insertEmoji(emoji) {
            const input = document.getElementById('messageInput');
            input.value += emoji;
            input.focus();
            document.getElementById('emojiPicker').style.display = 'none';
        }

        // Attachment functions
        function showAttachmentPreview(input) {
            const preview = document.getElementById('attachmentPreview');
            const text = preview.querySelector('.preview-text');

            if (input.files && input.files[0]) {
                text.textContent = input.files[0].name;
                preview.style.display = 'flex';
            }
        }

        function removeAttachment() {
            document.getElementById('attachment').value = '';
            document.getElementById('attachmentPreview').style.display = 'none';
        }

        // Drag & Drop
        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('dropOverlay').style.display = 'flex';
        }

        function handleDragLeave(e) {
            if (e.target.id === 'dropOverlay') {
                document.getElementById('dropOverlay').style.display = 'none';
            }
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('dropOverlay').style.display = 'none';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const input = document.getElementById('attachment');
                input.files = files;
                showAttachmentPreview(input);
            }
        }

        // View doctor profile
        function viewDoctorProfile(doctorId) {
            window.location.href = `/admin/doctors/${doctorId}`;
        }

        function toggleMoreOptions() {
            alert('Additional options coming soon');
        }

        // Modal functions
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openNewMessageModal() {
            openModal('newMessageModal');
        }

        function toggleFilters() {
            alert('Filter options coming soon');
        }

        // ✅ FIXED: Tab switching with preserved state
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const url = new URL(window.location.href);
                url.searchParams.set('type', type);
                url.searchParams.delete('conversation_id');
                window.location.href = url;
            });
        });

        // Close emoji picker when clicking outside
        document.addEventListener('click', function(event) {
            const emojiPicker = document.getElementById('emojiPicker');
            const emojiBtn = event.target.closest('[onclick*="openEmojiPicker"]');

            if (!emojiPicker.contains(event.target) && !emojiBtn) {
                emojiPicker.style.display = 'none';
            }
        });

        // Typing indicator
        let typingTimer;

        function showTyping() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                // Stop typing indicator
            }, 2000);
        }
    </script>

</body>

</html>