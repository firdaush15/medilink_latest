{{-- resources/views/doctor/doctor_messages.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MediLink | Messages</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @vite(['resources/css/sidebar.css', 'resources/css/doctor/doctor_messages.css'])
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
          <button class="btn-filter" onclick="toggleFilters()"><span>⚙️</span></button>
        </div>

        <!-- Tabs -->
        <div class="message-tabs">
          <button class="tab-btn {{ $type == 'all' ? 'active' : '' }}" data-type="all">
            All
            @if($unreadCount > 0)<span class="tab-badge">{{ $unreadCount }}</span>@endif
          </button>
          <button class="tab-btn {{ $type == 'admin' ? 'active' : '' }}" data-type="admin">Admin</button>
          <button class="tab-btn {{ $type == 'nurse' ? 'active' : '' }}" data-type="nurse">Nurses</button>
          <button class="tab-btn {{ $type == 'receptionist' ? 'active' : '' }}" data-type="receptionist">Reception</button>
          <button class="tab-btn {{ $type == 'pharmacist' ? 'active' : '' }}" data-type="pharmacist">Pharmacy</button>
          <button class="tab-btn {{ $type == 'patient' ? 'active' : '' }}" data-type="patient">Patients</button>
          <button class="tab-btn {{ $type == 'starred' ? 'active' : '' }}" data-type="starred">⭐</button>
        </div>

        <div class="conversations-list" id="conversationsList">
          @forelse($conversations as $conversation)
          @php
            switch ($conversation->conversation_type) {
                case 'doctor_admin':
                    $recipientUser = $conversation->admin;
                    $displayName   = 'Admin Support';
                    $avatarClass   = 'admin-avatar';
                    $avatarText    = 'A';
                    break;
                case 'doctor_nurse':
                case 'nurse_doctor':
                    $recipientUser = $conversation->nurse?->user;
                    $displayName   = $recipientUser?->name ?? 'Nurse';
                    $avatarClass   = 'nurse-avatar';
                    $avatarText    = substr($displayName, 0, 1);
                    break;
                case 'doctor_patient':
                    $recipientUser = $conversation->patient?->user;
                    $displayName   = $recipientUser?->name ?? 'Patient';
                    $avatarClass   = '';
                    $avatarText    = substr($displayName, 0, 1);
                    break;
                case 'doctor_receptionist':
                    $recipientUser = $conversation->receptionist?->user;
                    $displayName   = $recipientUser?->name ?? 'Receptionist';
                    $avatarClass   = 'receptionist-avatar';
                    $avatarText    = substr($displayName, 0, 1);
                    break;
                case 'doctor_pharmacist':
                case 'pharmacist_doctor':
                    $recipientUser = $conversation->pharmacist?->user;
                    $displayName   = $recipientUser?->name ?? 'Pharmacist';
                    $avatarClass   = 'pharmacist-avatar';
                    $avatarText    = substr($displayName, 0, 1);
                    break;
                case 'receptionist_doctor':
                    $recipientUser = $conversation->receptionist?->user;
                    $displayName   = $recipientUser?->name ?? 'Receptionist';
                    $avatarClass   = 'receptionist-avatar';
                    $avatarText    = substr($displayName, 0, 1);
                    break;
                default:
                    $recipientUser = null;
                    $displayName   = 'Unknown';
                    $avatarClass   = '';
                    $avatarText    = '?';
            }
          @endphp
          <div class="conversation-item {{ $selectedConversation && $selectedConversation->conversation_id == $conversation->conversation_id ? 'active' : '' }}"
            data-conversation-id="{{ $conversation->conversation_id }}"
            onclick="selectConversation({{ $conversation->conversation_id }})">
            <div class="conversation-avatar {{ $avatarClass }}">
              {{ $avatarText }}
              @if($recipientUser)
              <span class="online-status {{ $recipientUser->isOnline() ? 'online' : 'offline' }}"></span>
              @endif
            </div>
            <div class="conversation-details">
              <div class="conversation-header">
                <h4>{{ $displayName }}</h4>
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
              onclick="event.stopPropagation(); toggleStar({{ $conversation->conversation_id }})">⭐</button>
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
          switch ($selectedConversation->conversation_type) {
              case 'doctor_admin':
                  $recipientUser   = $selectedConversation->admin;
                  $chatDisplayName = 'Admin Support';
                  break;
              case 'doctor_nurse':
              case 'nurse_doctor':
                  $recipientUser   = $selectedConversation->nurse?->user;
                  $chatDisplayName = $recipientUser?->name ?? 'Nurse';
                  break;
              case 'doctor_patient':
                  $recipientUser   = $selectedConversation->patient?->user;
                  $chatDisplayName = $recipientUser?->name ?? 'Patient';
                  break;
              case 'doctor_receptionist':
                  $recipientUser   = $selectedConversation->receptionist?->user;
                  $chatDisplayName = $recipientUser?->name ?? 'Receptionist';
                  break;
              case 'doctor_pharmacist':
              case 'pharmacist_doctor':
                  $recipientUser   = $selectedConversation->pharmacist?->user;
                  $chatDisplayName = $recipientUser?->name ?? 'Pharmacist';
                  break;
              case 'receptionist_doctor':
                  $recipientUser   = $selectedConversation->receptionist?->user;
                  $chatDisplayName = $recipientUser?->name ?? 'Receptionist';
                  break;
              default:
                  $recipientUser   = null;
                  $chatDisplayName = 'Unknown';
          }
        @endphp
        <div class="chat-header">
          <div class="chat-header-info">
            <div class="recipient-info">
              <h3>{{ $chatDisplayName }}</h3>
              @if($recipientUser)
                @if($recipientUser->isOnline())
                <span id="recipientStatusBadge" class="status-badge online"><span id="recipientStatusDot" class="status-dot"></span> <span id="recipientStatusText">Online</span></span>
                @else
                <span id="recipientStatusBadge" class="status-badge offline"><span id="recipientStatusDot" class="status-dot"></span> <span id="recipientStatusText">Offline</span></span>
                  @if($recipientUser->last_seen_at)
                  <small class="last-seen-text">Last seen {{ $recipientUser->last_seen_at->diffForHumans() }}</small>
                  @endif
                @endif
              @endif
            </div>
            @if($selectedConversation->subject)
            <small class="subject-line">{{ $selectedConversation->subject }}</small>
            @endif
          </div>
          <div class="chat-header-actions">
            <button class="btn-icon" onclick="toggleStar({{ $selectedConversation->conversation_id }})" title="Star">
              <span class="{{ $selectedConversation->is_starred ?? false ? 'starred' : '' }}">⭐</span>
            </button>
            <button class="btn-icon" onclick="alert('More options coming soon')" title="More">⋮</button>
          </div>
        </div>

        <!-- Quick Templates Bar -->
        <div class="quick-templates">
          <button class="template-btn" onclick="openTemplatesModal()">📝 Templates</button>
          <button class="template-btn" onclick="insertTemplate('Your appointment is confirmed.')">✅ Confirm Appointment</button>
          <button class="template-btn" onclick="insertTemplate('Your lab results are ready. Please call to discuss.')">🔬 Lab Results Ready</button>
          <button class="template-btn" onclick="insertTemplate('Please schedule a follow-up appointment.')">📅 Follow-up</button>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatWindow"
          ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
          @foreach($messages as $message)
          <div class="message-wrapper {{ $message->sender_id == auth()->id() ? 'sent' : 'received' }}"
            data-message-id="{{ $message->message_id }}">
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

          <div class="typing-indicator" id="typingIndicator" style="display:none;">
            <div class="typing-dots">
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
              <span class="typing-dot"></span>
            </div>
            <span class="typing-text">Typing...</span>
          </div>
        </div>

        <!-- Input Form -->
        <form class="chat-input-form" id="messageForm" onsubmit="sendMessage(event)">
          @csrf
          <input type="hidden" name="conversation_id" value="{{ $selectedConversation->conversation_id }}">
          <div class="input-actions">
            <input type="file" name="attachment" id="attachment" style="display:none;" onchange="showAttachmentPreview(this)">
            <button type="button" class="btn-icon" onclick="document.getElementById('attachment').click()" title="Attach">📎</button>
            <button type="button" class="btn-icon" onclick="openEmojiPicker()" title="Emoji">😊</button>
            <button type="button" class="btn-icon" onclick="openTemplatesModal()" title="Template">📝</button>
            <label class="priority-toggle">
              <input type="checkbox" name="priority" id="urgentCheckbox" value="urgent">
              <span class="urgent-label" title="Mark urgent">🚨</span>
            </label>
          </div>
          <div class="input-wrapper">
            <textarea name="message_content" id="messageInput" class="message-input"
              placeholder="Type a message... (Shift+Enter for new line)"
              rows="1" onkeydown="handleKeyPress(event)" oninput="autoResize(this); showTyping()" required></textarea>
            <button type="submit" class="btn-send" id="sendBtn">
              <span class="send-icon">➤</span>
              <span class="send-text">Send</span>
            </button>
          </div>
          <div id="attachmentPreview" class="attachment-preview" style="display:none;">
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
          <button class="btn-primary" onclick="openNewMessageModal()">Start New Conversation</button>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Drop Overlay -->
  <div id="dropOverlay" class="drop-overlay" style="display:none;">
    <div class="drop-content">
      <div class="drop-icon">📎</div>
      <h3>Drop file to attach</h3>
      <p>PDF, JPG, PNG, DOC (Max 5MB)</p>
    </div>
  </div>

  <!-- ── New Message Modal ─────────────────────────────────── -->
  <div id="newMessageModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal('newMessageModal')">×</button>
      <h2>✉️ New Message</h2>

      <form id="newMessageForm" onsubmit="sendNewMessage(event)">
        @csrf

        {{-- Recipient type --}}
        <div class="form-group">
          <label>Send To</label>
          <select name="recipient_type" id="recipientType" onchange="toggleRecipientOptions()" required>
            <option value="">Select Recipient</option>
            <option value="admin">🏥 Admin Support</option>
            <option value="nurse">👩‍⚕️ Nurse (My Team)</option>
            <option value="receptionist">📋 Receptionist</option>
            <option value="pharmacist">💊 Pharmacist</option>
            <option value="patient">👤 Patient</option>
          </select>
        </div>

        {{-- Admin info card --}}
        <div class="form-group admin-info-card" id="adminInfoCard" style="display:none;">
          <div class="info-card">
            <span class="info-icon">ℹ️</span>
            <p>Your message will be received by the <strong>Admin Support team</strong>.
               Any available admin will respond on behalf of the team.</p>
          </div>
        </div>

        {{-- Nurse dropdown --}}
        <div class="form-group" id="nurseSelectGroup" style="display:none;">
          <label>Select Nurse</label>
          <select name="nurse_id" id="nurseIdSelect">
            <option value="">Choose a nurse</option>
            @foreach($myNurses as $nurse)
              <option value="{{ $nurse->nurse_id }}">
                {{ $nurse->user->name }}
                @if($nurse->department) — {{ $nurse->department }} @endif
              </option>
            @endforeach
          </select>
          @if($myNurses->isEmpty())
          <small style="color:var(--color-text-tertiary);display:block;margin-top:4px;">No nurses assigned to your team yet.</small>
          @endif
        </div>

        {{-- Receptionist dropdown --}}
        <div class="form-group" id="receptionistSelectGroup" style="display:none;">
          <label>Select Receptionist</label>
          <select name="receptionist_id" id="receptionistIdSelect">
            <option value="">Choose a receptionist</option>
            @foreach($receptionists as $receptionist)
              <option value="{{ $receptionist->receptionist_id }}">{{ $receptionist->user->name }}</option>
            @endforeach
          </select>
          @if($receptionists->isEmpty())
          <small style="color:var(--color-text-tertiary);display:block;margin-top:4px;">No receptionists available right now.</small>
          @endif
        </div>

        {{-- Pharmacist dropdown --}}
        <div class="form-group" id="pharmacistSelectGroup" style="display:none;">
          <label>Select Pharmacist</label>
          <select name="pharmacist_id" id="pharmacistIdSelect">
            <option value="">Choose a pharmacist</option>
            @foreach($pharmacists as $pharmacist)
              <option value="{{ $pharmacist->pharmacist_id }}">{{ $pharmacist->user->name }}</option>
            @endforeach
          </select>
          @if($pharmacists->isEmpty())
          <small style="color:var(--color-text-tertiary);display:block;margin-top:4px;">No pharmacists available right now.</small>
          @endif
        </div>

        {{-- Patient dropdown --}}
        <div class="form-group" id="patientSelectGroup" style="display:none;">
          <label>Select Patient</label>
          <select name="patient_id" id="patientIdSelect">
            <option value="">Choose a patient</option>
            @foreach($myPatients as $patient)
              <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Subject --}}
        <div class="form-group">
          <label>Subject</label>
          <input type="text" name="subject" id="newMsgSubject"
                 placeholder="e.g., Appointment Request, Medication Query" required>
        </div>

        {{-- Message --}}
        <div class="form-group">
          <label>Message</label>
          <textarea name="message_content" id="newMsgContent"
                    rows="5" placeholder="Type your message..." required></textarea>
        </div>

        {{-- Priority --}}
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
  <div id="emojiPicker" class="emoji-picker" style="display:none;">
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

  <style>
    .conversation-avatar.admin-avatar        { background:#f0fff4; color:#085041; }
    .conversation-avatar.nurse-avatar        { background:#e1f5ee; color:#085041; }
    .conversation-avatar.receptionist-avatar { background:#faeeda; color:#633806; }
    .conversation-avatar.pharmacist-avatar   { background:#fbeaf0; color:#72243e; }
    .admin-info-card .info-card {
      display:flex; align-items:flex-start; gap:.5rem;
      background:#f0f7ff; border:1px solid #bcd6f5;
      border-radius:8px; padding:.75rem 1rem;
      font-size:.875rem; color:#2c5282; line-height:1.45;
    }
    .admin-info-card .info-icon { flex-shrink:0; font-size:1rem; margin-top:1px; }
    .admin-info-card p { margin:0; }
  </style>

  <script>
    const chatWindow = document.getElementById('chatWindow');
    if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;

    function autoResize(textarea) {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    function handleKeyPress(event) {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('sendBtn').click();
      }
    }

    // ── Send reply in existing conversation ──────────────────
    function sendMessage(event) {
      event.preventDefault();
      const form    = event.target;
      const fd      = new FormData(form);
      const sendBtn = document.getElementById('sendBtn');
      sendBtn.disabled = true;
      sendBtn.innerHTML = '<span class="loading-spinner"></span> Sending...';

      fetch('{{ route("doctor.messages.send") }}', {
          method: 'POST', body: fd,
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) return r.json().then(e => { throw e; }); return r.json(); })
        .then(data => {
          if (data.success) {
            addMessageToChat(data.message);
            form.reset();
            document.getElementById('messageInput').style.height = 'auto';
            document.getElementById('attachmentPreview').style.display = 'none';
            chatWindow.scrollTop = chatWindow.scrollHeight;
          } else { alert(data.message || 'Failed to send message.'); }
        })
        .catch(e => { console.error(e); alert(e.message || 'Failed to send message.'); })
        .finally(() => {
          sendBtn.disabled = false;
          sendBtn.innerHTML = '<span class="send-icon">➤</span><span class="send-text">Send</span>';
        });
    }

    function addMessageToChat(message) {
      chatWindow.insertAdjacentHTML('beforeend', `
        <div class="message-wrapper sent" data-message-id="${message.message_id}">
          <div class="message-bubble ${message.priority === 'urgent' ? 'urgent' : ''}">
            ${message.priority === 'urgent' ? '<span class="urgent-badge">🚨 Urgent</span>' : ''}
            <p>${message.message_content}</p>
            ${message.attachment_path ? `<a href="/storage/${message.attachment_path}" class="message-attachment" target="_blank">📎 ${message.attachment_path.split('/').pop()}</a>` : ''}
            <div class="message-footer">
              <span class="message-time">Just now</span>
              <span class="message-status"><span class="status-sent">✓ Sent</span></span>
            </div>
          </div>
        </div>`);
    }

    // ── New message modal recipient toggle ───────────────────
    function toggleRecipientOptions() {
      const type = document.getElementById('recipientType').value;

      const groups = {
        admin:        'adminInfoCard',
        nurse:        'nurseSelectGroup',
        receptionist: 'receptionistSelectGroup',
        pharmacist:   'pharmacistSelectGroup',
        patient:      'patientSelectGroup',
      };

      Object.values(groups).forEach(id => document.getElementById(id).style.display = 'none');
      if (groups[type]) document.getElementById(groups[type]).style.display = 'block';

      document.getElementById('nurseIdSelect').required        = type === 'nurse';
      document.getElementById('receptionistIdSelect').required = type === 'receptionist';
      document.getElementById('pharmacistIdSelect').required   = type === 'pharmacist';
      document.getElementById('patientIdSelect').required      = type === 'patient';
    }

    // ── Send new conversation ────────────────────────────────
    function sendNewMessage(event) {
      event.preventDefault();
      const form    = event.target;
      const fd      = new FormData(form);
      const sendBtn = document.getElementById('newMsgSendBtn');
      const type    = fd.get('recipient_type');

      if (type === 'nurse'        && !fd.get('nurse_id'))        { alert('Please select a nurse'); return; }
      if (type === 'receptionist' && !fd.get('receptionist_id')) { alert('Please select a receptionist'); return; }
      if (type === 'pharmacist'   && !fd.get('pharmacist_id'))   { alert('Please select a pharmacist'); return; }
      if (type === 'patient'      && !fd.get('patient_id'))      { alert('Please select a patient'); return; }

      sendBtn.disabled    = true;
      sendBtn.textContent = 'Sending...';

      fetch('{{ route("doctor.messages.create") }}', {
          method: 'POST', body: fd,
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        })
        .then(r => { if (!r.ok) return r.json().then(e => { throw e; }); return r.json(); })
        .then(data => {
          if (data.success || data.conversation_id) {
            const currentType = '{{ $type }}';
            window.location.href = `{{ route('doctor.messages') }}?conversation_id=${data.conversation_id}&type=${currentType}`;
          } else { alert(data.message || 'Failed to send message.'); }
        })
        .catch(e => { console.error(e); alert(e.message || 'Failed to send message.'); })
        .finally(() => { sendBtn.disabled = false; sendBtn.textContent = 'Send Message'; });
    }

    function selectConversation(id) {
      const currentType = '{{ $type }}';
      window.location.href = `{{ route('doctor.messages') }}?conversation_id=${id}&type=${currentType}`;
    }

    function toggleStar(id) {
      fetch(`/doctor/messages/${id}/toggle-star`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
    }

    function searchMessages(query) {
      const url = new URL(window.location.href);
      query ? url.searchParams.set('search', query) : url.searchParams.delete('search');
      window.location.href = url;
    }

    function insertTemplate(text) {
      const input = document.getElementById('messageInput');
      input.value = text; input.focus(); autoResize(input);
    }

    function useTemplate(text) { insertTemplate(text); closeModal('templatesModal'); }
    function openTemplatesModal() { openModal('templatesModal'); }

    function openEmojiPicker() {
      const p = document.getElementById('emojiPicker');
      p.style.display = p.style.display === 'none' ? 'block' : 'none';
    }

    function insertEmoji(emoji) {
      const input = document.getElementById('messageInput');
      input.value += emoji; input.focus();
      document.getElementById('emojiPicker').style.display = 'none';
    }

    function showAttachmentPreview(input) {
      if (input.files && input.files[0]) {
        const preview = document.getElementById('attachmentPreview');
        preview.querySelector('.preview-text').textContent = '📎 ' + input.files[0].name;
        preview.style.display = 'flex';
      }
    }

    function removeAttachment() {
      document.getElementById('attachment').value = '';
      document.getElementById('attachmentPreview').style.display = 'none';
    }

    function handleDragOver(e) { e.preventDefault(); document.getElementById('dropOverlay').style.display = 'flex'; }
    function handleDragLeave(e) { if (e.target.id === 'dropOverlay') document.getElementById('dropOverlay').style.display = 'none'; }
    function handleDrop(e) {
      e.preventDefault();
      document.getElementById('dropOverlay').style.display = 'none';
      const files = e.dataTransfer.files;
      if (files.length > 0) { document.getElementById('attachment').files = files; showAttachmentPreview(document.getElementById('attachment')); }
    }

    let typingTimer;
    function showTyping() { clearTimeout(typingTimer); typingTimer = setTimeout(() => {}, 2000); }

    function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function openNewMessageModal() { openModal('newMessageModal'); }
    function toggleFilters() { alert('Filter options coming soon'); }

    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const type = this.dataset.type;
        const url = new URL(window.location.href);
        url.searchParams.set('type', type);
        url.searchParams.delete('conversation_id');
        window.location.href = url;
      });
    });

    document.addEventListener('click', function(event) {
      const picker = document.getElementById('emojiPicker');
      if (!picker.contains(event.target) && !event.target.closest('[onclick*="openEmojiPicker"]')) {
        picker.style.display = 'none';
      }
    });

    document.getElementById('dropOverlay')?.addEventListener('click', function() { this.style.display = 'none'; });
  </script>

  {{-- Inbox poll: detects new inbound conversations and reloads the page --}}
<script>
(function () {
    const INBOX_POLL_URL      = '{{ route("doctor.messages.inbox-poll") }}';
    const INITIAL_COUNT       = {{ $conversations->count() }};
    const INITIAL_UNREAD      = {{ $unreadCount }};
    const INITIAL_ACTIVITY    = '{{ $conversations->max("last_message_at")?->toIso8601String() ?? "" }}';
    const INBOX_POLL_INTERVAL = 5000; // 5 seconds

    async function checkInbox() {
        try {
            const res  = await fetch(INBOX_POLL_URL, {
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            // Reload if a new conversation appeared, unread count changed,
            // or the latest activity timestamp is newer than what we had
            const countChanged    = data.conversation_count !== INITIAL_COUNT;
            const unreadChanged   = data.unread_count       !== INITIAL_UNREAD;
            const activityChanged = INITIAL_ACTIVITY && data.latest_activity &&
                                    data.latest_activity > INITIAL_ACTIVITY;

            if (countChanged || unreadChanged || activityChanged) {
                // Preserve current conversation_id and type so the reload
                // opens back on the same conversation if one was open
                const url = new URL(window.location.href);
                window.location.href = url.toString();
            }
        } catch (_) { /* silent fail */ }
    }

    // Only run when the tab is visible
    let inboxTimer = null;

    function startInboxPoll() {
        if (inboxTimer) return;
        inboxTimer = setInterval(checkInbox, INBOX_POLL_INTERVAL);
    }

    function stopInboxPoll() {
        clearInterval(inboxTimer);
        inboxTimer = null;
    }

    document.addEventListener('visibilitychange', function () {
        document.hidden ? stopInboxPoll() : startInboxPoll();
    });

    startInboxPoll();
})();
</script>

@if($selectedConversation)
  <script>
    window.POLL_URL        = '{{ route("doctor.messages.poll", $selectedConversation->conversation_id) }}';
    window.CURRENT_USER_ID = {{ auth()->id() }};
    window.LAST_MESSAGE_AT = '{{ $messages->last()?->created_at->toIso8601String() ?? '' }}';
  </script>
  @vite(['resources/js/message-polling.js'])
@endif
@vite(['resources/js/sidebar.js'])
</body>
</html>