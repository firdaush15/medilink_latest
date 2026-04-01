/**
 * message-polling.js
 * Place at: resources/js/message-polling.js
 *
 * Polls for new messages every 3 seconds when a conversation is open.
 *
 * Requires these variables set BEFORE this script runs (in blade):
 *   window.POLL_URL        - e.g. '/doctor/messages/42/poll'
 *   window.CURRENT_USER_ID - auth()->id() value
 *   window.LAST_MESSAGE_AT - ISO timestamp of last existing message on page load
 *                            Set from PHP: $messages->last()?->created_at->toIso8601String()
 *                            If null/undefined, falls back to 30 seconds ago.
 */

(function () {
    'use strict';

    const POLL_INTERVAL_MS = 3000;
    let pollTimer          = null;
    let lastMessageTime    = null;
    let isPolling          = false;

    // ── Helpers ───────────────────────────────────────────────

    function getChatWindow() {
        return document.getElementById('chatWindow');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    function buildMessageHtml(msg) {
        const isMine      = parseInt(msg.sender_id) === parseInt(window.CURRENT_USER_ID);
        const wrapClass   = isMine ? 'sent' : 'received';
        const urgentBadge = msg.priority === 'urgent'
            ? '<span class="urgent-badge">🚨 Urgent</span>' : '';
        const attachment  = msg.attachment_path
            ? `<a href="/storage/${msg.attachment_path}" class="message-attachment" target="_blank">📎 ${msg.attachment_path.split('/').pop()}</a>`
            : '';
        const statusHtml  = isMine
            ? '<span class="message-status"><span class="status-sent">✓ Sent</span></span>' : '';

        return `
            <div class="message-wrapper ${wrapClass}" data-message-id="${msg.message_id}">
                <div class="message-bubble ${msg.priority === 'urgent' ? 'urgent' : ''}">
                    ${urgentBadge}
                    <p>${escapeHtml(msg.message_content)}</p>
                    ${attachment}
                    <div class="message-footer">
                        <span class="message-time">${msg.time_formatted}</span>
                        ${statusHtml}
                    </div>
                </div>
            </div>`;
    }

    function updateOnlineStatus(isOnline) {
        const badge = document.getElementById('recipientStatusBadge');
        const text  = document.getElementById('recipientStatusText');
        if (!badge) return;
        badge.className = isOnline ? 'status-badge online' : 'status-badge offline';
        if (text) text.textContent = isOnline ? 'Online' : 'Offline';
    }

    // ── Core poll ─────────────────────────────────────────────

    async function doPoll() {
        if (isPolling || !window.POLL_URL) return;
        isPolling = true;

        try {
            const url = `${window.POLL_URL}?since=${encodeURIComponent(lastMessageTime)}`;

            const response = await fetch(url, {
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (!response.ok) return;
            const data = await response.json();
            if (!data.success) return;

            // Advance cursor — next poll only fetches truly new messages
            lastMessageTime = data.server_time;

            if (data.messages && data.messages.length > 0) {
                const chatWindow = getChatWindow();
                if (chatWindow) {
                    const wasAtBottom =
                        chatWindow.scrollHeight - chatWindow.scrollTop <= chatWindow.clientHeight + 80;

                    data.messages.forEach(msg => {
                        // Skip if already in DOM (avoids duplicating messages from addMessageToChat)
                        if (document.querySelector(`[data-message-id="${msg.message_id}"]`)) return;
                        chatWindow.insertAdjacentHTML('beforeend', buildMessageHtml(msg));
                    });

                    if (wasAtBottom) chatWindow.scrollTop = chatWindow.scrollHeight;
                }
            }

            updateOnlineStatus(data.other_party_online);

        } catch (_) {
            // silent fail — retry next tick
        } finally {
            isPolling = false;
        }
    }

    // ── Start / stop ──────────────────────────────────────────

    function startPolling() {
        if (!window.POLL_URL) return;
        stopPolling();

        // KEY FIX: use LAST_MESSAGE_AT (the timestamp of the last rendered message)
        // so that after a sendNewMessage redirect the first poll picks up that message.
        // We subtract 2 s as a small safety buffer for clock skew.
        if (window.LAST_MESSAGE_AT) {
            const d = new Date(window.LAST_MESSAGE_AT);
            d.setSeconds(d.getSeconds() - 2);
            lastMessageTime = d.toISOString();
        } else {
            // No prior messages — look back 30 s so we catch anything just sent
            const d = new Date();
            d.setSeconds(d.getSeconds() - 30);
            lastMessageTime = d.toISOString();
        }

        // Fire immediately so there is no 3-second wait on page load
        doPoll();
        pollTimer = setInterval(doPoll, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    // Pause when tab hidden, resume when visible
    document.addEventListener('visibilitychange', function () {
        document.hidden ? stopPolling() : (window.POLL_URL && startPolling());
    });

    // Boot
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }

    window.startMessagePolling = startPolling;
    window.stopMessagePolling  = stopPolling;

})();