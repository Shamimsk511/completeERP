@extends('layouts.modern-admin')

@section('title', 'Team Chat')
@section('page_title', 'Team Chat')

@section('header_actions')
    @can('chat-clear')
        <button type="button" class="btn modern-btn modern-btn-danger" id="chat-clear-all">
            <i class="fas fa-trash"></i> Clear All History
        </button>
    @endcan
@stop

@section('page_content')
    <div class="row">
        <div class="col-md-4">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title"><i class="fas fa-users"></i> Users</h3>
                </div>
                <div class="card-body">
                    <input type="text" id="chat-user-search" class="form-control modern-input mb-3" placeholder="Search users...">
                    <div id="chat-users" class="list-group chat-user-list">
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center chat-user chat-user-all" data-user-id="all" data-user-name="All Users">
                            <div class="d-flex align-items-center">
                                <span class="chat-online-dot online"></span>
                                <span class="ml-2 font-weight-bold">All Users</span>
                            </div>
                            <span class="badge badge-pill badge-danger chat-unread" data-user-unread="all" style="display: none;">0</span>
                        </button>
                        @forelse($users as $user)
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center chat-user" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                                <div class="d-flex align-items-center">
                                    <span class="chat-online-dot" data-user-dot="{{ $user->id }}"></span>
                                    <span class="ml-2">{{ $user->name }}</span>
                                </div>
                                <span class="badge badge-pill badge-danger chat-unread" data-user-unread="{{ $user->id }}" style="display: none;">0</span>
                            </button>
                        @empty
                            <div class="text-muted text-center">No users found for this tenant.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <div>
                        <h3 class="card-title mb-0" id="chat-title">Select a user to start chatting</h3>
                        <small class="text-muted" id="chat-subtitle"></small>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chat-messages" class="chat-messages"></div>
                </div>
                <div class="card-footer">
                    <form id="chat-form" class="d-flex">
                        @csrf
                        <input type="hidden" id="chat-recipient-id">
                        <input type="text" id="chat-message-input" class="form-control modern-input" placeholder="Type a message..." disabled>
                        <button type="submit" class="btn modern-btn modern-btn-primary ml-2" disabled>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    .chat-user-list {
        max-height: 520px;
        overflow-y: auto;
    }
    .chat-online-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #9ca3af;
        display: inline-block;
    }
    .chat-online-dot.online {
        background: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
    }
    .chat-messages {
        min-height: 420px;
        max-height: 420px;
        overflow-y: auto;
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
    }
    .chat-message {
        display: flex;
        flex-direction: column;
        margin-bottom: 12px;
        max-width: 70%;
    }
    .chat-message.outgoing {
        margin-left: auto;
        align-items: flex-end;
    }
    .chat-message.incoming {
        margin-right: auto;
        align-items: flex-start;
    }
    .chat-bubble {
        padding: 10px 14px;
        border-radius: 16px;
        background: #e2e8f0;
        color: #0f172a;
    }
    .chat-message.outgoing .chat-bubble {
        background: #c7f0d8;
    }
    .chat-meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 4px;
    }
</style>
@stop

@section('additional_js')
<script>
    $(document).ready(function() {
        let currentRecipientId = null;
        let lastMessageId = 0;
        const POLL_INTERVAL_MS = 10000;
        let pollingInterval = null;
        let presenceInterval = null;
        let pingInterval = null;

        const chatMessages = $('#chat-messages');
        const chatInput = $('#chat-message-input');
        const chatForm = $('#chat-form');
        const chatTitle = $('#chat-title');
        const chatSubtitle = $('#chat-subtitle');

        const scrollToBottom = function() {
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        };

        const renderMessage = function(message) {
            const wrapper = $('<div>').addClass('chat-message').addClass(message.is_mine ? 'outgoing' : 'incoming');
            const bubble = $('<div>').addClass('chat-bubble').text(message.message);
            const label = message.is_broadcast ? 'Broadcast' : (message.sender_name || 'User');
            const meta = $('<div>').addClass('chat-meta').text(`${label} · ${message.created_at}`);
            wrapper.append(bubble).append(meta);
            chatMessages.append(wrapper);
        };

        const loadMessages = function(userId, reset = true) {
            if (!userId) {
                return;
            }

            const target = userId === 'all' ? 'all' : userId;
            const url = `{{ url('chat/messages') }}/${target}` + (reset ? '' : `?after_id=${lastMessageId}`);

            $.get(url, function(response) {
                if (reset) {
                    chatMessages.empty();
                    lastMessageId = 0;
                }

                if (response.messages && response.messages.length) {
                    response.messages.forEach(function(message) {
                        renderMessage(message);
                        lastMessageId = message.id;
                    });
                    scrollToBottom();
                }
            });
        };

        const startPolling = function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }

            pollingInterval = setInterval(function() {
                if (!currentRecipientId) {
                    return;
                }
                loadMessages(currentRecipientId, false);
            }, POLL_INTERVAL_MS);
        };

        const stopPolling = function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        };

        const updatePresence = function() {
            $.get(`{{ route('chat.presence') }}`, function(response) {
                const onlineIds = response.online_user_ids || [];
                $('[data-user-dot]').each(function() {
                    const userId = parseInt($(this).data('user-dot'), 10);
                    if (onlineIds.includes(userId)) {
                        $(this).addClass('online');
                    } else {
                        $(this).removeClass('online');
                    }
                });

                const unread = response.unread_by_sender || {};
                $('[data-user-unread]').each(function() {
                    const userId = $(this).data('user-unread');
                    if (userId === 'all') {
                        return;
                    }
                    const count = unread[userId] || 0;
                    if (count > 0) {
                        $(this).text(count).show();
                    } else {
                        $(this).hide();
                    }
                });

                const broadcastUnread = response.broadcast_unread || 0;
                const broadcastBadge = $('[data-user-unread="all"]');
                if (broadcastUnread > 0) {
                    broadcastBadge.text(broadcastUnread).show();
                } else {
                    broadcastBadge.hide();
                }
            });
        };

        const ping = function() {
            if (!currentRecipientId) {
                return;
            }
            $.post(`{{ route('chat.ping') }}`, {_token: "{{ csrf_token() }}"});
        };

        $(document).on('click', '.chat-user', function() {
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');
            currentRecipientId = userId;
            lastMessageId = 0;

            $('.chat-user').removeClass('active');
            $(this).addClass('active');

            if (userId === 'all') {
                chatTitle.text('Broadcast to All Users');
            } else {
                chatTitle.text(`Chat with ${userName}`);
            }
            chatSubtitle.text('');

            chatInput.prop('disabled', false);
            chatForm.find('button').prop('disabled', false);
            $('#chat-recipient-id').val(userId);

            loadMessages(userId, true);
            if (!document.hidden) {
                startPolling();
            }
        });

        chatForm.on('submit', function(e) {
            e.preventDefault();
            const message = chatInput.val().trim();
            const recipientId = $('#chat-recipient-id').val();

            if (!message || !recipientId) {
                return;
            }

            const payload = {
                _token: "{{ csrf_token() }}",
                message: message
            };

            if (recipientId === 'all') {
                payload.broadcast_all = 1;
            } else {
                payload.recipient_id = recipientId;
            }

            $.post(`{{ route('chat.send') }}`, payload).done(function(response) {
                renderMessage(response);
                lastMessageId = response.id;
                chatInput.val('');
                scrollToBottom();
            });
        });

        $('#chat-user-search').on('input', function() {
            const term = $(this).val().toLowerCase();
            $('.chat-user').each(function() {
                const name = $(this).data('user-name').toLowerCase();
                $(this).toggle(name.includes(term));
            });
        });

        $('#chat-clear-all').on('click', function() {
            const runClear = function() {
                $.post(`{{ route('chat.clear') }}`, {_token: "{{ csrf_token() }}"})
                    .done(function() {
                        chatMessages.empty();
                        lastMessageId = 0;
                        updatePresence();
                    });
            };

            if (!window.Swal) {
                if (confirm('This will delete all chat history for this tenant. Continue?')) {
                    runClear();
                }
                return;
            }

            Swal.fire({
                title: 'Clear Chat History',
                text: 'This will delete all chat history for this tenant. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, clear all',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    runClear();
                }
            });
        });

        const startPresence = function() {
            if (presenceInterval) {
                clearInterval(presenceInterval);
            }
            presenceInterval = setInterval(updatePresence, POLL_INTERVAL_MS);
        };

        const startPing = function() {
            if (pingInterval) {
                clearInterval(pingInterval);
            }
            pingInterval = setInterval(ping, POLL_INTERVAL_MS);
        };

        const stopPresence = function() {
            if (presenceInterval) {
                clearInterval(presenceInterval);
                presenceInterval = null;
            }
        };

        const stopPing = function() {
            if (pingInterval) {
                clearInterval(pingInterval);
                pingInterval = null;
            }
        };

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopPolling();
                stopPresence();
                stopPing();
                return;
            }

            startPresence();
            startPing();
            updatePresence();
            ping();

            if (currentRecipientId) {
                startPolling();
            }
        });

        startPresence();
        startPing();
        updatePresence();
        ping();

        const preselectedUserId = @json($selectedUserId);
        const preselectBroadcast = @json($broadcastSelected);
        if (preselectBroadcast) {
            $('[data-user-id="all"]').trigger('click');
        } else if (preselectedUserId) {
            $(`[data-user-id="${preselectedUserId}"]`).trigger('click');
        }
    });
</script>
@stop
