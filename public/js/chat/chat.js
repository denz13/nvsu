"use strict";

// Wait for jQuery to be available
(function() {
    // Wait for jQuery
    function waitForJQuery() {
        if (typeof jQuery === 'undefined' || typeof window.$ === 'undefined' || (typeof window.$ !== 'undefined' && !window.$.ajax)) {
            console.log('Waiting for jQuery...');
            setTimeout(waitForJQuery, 100);
            return;
        }
        
        // jQuery is available, initialize chat
        initializeChat();
    }
    
    function initializeChat() {
// Chat functionality
jQuery(document).ready(function($) {
    let currentChatUserId = null;
    let currentChatUserType = null;
    let messagePollingInterval = null;

    console.log('Chat JS initialized');
    console.log('Current user ID:', window.currentUserId);

    // Initialize chat
    initChat();

    // Initialize conversations
    loadConversations();

    // Send message button click
    $(document).on('click', '.chat__send-button', function() {
        sendMessage();
    });

    // Enter key in textarea
    $(document).on('keypress', '.chat__box__input', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Click on conversation item
    $(document).on('click', '.chat__conversation-item', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const userType = $(this).data('user-type') || 'user'; // Ensure default type
        console.log('Conversation clicked, userId:', userId, 'userType:', userType);
        console.log('Element data attributes:', {
            'data-user-id': $(this).attr('data-user-id'),
            'data-user-type': $(this).attr('data-user-type')
        });
        if (userId) {
            openChat(userId, userType);
        } else {
            console.error('No user ID found on clicked element');
        }
    });

    // Click on user item (from contact list)
    $(document).on('click', '.chat__user-item', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const userType = $(this).data('user-type');
        console.log('User clicked from contact list, userId:', userId, 'userType:', userType);
        if (userId) {
            openChat(userId, userType);
        } else {
            console.error('No user ID found on clicked element');
        }
    });

    // Search users in contact list
    $(document).on('keyup', '#chat-search-users', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.chat__user-item').each(function() {
            const userName = $(this).find('.text-xs').text().toLowerCase();
            if (userName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Refresh conversations every 10 seconds (reduced frequency to prevent overload)
    let conversationsRefreshInterval = setInterval(function() {
        // Only refresh if not currently loading a chat
        if (!currentChatUserId) {
            loadConversations();
        }
    }, 10000);

    /**
     * Initialize chat
     */
    function initChat() {
        // Hide default message, show empty state
        const $chatActive = $('.chat__active');
        const $chatDefault = $('.chat__default');
        
        console.log('Initializing chat...');
        console.log('Chat active elements:', $chatActive.length);
        console.log('Chat default elements:', $chatDefault.length);
        
        $chatActive.addClass('hidden');
        $chatDefault.removeClass('hidden');
        
        console.log('Chat initialized - default should be visible');
    }

    /**
     * Load conversations list
     */
    function loadConversations() {
        $.ajax({
            url: '/chat/conversations',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    renderConversations(response.conversations || []);
                } else {
                    renderConversations([]);
                    console.error('Failed to load conversations:', response.error || 'Unknown error');
                }
            },
            error: function(xhr) {
                console.error('Error loading conversations:', xhr);
                renderConversations([]);
            }
        });
    }

    /**
     * Render conversations list
     */
    function renderConversations(conversations) {
        const $chatList = $('.chat__chat-list');
        
        // Remove loading message - check for any loading indicators
        $chatList.find('.loading-conversations').remove();
        // Use a more specific selector
        $chatList.children('div').each(function() {
            if ($(this).find('p:contains("Loading conversations")').length > 0) {
                $(this).remove();
            }
        });
        
        // Clear the list
        $chatList.empty();

        if (!conversations || conversations.length === 0) {
            $chatList.append('<div class="text-center text-slate-500 p-5">No conversations yet</div>');
            return;
        }

        conversations.forEach(function(conversation) {
            const timeAgo = formatTimeAgo(conversation.last_message_time);
            const unreadBadge = conversation.unread_count > 0 
                ? `<div class="w-5 h-5 flex items-center justify-center absolute top-0 right-0 text-xs text-white rounded-full bg-primary font-medium -mt-1 -mr-1">${conversation.unread_count}</div>`
                : '';

            const item = `
                <div class="chat__conversation-item intro-x cursor-pointer box relative flex items-center p-5 mt-2" data-user-id="${conversation.user_id}" data-user-type="${conversation.user_type || 'user'}">
                    <div class="w-12 h-12 flex-none image-fit mr-1">
                        <img alt="${conversation.name}" class="rounded-full" src="${conversation.photo}">
                        <div class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white dark:border-darkmode-600"></div>
                    </div>
                    <div class="ml-2 overflow-hidden flex-1">
                        <div class="flex items-center">
                            <a href="javascript:;" class="font-medium">${conversation.name}</a>
                            <div class="text-xs text-slate-400 ml-auto">${timeAgo}</div>
                        </div>
                        <div class="w-full truncate text-slate-500 mt-0.5">${conversation.last_message || 'No messages yet'}</div>
                    </div>
                    ${unreadBadge}
                </div>
            `;
            $chatList.append(item);
        });
    }

    /**
     * Open chat with a user
     */
    function openChat(userId, userType) {
        if (!userId) {
            console.error('No user ID provided');
            return;
        }
        
        // Prevent chatting with yourself - check both ID and type
        const clickedUserId = parseInt(userId);
        const clickedUserType = userType || 'user'; // Default to 'user' if not provided
        const currentUserId = parseInt(window.currentUserId);
        const currentUserType = window.currentUserType || 'user';
        
        // Debug logging
        console.log('OpenChat validation:', {
            clickedUserId: clickedUserId,
            clickedUserType: clickedUserType,
            currentUserId: currentUserId,
            currentUserType: currentUserType,
            'ID Match?': (clickedUserId === currentUserId),
            'Type Match?': (clickedUserType === currentUserType),
            'Should Block?': (clickedUserId === currentUserId && clickedUserType === currentUserType)
        });
        
        // Only block if both ID AND type match
        if (clickedUserId === currentUserId && clickedUserType === currentUserType) {
            console.warn('Cannot chat with yourself. User ID and type match current user.');
            alert('You cannot chat with yourself. Please select a different user.');
            return;
        }
        
        console.log('Opening chat with user ID:', userId, 'userType:', clickedUserType, 'Current user ID:', currentUserId, 'Current user type:', currentUserType);
        currentChatUserId = userId;
        currentChatUserType = clickedUserType || 'user'; // Ensure it's set
        console.log('Set currentChatUserId:', currentChatUserId, 'currentChatUserType:', currentChatUserType);
        
        // Show chat active, hide default
        const $chatActive = $('.chat__active');
        const $chatDefault = $('.chat__default');
        
        console.log('Chat active element found:', $chatActive.length);
        console.log('Chat default element found:', $chatDefault.length);
        
        if ($chatActive.length === 0 || $chatDefault.length === 0) {
            console.error('Chat elements not found!');
            return;
        }
        
        $chatActive.removeClass('hidden');
        $chatDefault.addClass('hidden');
        
        console.log('Chat opened - active visible, default hidden');

        // Update chat header with user info - check both conversation list and contact list
        let userName = '';
        let userPhoto = '';
        
        // First try to get from conversation list
        const conversation = $('.chat__conversation-item[data-user-id="' + userId + '"]');
        if (conversation.length) {
            userName = conversation.find('.font-medium').text();
            userPhoto = conversation.find('img').attr('src');
        } else {
            // If not in conversations, get from contact list
            const userItem = $('.chat__user-item[data-user-id="' + userId + '"]');
            if (userItem.length) {
                userName = userItem.find('.text-xs').text();
                userPhoto = userItem.find('img').attr('src');
            }
        }
        
        // Update header if we found user info
        if (userName) {
            $('.chat__header-name').text(userName);
        }
        if (userPhoto) {
            $('.chat__header-avatar img').attr('src', userPhoto);
        }

        // Reset messages offset
        window.currentMessagesOffset = 0;
        window.hasMoreMessages = false;
        
        // Load messages
        loadMessages(userId);

        // Start polling for new messages
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
        }
        messagePollingInterval = setInterval(function() {
            if (currentChatUserId) {
                // Only load latest messages when polling (not load more)
                loadLatestMessages(currentChatUserId);
            }
        }, 3000); // Poll every 3 seconds
    }

    /**
     * Load latest messages only (for polling)
     */
    function loadLatestMessages(userId) {
        $.ajax({
            url: '/chat/messages/' + userId,
            type: 'GET',
            data: {
                limit: 15,
                offset: 0
            },
            success: function(response) {
                if (response.success && response.messages) {
                    // Check if there are new messages
                    const $messagesContainer = $('.chat__messages-container');
                    const existingMessageIds = [];
                    $messagesContainer.find('.message-item').each(function() {
                        const msgId = $(this).data('message-id');
                        if (msgId) {
                            existingMessageIds.push(parseInt(msgId));
                        }
                    });
                    
                    let hasNewMessages = false;
                    response.messages.forEach(function(message) {
                        const messageId = parseInt(message.id);
                        // Only append if message doesn't already exist
                        if (existingMessageIds.indexOf(messageId) === -1) {
                            hasNewMessages = true;
                            // Append new message
                            appendNewMessage(message);
                        }
                    });
                    
                    if (hasNewMessages) {
                        scrollToBottom();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading latest messages:', xhr);
            }
        });
    }
    
    /**
     * Append a new message to the bottom
     * Only adds if message doesn't already exist
     */
    function appendNewMessage(message) {
        const $messagesContainer = $('.chat__messages-container');
        
        // Check if message already exists to prevent duplication
        const messageId = parseInt(message.id);
        const existingMessage = $messagesContainer.find(`.message-item[data-message-id="${messageId}"]`);
        if (existingMessage.length > 0) {
            return; // Message already exists, don't add again
        }
        
        const messageDate = new Date(message.created_at);
        const dateStr = formatDate(messageDate);
        const timeStr = formatTime(messageDate);
        // Determine if message is from current user (sender) or receiver
        const messageFromId = parseInt(message.from_id);
        const messageToId = parseInt(message.to_id);
        const currentUserId = parseInt(window.currentUserId);
        const selectedUserId = parseInt(currentChatUserId);
        
        // CRITICAL: If from_id === currentUserId, it's SENDER (RIGHT side)
        // If to_id === currentUserId, it's RECEIVER (LEFT side)
        const isSender = (messageFromId === currentUserId);
        
        // Additional validation: message must be between current user and selected user
        const isValidMessage = (messageFromId === currentUserId && messageToId === selectedUserId) ||
                               (messageFromId === selectedUserId && messageToId === currentUserId);
        
        if (!isValidMessage) {
            console.warn('Message not between current user and selected user, skipping:', {
                messageFromId: messageFromId,
                messageToId: messageToId,
                currentUserId: currentUserId,
                selectedUserId: selectedUserId
            });
            return; // Skip this message
        }
        
        // Debug logging
        if (window.debugChat) {
            console.log('Append message check:', {
                messageFromId: messageFromId,
                messageToId: messageToId,
                currentUserId: currentUserId,
                selectedUserId: selectedUserId,
                isSender: isSender,
                'Should be RIGHT (sender)': isSender,
                'Should be LEFT (receiver)': !isSender,
                isValidMessage: isValidMessage,
                messageContent: message.message_content
            });
        }
        
        // Sender (current user sent) - RIGHT side: float-right, primary color, avatar right
        // Receiver (current user received) - LEFT side: float-left, light gray, avatar left
        const messageWrapperClass = isSender 
            ? 'chat__box__text-box float-right mb-4' 
            : 'chat__box__text-box float-left mb-4';
        const bubbleClass = isSender 
            ? 'bg-primary px-4 py-3 text-white rounded-l-md rounded-t-md' 
            : 'bg-slate-100 dark:bg-darkmode-400 px-4 py-3 text-slate-500 rounded-r-md rounded-t-md';
        const timeClass = isSender ? 'text-white text-opacity-80' : 'text-slate-500';
        const avatarPosition = isSender ? 'ml-5' : 'mr-5';
        const dropdownPosition = isSender ? 'mr-3 my-auto' : 'ml-3 my-auto';
        
        // Get avatar photo - use same default as topbar.blade.php
        let senderPhoto = '/dist/images/profile-5.jpg';
        if (message.sender_data && message.sender_data.photo) {
            senderPhoto = message.sender_data.photo;
        } else if (message.sender && message.sender.photo) {
            senderPhoto = message.sender.photo;
        } else if (message.from && message.from.photo) {
            senderPhoto = message.from.photo;
        }
        
        const defaultPhoto = '/dist/images/profile-5.jpg';
        
        // Check if we need date separator
        const lastDateStr = $messagesContainer.find('.message-item').last().data('date');
        if (lastDateStr && lastDateStr !== dateStr) {
            $messagesContainer.append(`
                <div class="text-slate-400 dark:text-slate-500 text-xs text-center mb-10 mt-5">${dateStr}</div>
            `);
        }
        
        // Receiver (left) structure: Avatar → Bubble → Dropdown
        // Sender (right) structure: Dropdown → Bubble → Avatar
        const messageHtml = isSender ? `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
            </div>
            <div class="clear-both"></div>
        ` : `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="clear-both"></div>
        `;
        
        $messagesContainer.append(messageHtml);
    }
    
    /**
     * Load messages for a conversation
     */
    function loadMessages(userId, silent = false, loadMore = false) {
        const limit = 15;
        const offset = loadMore ? (window.currentMessagesOffset || 0) + limit : 0;
        
        $.ajax({
            url: '/chat/messages/' + userId,
            type: 'GET',
            data: {
                limit: limit,
                offset: offset
            },
            success: function(response) {
                if (response.success) {
                    if (loadMore) {
                        // Append older messages at the top
                        prependMessages(response.messages, response.has_more);
                        window.currentMessagesOffset = offset;
                        window.hasMoreMessages = response.has_more;
                    } else {
                        // Replace all messages
                        window.currentMessagesOffset = 0;
                        window.hasMoreMessages = response.has_more;
                        renderMessages(response.messages, response.has_more);
                        if (!silent) {
                            scrollToBottom();
                        }
                    }
                    
                    // Reload conversations to update unread counts
                    if (!silent) {
                        loadConversations();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading messages:', xhr);
            }
        });
    }

    /**
     * Render messages
     */
    function renderMessages(messages, hasMore = false) {
        const $messagesContainer = $('.chat__messages-container');
        $messagesContainer.empty();
        
        // Remove existing load more button
        $messagesContainer.find('.load-more-messages').remove();

        // Add load more button at the top if there are more messages
        if (hasMore) {
            const loadMoreBtn = `
                <div class="load-more-messages text-center py-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="loadMoreMessages()">
                        Load older messages
                    </button>
                </div>
            `;
            $messagesContainer.prepend(loadMoreBtn);
        }

        let currentDate = null;

        messages.forEach(function(message) {
            // Only show messages between current user and selected user
            const messageFromId = parseInt(message.from_id);
            const messageToId = parseInt(message.to_id);
            const currentUserId = parseInt(window.currentUserId);
            const selectedUserId = parseInt(currentChatUserId);
            
            // Skip if message is not between current user and selected user
            if (messageFromId !== currentUserId && messageFromId !== selectedUserId) {
                return; // Skip this message
            }
            if (messageToId !== currentUserId && messageToId !== selectedUserId) {
                return; // Skip this message
            }
            
            const messageDate = new Date(message.created_at);
            const dateStr = formatDate(messageDate);
            
            // Show date separator if date changed
            if (!currentDate || currentDate !== dateStr) {
                $messagesContainer.append(`
                    <div class="text-slate-400 dark:text-slate-500 text-xs text-center mb-10 mt-5">${dateStr}</div>
                `);
                currentDate = dateStr;
            }

            // Determine if message is from current user (sender) or receiver
            // CRITICAL: If from_id === currentUserId, it's SENDER (RIGHT side)
            // If to_id === currentUserId, it's RECEIVER (LEFT side)
            const isSender = (messageFromId === currentUserId);
            
            // Debug logging
            if (window.debugChat) {
                console.log('Render message:', {
                    messageFromId: messageFromId,
                    messageToId: messageToId,
                    currentUserId: currentUserId,
                    selectedUserId: selectedUserId,
                    isSender: isSender,
                    'Should be RIGHT (sender)': isSender,
                    'Should be LEFT (receiver)': !isSender
                });
            }

            const timeStr = formatTime(messageDate);
            // Sender (current user sent) - RIGHT side: float-right, primary color, avatar right
            // Receiver (current user received) - LEFT side: float-left, light gray, avatar left
            const messageWrapperClass = isSender 
                ? 'chat__box__text-box flex items-end float-right mb-4' 
                : 'chat__box__text-box flex items-end float-left mb-4';
            const bubbleClass = isSender 
                ? 'bg-primary px-4 py-3 text-white rounded-l-md rounded-t-md' 
                : 'bg-slate-100 dark:bg-darkmode-400 px-4 py-3 text-slate-500 rounded-r-md rounded-t-md';
            const timeClass = isSender ? 'text-white text-opacity-80' : 'text-slate-500';
            const avatarPosition = isSender ? 'ml-5' : 'mr-5';
            const dropdownPosition = isSender ? 'mr-3 my-auto' : 'ml-3 my-auto';

            // Get avatar photo from sender_data or receiver_data
            // Use same default as topbar.blade.php
            let senderPhoto = '/dist/images/profile-5.jpg';
            if (message.sender_data && message.sender_data.photo) {
                senderPhoto = message.sender_data.photo;
            } else if (message.sender && message.sender.photo) {
                senderPhoto = message.sender.photo;
            } else if (message.from && message.from.photo) {
                senderPhoto = message.from.photo;
            }
            
            const defaultPhoto = '/dist/images/profile-5.jpg';
            
        // Receiver (left) structure: Avatar → Bubble → Dropdown
        // Sender (right) structure: Dropdown → Bubble → Avatar
        const messageHtml = isSender ? `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
            </div>
            <div class="clear-both"></div>
        ` : `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="clear-both"></div>
        `;
            
            $messagesContainer.append(messageHtml);
        });

        scrollToBottom();
    }
    
    /**
     * Prepend older messages (for load more)
     */
    function prependMessages(messages, hasMore) {
        const $messagesContainer = $('.chat__messages-container');
        const $loadMoreBtn = $messagesContainer.find('.load-more-messages');
        
        // Remove load more button temporarily
        $loadMoreBtn.remove();
        
        let currentDate = null;
        
        // Get the first date from existing messages to compare
        const firstMessageDate = $messagesContainer.find('.message-item').first().data('date');
        
        messages.forEach(function(message) {
            // Only show messages between current user and selected user
            const messageFromId = parseInt(message.from_id);
            const messageToId = parseInt(message.to_id);
            const currentUserId = parseInt(window.currentUserId);
            const selectedUserId = parseInt(currentChatUserId);
            
            // Skip if message is not between current user and selected user
            if (messageFromId !== currentUserId && messageFromId !== selectedUserId) {
                return; // Skip this message
            }
            if (messageToId !== currentUserId && messageToId !== selectedUserId) {
                return; // Skip this message
            }
            
            const messageDate = new Date(message.created_at);
            const dateStr = formatDate(messageDate);
            
            // Show date separator if date changed
            if (!currentDate || currentDate !== dateStr) {
                // Check if date is different from first existing message
                if (firstMessageDate && dateStr !== firstMessageDate) {
                    $messagesContainer.prepend(`
                        <div class="text-slate-400 dark:text-slate-500 text-xs text-center mb-10 mt-5">${dateStr}</div>
                    `);
                    currentDate = dateStr;
                } else if (!firstMessageDate) {
                    $messagesContainer.prepend(`
                        <div class="text-slate-400 dark:text-slate-500 text-xs text-center mb-10 mt-5">${dateStr}</div>
                    `);
                    currentDate = dateStr;
                }
            }

            // Determine if message is from current user (sender) or receiver
            // CRITICAL: If from_id === currentUserId, it's SENDER (RIGHT side)
            // If to_id === currentUserId, it's RECEIVER (LEFT side)
            const isSender = (messageFromId === currentUserId);
            
            // Debug logging
            if (window.debugChat) {
                console.log('Prepend message:', {
                    messageFromId: messageFromId,
                    messageToId: messageToId,
                    currentUserId: currentUserId,
                    selectedUserId: selectedUserId,
                    isSender: isSender,
                    'Should be RIGHT (sender)': isSender,
                    'Should be LEFT (receiver)': !isSender
                });
            }

            const timeStr = formatTime(messageDate);
            // Sender (current user sent) - RIGHT side: float-right, primary color, avatar right
            // Receiver (current user received) - LEFT side: float-left, light gray, avatar left
            const messageWrapperClass = isSender 
                ? 'chat__box__text-box flex items-end float-right mb-4' 
                : 'chat__box__text-box flex items-end float-left mb-4';
            const bubbleClass = isSender 
                ? 'bg-primary px-4 py-3 text-white rounded-l-md rounded-t-md' 
                : 'bg-slate-100 dark:bg-darkmode-400 px-4 py-3 text-slate-500 rounded-r-md rounded-t-md';
            const timeClass = isSender ? 'text-white text-opacity-80' : 'text-slate-500';
            const avatarPosition = isSender ? 'ml-5' : 'mr-5';
            const dropdownPosition = isSender ? 'mr-3 my-auto' : 'ml-3 my-auto';
            
            // Get avatar photo from sender_data or receiver_data
            // Use same default as topbar.blade.php
            let senderPhoto = '/dist/images/profile-5.jpg';
            if (message.sender_data && message.sender_data.photo) {
                senderPhoto = message.sender_data.photo;
            } else if (message.sender && message.sender.photo) {
                senderPhoto = message.sender.photo;
            } else if (message.from && message.from.photo) {
                senderPhoto = message.from.photo;
            }
            
            const defaultPhoto = '/dist/images/profile-5.jpg';
            
        // Receiver (left) structure: Avatar → Bubble → Dropdown
        // Sender (right) structure: Dropdown → Bubble → Avatar
        const messageHtml = isSender ? `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
            </div>
            <div class="clear-both"></div>
        ` : `
            <div class="message-item ${messageWrapperClass}" data-date="${dateStr}" data-message-id="${message.id}">
                <div class="w-10 h-10 hidden sm:block flex-none image-fit relative ${avatarPosition}">
                    <img alt="Avatar" class="rounded-full" src="${senderPhoto}" onerror="this.src='${defaultPhoto}'">
                </div>
                <div class="${bubbleClass}">
                    ${message.message_content}
                    <div class="mt-1 text-xs ${timeClass}">${timeStr}</div>
                </div>
                <div class="hidden sm:block dropdown ${dropdownPosition}">
                    <a href="javascript:;" class="dropdown-toggle w-4 h-4 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-more-vertical w-4 h-4">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-up-left w-4 h-4 mr-2">
                                        <polyline points="9 14 4 9 9 4"></polyline>
                                        <path d="M20 20v-7a4 4 0 00-4-4H4"></path>
                                    </svg> Reply
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash w-4 h-4 mr-2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                    </svg> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="clear-both"></div>
        `;
            
            $messagesContainer.prepend(messageHtml);
        });
        
        // Re-add load more button if there are more messages
        if (hasMore) {
            const loadMoreBtn = `
                <div class="load-more-messages text-center py-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="loadMoreMessages()">
                        Load older messages
                    </button>
                </div>
            `;
            $messagesContainer.prepend(loadMoreBtn);
        }
        
        // Store scroll position
        const scrollHeight = $messagesContainer.parent()[0].scrollHeight;
        const scrollTop = $messagesContainer.parent()[0].scrollTop;
        const height = $messagesContainer.parent()[0].clientHeight;
        
        // After prepending, restore scroll position
        setTimeout(function() {
            const newScrollHeight = $messagesContainer.parent()[0].scrollHeight;
            const scrollDiff = newScrollHeight - scrollHeight;
            $messagesContainer.parent()[0].scrollTop = scrollTop + scrollDiff;
        }, 100);
    }
    
    /**
     * Load more messages (older messages)
     */
    window.loadMoreMessages = function() {
        if (currentChatUserId && window.hasMoreMessages) {
            loadMessages(currentChatUserId, true, true);
        }
    };

    /**
     * Send a message
     */
    function sendMessage() {
        if (!currentChatUserId) {
            alert('Please select a conversation first');
            return;
        }
        
        // Prevent sending message to yourself - check both ID and type
        const toUserId = parseInt(currentChatUserId);
        const toUserType = currentChatUserType || 'user'; // Default to 'user' if not set
        const currentUserId = parseInt(window.currentUserId);
        const currentUserType = window.currentUserType || 'user';
        
        // Debug logging
        if (window.debugChat) {
            console.log('Send message check:', {
                toUserId: toUserId,
                toUserType: toUserType,
                currentUserId: currentUserId,
                currentUserType: currentUserType,
                currentChatUserType: currentChatUserType,
                'Should block?': (toUserId === currentUserId && toUserType === currentUserType)
            });
        }
        
        // Only block if both ID AND type match
        if (toUserId === currentUserId && toUserType === currentUserType) {
            alert('You cannot send messages to yourself. Please select a different user.');
            return;
        }

        const $input = $('.chat__box__input');
        const messageContent = $input.val().trim();

        if (!messageContent) {
            return;
        }

        // Disable input while sending
        $input.prop('disabled', true);

        $.ajax({
            url: '/chat/send',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                to_id: currentChatUserId,
                message_content: messageContent
            },
            success: function(response) {
                if (response.success && response.message) {
                    // Clear input
                    $input.val('').prop('disabled', false).focus();

                    // Debug logging
                    if (window.debugChat) {
                        console.log('Message sent successfully:', {
                            messageId: response.message.id,
                            from_id: response.message.from_id,
                            to_id: response.message.to_id,
                            currentUserId: window.currentUserId,
                            currentChatUserId: currentChatUserId,
                            'Should be sender (RIGHT)': (parseInt(response.message.from_id) === parseInt(window.currentUserId))
                        });
                    }

                    // Check if message already exists to avoid duplication
                    const $messagesContainer = $('.chat__messages-container');
                    const messageId = parseInt(response.message.id);
                    const existingMessage = $messagesContainer.find(`.message-item[data-message-id="${messageId}"]`);
                    
                    if (existingMessage.length === 0) {
                        // Only append if message doesn't exist
                        appendNewMessage(response.message);
                        scrollToBottom();
                    } else {
                        console.warn('Message already exists in DOM, not adding again:', messageId);
                    }

                    // Reload conversations to update list
                    loadConversations();
                }
            },
            error: function(xhr) {
                console.error('Error sending message:', xhr);
                $input.prop('disabled', false);
                alert('Error sending message. Please try again.');
            }
        });
    }

    /**
     * Scroll messages to bottom
     */
    function scrollToBottom() {
        const $container = $('.chat__messages-container').parent();
        if ($container.length && $container[0].scrollHeight) {
            // Use animate for smooth scroll
            $container.animate({
                scrollTop: $container[0].scrollHeight
            }, 300);
        }
    }

    /**
     * Format time ago
     */
    function formatTimeAgo(dateTime) {
        if (!dateTime) return '';
        
        const now = new Date();
        const then = new Date(dateTime);
        const diffMs = now - then;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return diffMins + ' mins ago';
        if (diffHours < 24) return diffHours + ' hours ago';
        if (diffDays < 7) return diffDays + ' days ago';
        
        return formatDate(then);
    }

    /**
     * Format date
     */
    function formatDate(date) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
    }

    /**
     * Format time
     */
    function formatTime(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }
    }); // End of jQuery document ready
    } // End of initializeChat
    
    // Start waiting for jQuery
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForJQuery);
    } else {
        waitForJQuery();
    }
})(); // End of IIFE

