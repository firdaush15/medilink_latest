{{-- resources/views/doctor/doctor_messages.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MediLink | Messages</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_messages.css'])
</head>

<body>

  @include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <div class="page-header">
      <h1 class="page-title">
        Messages
        @if($unreadCount > 0)
        <span class="unread-count-badge">{{ $unreadCount }}</span>
        @endif
      </h1>
      <div class="header-actions">
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search messages..." onkeyup="searchMessages(this.value)">
          <span class="search-icon">🔍</span>
        </div>
        <button class="btn-new-message" onclick="openNewMessageModal()">
          <span>✉️</span> New Message
        </button>
      </div>
    </div>

    <div class="messages-container">
      <!-- Inbox List -->
      <div class="inbox-panel">
        <div class="panel-header">
          <h2>Conversations</h2>
          <button class="btn-filter" onclick="toggleFilters()">
            <span>⚙️</span>
          </button>
        </div>

        <!-- Enhanced Tabs -->
        <div class="message-tabs">
          <button class="tab-btn {{ $type == 'all' ? 'active' : '' }}" data-type="all">
            All
            @if($unreadCount > 0)
            <span class="tab-badge">{{ $unreadCount }}</span>
            @endif
          </button>
          <button class="tab-btn {{ $type == 'admin' ? 'active' : '' }}" data-type="admin">
            Admin
          </button>
          <button class="tab-btn {{ $type == 'patient' ? 'active' : '' }}" data-type="patient">
            Patients
          </button>
          <button class="tab-btn {{ $type == 'starred' ? 'active' : '' }}" data-type="starred">
            ⭐ Starred
          </button>
        </div>

        <div class="conversations-list" id="conversationsList">
          @forelse($conversations as $conversation)
          @php
          $recipientUser = $conversation->conversation_type == 'doctor_admin'
          ? $conversation->admin
          : ($conversation->patient->user ?? null);
          @endphp
          <div class="conversation-item {{ $selectedConversation && $selectedConversation->conversation_id == $conversation->conversation_id ? 'active' : '' }}"
            data-conversation-id="{{ $conversation->conversation_id }}"
            onclick="selectConversation({{ $conversation->conversation_id }})">
            <div class="conversation-avatar {{ $conversation->conversation_type == 'doctor_admin' ? 'admin-avatar' : '' }}">
              @if($conversation->conversation_type == 'doctor_admin')
              <span class="avatar-icon">👤</span>
              @else
              {{ substr($conversation->patient->user->name ?? 'P', 0, 1) }}
              @endif
              @if($recipientUser)
              <span class="online-status {{ $recipientUser->isOnline() ? 'online' : 'offline' }}"></span>
              @endif
            </div>
            <div class="conversation-details">
              <div class="conversation-header">
                <h4>
                  @if($conversation->conversation_type == 'doctor_admin')
                  Admin Support
                  @else
                  {{ $conversation->patient->user->name ?? 'Patient' }}
                  @endif
                </h4>
                <span class="time">{{ $conversation->last_message_at?->diffForHumans() ?? 'New' }}</span>
              </div>
              <p class="last-message">
                @if($conversation->latestMessage)
                {{ Str::limit($conversation->latestMessage->message_content, 40) }}
                @else
                Start a conversation
                @endif
              </p>
              @if($conversation->getUnreadCount(auth()->id()) > 0)
              <span class="unread-badge">{{ $conversation->getUnreadCount(auth()->id()) }}</span>
              @endif
            </div>
            <button class="btn-star {{ $conversation->is_starred ?? false ? 'starred' : '' }}"
              onclick="event.stopPropagation(); toggleStar({{ $conversation->conversation_id }})">
              ⭐
            </button>
          </div>
          @empty
          <div class="empty-state">
            <p>No conversations yet</p>
            <small>Start a new conversation</small>
          </div>
          @endforelse
        </div>
      </div>

      <!-- Chat Window -->
      <div class="chat-panel">
        @if($selectedConversation)
        @php
        $recipientUser = $selectedConversation->conversation_type == 'doctor_admin'
        ? $selectedConversation->admin
        : ($selectedConversation->patient->user ?? null);
        @endphp
        <div class="chat-header">
          <div class="chat-header-info">
            <div class="recipient-info">
              <h3>
                @if($selectedConversation->conversation_type == 'doctor_admin')
                Admin Support
                @else
                {{ $selectedConversation->patient->user->name ?? 'Patient' }}
                @endif
              </h3>

              @if($recipientUser)
              @if($recipientUser->isOnline())
              <span class="status-badge online">
                <span class="status-dot"></span>
                Online
              </span>
              @else
              <span class="status-badge offline">
                <span class="status-dot"></span>
                Offline
              </span>

              @if($recipientUser->last_seen_at)
              <small class="last-seen-text">
                Last seen {{ $recipientUser->last_seen_at->diffForHumans() }}
              </small>
              @endif
              @endif
              @endif
            </div>

            @if($selectedConversation->subject)
            <small class="subject-line">{{ $selectedConversation->subject }}</small>
            @endif
          </div>

          <div class="chat-header-actions">
            <button class="btn-icon"
              onclick="toggleStar({{ $selectedConversation->conversation_id }})"
              title="Star conversation">
              <span class="{{ $selectedConversation->is_starred ?? false ? 'starred' : '' }}">⭐</span>
            </button>
            <button class="btn-icon"
              onclick="alert('More options coming soon')"
              title="More">
              ⋮
            </button>
          </div>
        </div>

        <!-- Quick Templates Bar -->
        <div class="quick-templates">
          <button class="template-btn" onclick="openTemplatesModal()">
            📝 Templates
          </button>
          <button class="template-btn" onclick="insertTemplate('Your appointment is confirmed.')">
            ✅ Confirm Appointment
          </button>
          <button class="template-btn" onclick="insertTemplate('Your lab results are ready. Please call to discuss.')">
            🔬 Lab Results Ready
          </button>
          <button class="template-btn" onclick="insertTemplate('Please schedule a follow-up appointment.')">
            📅 Follow-up
          </button>
        </div>

        <!-- Chat Messages with Scroll -->
        <div class="chat-messages" id="chatWindow" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
          @foreach($messages as $message)
          <div class="message-wrapper {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}" data-message-id="{{ $message->message_id }}">
            <div class="message-bubble {{ $message->priority == 'urgent' ? 'urgent' : '' }}">
              @if($message->priority == 'urgent')
              <span class="urgent-badge">🚨 Urgent</span>
              @endif
              <p>{{ $message->message_content }}</p>
              @if($message->attachment_path)
              <a href="{{ asset('storage/' . $message->attachment_path) }}" class="message-attachment" target="_blank">
                📎 {{ basename($message->attachment_path) }}
              </a>
              @endif
              <div class="message-footer">
                <span class="message-time">{{ $message->created_at->format('g:i A') }}</span>
                @if($message->sender_id == auth()->id())
                <span class="message-status">
                  @if($message->is_read)
                  <span class="status-read">✓✓ Read</span>
                  @else
                  <span class="status-sent">✓ Sent</span>
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
            <span class="typing-text">Typing...</span>
          </div>
        </div>

        <!-- Enhanced Input Form -->
        <form class="chat-input-form" id="messageForm" onsubmit="sendMessage(event)">
          @csrf
          <input type="hidden" name="conversation_id" value="{{ $selectedConversation->conversation_id }}">

          <div class="input-actions">
            <input type="file" name="attachment" id="attachment" style="display: none;" onchange="showAttachmentPreview(this)">

            <button type="button" class="btn-icon" onclick="document.getElementById('attachment').click()" title="Attach file">
              📎
            </button>

            <button type="button" class="btn-icon" onclick="openEmojiPicker()" title="Add emoji">
              😊
            </button>

            <button type="button" class="btn-icon" onclick="openTemplatesModal()" title="Use template">
              📝
            </button>

            <label class="priority-toggle">
              <input type="checkbox" name="priority" id="urgentCheckbox" value="urgent">
              <span class="urgent-label" title="Mark as urgent">🚨</span>
            </label>
          </div>

          <div class="input-wrapper">
            <textarea
              name="message_content"
              id="messageInput"
              class="message-input"
              placeholder="Type a message... (Shift+Enter for new line)"
              rows="1"
              onkeydown="handleKeyPress(event)"
              oninput="autoResize(this); showTyping()"
              required></textarea>
            <button type="submit" class="btn-send" id="sendBtn">
              <span class="send-icon">➤</span>
              <span class="send-text">Send</span>
            </button>
          </div>

          <div id="attachmentPreview" class="attachment-preview" style="display: none;">
            <span class="preview-text"></span>
            <button type="button" class="btn-remove" onclick="removeAttachment()">×</button>
          </div>
        </form>
        @else
        <div class="empty-chat-state">
          <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          <h3>Select a conversation</h3>
          <p>Choose a conversation from the list or start a new one</p>
          <button class="btn-primary" onclick="openNewMessageModal()">
            Start New Conversation
          </button>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Drop Overlay -->
  <div id="dropOverlay" class="drop-overlay" style="display: none;">
    <div class="drop-content">
      <div class="drop-icon">📎</div>
      <h3>Drop file to attach</h3>
      <p>PDF, JPG, PNG, DOC (Max 5MB)</p>
    </div>
  </div>

  <!-- New Message Modal -->
  <div id="newMessageModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal('newMessageModal')">×</button>
      <h2>✉️ New Message</h2>

      <form id="newMessageForm" onsubmit="sendNewMessage(event)">
        @csrf

        <!-- Recipient Type -->
        <div class="form-group">
          <label>Send To</label>
          <select name="recipient_type" id="recipientType" onchange="toggleRecipientOptions()" required>
            <option value="">Select Recipient</option>
            <option value="admin">🏥 Admin Support</option>
            <option value="patient">👤 Patient</option>
          </select>
        </div>

        <!-- Admin Dropdown -->
        <div class="form-group" id="adminSelect" style="display: none;">
          <label>Select Admin</label>
          <select name="admin_id" id="adminIdSelect">
            <option value="">Choose an admin</option>
            @foreach($admins as $admin)
            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Patient Dropdown -->
        <div class="form-group" id="patientSelect" style="display: none;">
          <label>Select Patient</label>
          <select name="patient_id" id="patientIdSelect">
            <option value="">Choose a patient</option>
            @foreach($myPatients as $patient)
            <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Subject -->
        <div class="form-group">
          <label>Subject</label>
          <input type="text" name="subject" id="newMsgSubject" placeholder="e.g., Appointment Request" required>
        </div>

        <!-- Message -->
        <div class="form-group">
          <label>Message</label>
          <textarea name="message_content" id="newMsgContent" rows="5" placeholder="Type your message..." required></textarea>
        </div>

        <!-- Priority Checkbox -->
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" name="priority" id="newMsgPriority" value="urgent">
            <span>🚨 Mark as Urgent</span>
          </label>
        </div>

        <button type="submit" class="btn-primary" id="newMsgSendBtn">Send Message</button>
      </form>
    </div>
  </div>

  <!-- Templates Modal -->
  <div id="templatesModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal('templatesModal')">×</button>
      <h2>📝 Message Templates</h2>
      <div class="templates-list">
        @foreach($templates as $template)
        <div class="template-item" onclick="useTemplate('{{ addslashes($template->template_content) }}')">
          <h4>{{ $template->template_name }}</h4>
          <p>{{ Str::limit($template->template_content, 60) }}</p>
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
      <span onclick="insertEmoji('😂')">😂</span>
      <span onclick="insertEmoji('🙏')">🙏</span>
      <span onclick="insertEmoji('👏')">👏</span>
      <span onclick="insertEmoji('💪')">💪</span>
      <span onclick="insertEmoji('🎉')">🎉</span>
      <span onclick="insertEmoji('✅')">✅</span>
      <span onclick="insertEmoji('⚠️')">⚠️</span>
      <span onclick="insertEmoji('📅')">📅</span>
      <span onclick="insertEmoji('💊')">💊</span>
    </div>
  </div>

  <script>
    // Scroll chat to bottom on load
    const chatWindow = document.getElementById('chatWindow');
    if (chatWindow) {
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    // Auto-resize textarea
    function autoResize(textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    // Handle Enter key press
    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('sendBtn').click();
      }
    }

    // Send message in conversation
    function sendMessage(event) {
      event.preventDefault();

      const form = event.target;
      const formData = new FormData(form);
      const sendBtn = document.getElementById('sendBtn');

      sendBtn.disabled = true;
      sendBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';

      fetch('{{ route("doctor.messages.send") }}', {
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
          sendBtn.innerHTML = '<span class="send-icon">➤</span><span class="send-text">Send</span>';
        });
    }

    // Add message to chat UI
    function addMessageToChat(message) {
      const messageHtml = `
        <div class="message-wrapper sent" data-message-id="${message.message_id}">
          <div class="message-bubble ${message.priority === 'urgent' ? 'urgent' : ''}">
            ${message.priority === 'urgent' ? '<span class="urgent-badge">🚨 Urgent</span>' : ''}
            <p>${message.message_content}</p>
            ${message.attachment_path ? `<a href="/storage/${message.attachment_path}" class="message-attachment" target="_blank">📎 ${message.attachment_path.split('/').pop()}</a>` : ''}
            <div class="message-footer">
              <span class="message-time">Just now</span>
              <span class="message-status">
                <span class="status-sent">✓ Sent</span>
              </span>
            </div>
          </div>
        </div>
      `;

      chatWindow.insertAdjacentHTML('beforeend', messageHtml);
    }

    // Send new message (create conversation)
    function sendNewMessage(event) {
      event.preventDefault();

      const form = event.target;
      const formData = new FormData(form);
      const sendBtn = document.getElementById('newMsgSendBtn');

      // Validate recipient selection
      const recipientType = formData.get('recipient_type');
      if (recipientType === 'admin' && !formData.get('admin_id')) {
        alert('Please select an admin');
        return;
      }
      if (recipientType === 'patient' && !formData.get('patient_id')) {
        alert('Please select a patient');
        return;
      }

      sendBtn.disabled = true;
      sendBtn.textContent = 'Sending...';

      fetch('{{ route("doctor.messages.create") }}', {
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
          if (data.success || data.conversation_id) {
            // ✅ FIXED: Preserve the current tab when redirecting
            const currentType = '{{ $type }}';
            window.location.href = `{{ route('doctor.messages') }}?conversation_id=${data.conversation_id}&type=${currentType}`;
          } else {
            alert(data.message || 'Failed to send message. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert(error.message || 'Failed to send message. Please try again.');
        })
        .finally(() => {
          sendBtn.disabled = false;
          sendBtn.textContent = 'Send Message';
        });
    }

    // Toggle recipient options
    function toggleRecipientOptions() {
      const recipientType = document.getElementById('recipientType').value;
      const adminSelect = document.getElementById('adminSelect');
      const patientSelect = document.getElementById('patientSelect');
      const adminIdSelect = document.getElementById('adminIdSelect');
      const patientIdSelect = document.getElementById('patientIdSelect');

      if (recipientType === 'admin') {
        adminSelect.style.display = 'block';
        patientSelect.style.display = 'none';
        adminIdSelect.required = true;
        patientIdSelect.required = false;
      } else if (recipientType === 'patient') {
        adminSelect.style.display = 'none';
        patientSelect.style.display = 'block';
        adminIdSelect.required = false;
        patientIdSelect.required = true;
      } else {
        adminSelect.style.display = 'none';
        patientSelect.style.display = 'none';
        adminIdSelect.required = false;
        patientIdSelect.required = false;
      }
    }

    // ✅ FIXED: Select conversation with tab preservation
    function selectConversation(id) {
      const currentType = '{{ $type }}';
      window.location.href = `{{ route('doctor.messages') }}?conversation_id=${id}&type=${currentType}`;
    }

    // Toggle star
    function toggleStar(id) {
      fetch(`/doctor/messages/${id}/toggle-star`, {
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

    // Insert template
    function insertTemplate(text) {
      const input = document.getElementById('messageInput');
      input.value = text;
      input.focus();
      autoResize(input);
    }

    // Use template
    function useTemplate(text) {
      insertTemplate(text);
      closeModal('templatesModal');
    }

    // Open templates modal
    function openTemplatesModal() {
      openModal('templatesModal');
    }

    // Open emoji picker
    function openEmojiPicker() {
      const picker = document.getElementById('emojiPicker');
      picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
    }

    // Insert emoji
    function insertEmoji(emoji) {
      const input = document.getElementById('messageInput');
      input.value += emoji;
      input.focus();
      document.getElementById('emojiPicker').style.display = 'none';
    }

    // Show attachment preview
    function showAttachmentPreview(input) {
      const preview = document.getElementById('attachmentPreview');
      const text = preview.querySelector('.preview-text');

      if (input.files && input.files[0]) {
        text.textContent = '📎 ' + input.files[0].name;
        preview.style.display = 'flex';
      }
    }

    // Remove attachment
    function removeAttachment() {
      document.getElementById('attachment').value = '';
      document.getElementById('attachmentPreview').style.display = 'none';
    }

    // Drag and drop handlers
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

    // Typing indicator
    let typingTimer;
    function showTyping() {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => {}, 2000);
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

    // Close drop overlay on click
    document.getElementById('dropOverlay')?.addEventListener('click', function() {
      this.style.display = 'none';
    });
  </script>

</body>

</html>