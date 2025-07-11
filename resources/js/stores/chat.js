import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useChatStore = defineStore('chat', () => {
    const chatRooms = ref([]);
    const currentChatRoom = ref(null);
    const messages = ref([]);
    const participants = ref([]);
    const typingUsers = ref([]);
    const onlineUsers = ref([]);
    const loading = ref(false);
    const messagesLoading = ref(false);

    // Computed properties
    const sortedChatRooms = computed(() => {
        return chatRooms.value.sort((a, b) => {
            return new Date(b.updated_at) - new Date(a.updated_at);
        });
    });

    const sortedMessages = computed(() => {
        return messages.value.sort((a, b) => {
            return new Date(a.created_at) - new Date(b.created_at);
        });
    });

    const currentChatParticipants = computed(() => {
        return participants.value.filter(p => p.pivot?.is_active);
    });

    // Actions
    const fetchChatRooms = async () => {
        loading.value = true;
        try {
            const response = await axios.get('/api/chat-rooms');
            
            console.log('🔍 Full API response structure:', response.data);
            console.log('🔍 Response keys:', Object.keys(response.data));
            
            // Handle pagination response structure
            const rooms = response.data.data || response.data || [];

            console.log('🔍 Rooms extracted:', rooms);

            // Log each room's latest message for debugging
            rooms.forEach(room => {
                console.log(`🔍 Room ${room.id} (${room.name}):`, {
                    latest_message: room.latest_message,
                    latest_message_content: room.latest_message?.content,
                    latest_message_is_deleted: room.latest_message?.is_deleted,
                    latest_message_updated_at: room.latest_message?.updated_at,
                    latest_message_created_at: room.latest_message?.created_at,
                    updated_at: room.updated_at
                });
            });

            // Ensure each room has participants data
            chatRooms.value = rooms.map(room => ({
                ...room,
                participants: room.active_participants || room.activeParticipants || []
            }));

            console.log('Chat rooms loaded:', chatRooms.value); // Debug log
            return { success: true };
        } catch (error) {
            console.error('Error fetching chat rooms:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch chat rooms' };
        } finally {
            loading.value = false;
        }
    };

    const fetchChatRoom = async (id) => {
        loading.value = true;
        try {
            const response = await axios.get(`/api/chat-rooms/${id}`);
            console.log('Chat room response:', response.data); // Debug log

            const roomData = response.data;
            currentChatRoom.value = {
                ...roomData,
                participants: roomData.active_participants || roomData.activeParticipants || []
            };

            participants.value = currentChatRoom.value.participants;
            console.log('Participants:', participants.value); // Debug log
            return { success: true };
        } catch (error) {
            console.error('Error fetching chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch chat room' };
        } finally {
            loading.value = false;
        }
    };

    const fetchMessages = async (chatRoomId, page = 1) => {
        messagesLoading.value = true;
        try {
            const response = await axios.get(`/api/chat-rooms/${chatRoomId}/messages?page=${page}`);
            if (page === 1) {
                messages.value = response.data.data || [];
            } else {
                messages.value = [...(response.data.data || []), ...messages.value];
            }
            return { success: true, hasMore: response.data.next_page_url !== null };
        } catch (error) {
            console.error('Error fetching messages:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to fetch messages' };
        } finally {
            messagesLoading.value = false;
        }
    };

    const sendMessage = async (chatRoomId, messageData) => {
        try {
            console.log('🚀 Chat Store: Sending message to API');
            console.log('📍 Chat Room ID:', chatRoomId);
            console.log('📨 Message Data:', messageData);

            let response;
            let config = {};

            // Handle FormData for file uploads
            if (messageData.formData) {
                config.headers = {
                    'Content-Type': 'multipart/form-data'
                };
                response = await axios.post(`/api/chat-rooms/${chatRoomId}/messages`, messageData.formData, config);
            } else {
                response = await axios.post(`/api/chat-rooms/${chatRoomId}/messages`, messageData);
            }

            const newMessage = response.data.data;
            console.log('✅ Chat Store: Message sent successfully');
            console.log('📨 New Message:', newMessage);

            // Check for conversion notice
            if (response.data.conversion_notice) {
                console.log('🔄 File conversion notice:', response.data.conversion_notice);
                return { 
                    success: true, 
                    message: newMessage,
                    conversion_notice: response.data.conversion_notice
                };
            }

            // Don't add message to local store here - real-time event will handle it
            console.log('⏳ Chat Store: Waiting for real-time event to add message');

            // Update chat room's last message
            const chatRoomIndex = chatRooms.value.findIndex(room => room.id === chatRoomId);
            if (chatRoomIndex !== -1) {
                chatRooms.value[chatRoomIndex].latest_message = newMessage;
                chatRooms.value[chatRoomIndex].updated_at = newMessage.created_at;
                console.log('✅ Chat Store: Chat room last message updated');
            }

            return { success: true, message: newMessage };
        } catch (error) {
            console.error('❌ Chat Store: Send message error:', error);
            console.error('❌ Error response:', error.response?.data);
            return { success: false, message: error.response?.data?.message || 'Failed to send message' };
        }
    };

    const editMessage = async (chatRoomId, messageId, content) => {
        try {
            const response = await axios.put(`/api/chat-rooms/${chatRoomId}/messages/${messageId}`, { content });
            const updatedMessage = response.data.data;

            const messageIndex = messages.value.findIndex(msg => msg.id === messageId);
            if (messageIndex !== -1) {
                messages.value[messageIndex] = updatedMessage;

                // Update chat room's latest message if this is the latest message
                const chatRoomIndex = chatRooms.value.findIndex(room => room.id === chatRoomId);
                if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].latest_message?.id === messageId) {
                    chatRooms.value[chatRoomIndex].latest_message = updatedMessage;
                    console.log('✅ Chat Store: Chat room latest message updated after local edit');
                }
            }

            return { success: true, message: updatedMessage };
        } catch (error) {
            console.error('Error editing message:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to edit message' };
        }
    };

    const deleteMessage = async (chatRoomId, messageId) => {
        try {
            await axios.delete(`/api/chat-rooms/${chatRoomId}/messages/${messageId}`);

            const messageIndex = messages.value.findIndex(msg => msg.id === messageId);
            if (messageIndex !== -1) {
                // Mark message as deleted instead of removing it
                const updatedMessage = {
                    ...messages.value[messageIndex],
                    is_deleted: true,
                    content: null,
                    deleted_at: new Date().toISOString()
                };
                
                messages.value[messageIndex] = updatedMessage;

                // Update chat room's latest message if this is the latest message
                const chatRoomIndex = chatRooms.value.findIndex(room => room.id === chatRoomId);
                if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].latest_message?.id === messageId) {
                    chatRooms.value[chatRoomIndex].latest_message = updatedMessage;
                    console.log('✅ Chat Store: Chat room latest message updated after local delete');
                }
            }

            return { success: true };
        } catch (error) {
            console.error('Error deleting message:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to delete message' };
        }
    };

    const createChatRoom = async (roomData) => {
        loading.value = true;
        try {
            const response = await axios.post('/api/chat-rooms', roomData);
            const newRoom = response.data.chat_room;
            chatRooms.value.unshift(newRoom);
            return { success: true, chatRoom: newRoom };
        } catch (error) {
            console.error('Error creating chat room:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to create chat room' };
        } finally {
            loading.value = false;
        }
    };

    const addParticipant = async (chatRoomId, userId, role = 'member') => {
        try {
            const response = await axios.post(`/api/chat-rooms/${chatRoomId}/participants`, {
                user_id: userId,
                role: role
            });

            // Refresh participants
            if (currentChatRoom.value?.id === chatRoomId) {
                await fetchChatRoom(chatRoomId);
            }

            return { success: true, participant: response.data.participant };
        } catch (error) {
            console.error('Error adding participant:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to add participant' };
        }
    };

    const removeParticipant = async (chatRoomId, userId) => {
        try {
            await axios.delete(`/api/chat-rooms/${chatRoomId}/participants/${userId}`);

            // Refresh participants
            if (currentChatRoom.value?.id === chatRoomId) {
                await fetchChatRoom(chatRoomId);
            }

            return { success: true };
        } catch (error) {
            console.error('Error removing participant:', error);
            return { success: false, message: error.response?.data?.message || 'Failed to remove participant' };
        }
    };

    const sendTypingStatus = async (chatRoomId, isTyping) => {
        try {
            await axios.post(`/api/chat-rooms/${chatRoomId}/typing`, { is_typing: isTyping });
            return { success: true };
        } catch (error) {
            console.error('Error sending typing status:', error);
            return { success: false };
        }
    };

    const markMessageAsRead = async (chatRoomId, messageId) => {
        try {
            await axios.post(`/api/chat-rooms/${chatRoomId}/messages/${messageId}/read`);
            return { success: true };
        } catch (error) {
            console.error('Error marking message as read:', error);
            return { success: false };
        }
    };

    // Real-time event handlers
    const addMessage = (message) => {
        console.log('🎉 Chat Store: Adding real-time message');
        console.log('📨 Real-time Message:', message);

        // Check if message already exists to avoid duplicates
        const existingMessage = messages.value.find(m => m.id === message.id);
        if (existingMessage) {
            console.log('⚠️ Chat Store: Message already exists, skipping duplicate');
            return;
        }

        messages.value.push(message);
        console.log('✅ Chat Store: Real-time message added to store');

        // Update chat room's last message
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === message.chat_room_id);
        if (chatRoomIndex !== -1) {
            chatRooms.value[chatRoomIndex].latest_message = message;
            chatRooms.value[chatRoomIndex].updated_at = message.created_at;
            console.log('✅ Chat Store: Chat room last message updated via real-time');
        }
    };

    const updateMessage = (updatedMessage) => {
        console.log('🔄 Chat Store: Updating real-time message');
        console.log('📝 Updated Message:', updatedMessage);
        console.log('📝 Current messages count:', messages.value.length);
        console.log('📝 Looking for message ID:', updatedMessage.id);

        const messageIndex = messages.value.findIndex(m => m.id === updatedMessage.id);
        console.log('📝 Message index found:', messageIndex);

        if (messageIndex !== -1) {
            console.log('📝 Old message:', messages.value[messageIndex]);
            messages.value[messageIndex] = updatedMessage;
            console.log('📝 New message:', messages.value[messageIndex]);
            console.log('✅ Chat Store: Message updated via real-time');

            // Update chat room's latest message if this is the latest message
            const chatRoomIndex = chatRooms.value.findIndex(room => room.id === updatedMessage.chat_room_id);
            if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].latest_message?.id === updatedMessage.id) {
                chatRooms.value[chatRoomIndex].latest_message = updatedMessage;
                console.log('✅ Chat Store: Chat room latest message updated after edit');
            }
        } else {
            console.log('❌ Chat Store: Message not found for update');
        }
    };

    const markMessageAsDeleted = (deletedMessage) => {
        console.log('🗑️ Chat Store: Marking message as deleted via real-time');
        console.log('🗑️ Deleted Message:', deletedMessage);

        const messageIndex = messages.value.findIndex(m => m.id === deletedMessage.id);
        if (messageIndex !== -1) {
            // Mark message as deleted instead of removing it
            const updatedMessage = {
                ...messages.value[messageIndex],
                is_deleted: true,
                content: null,
                deleted_at: deletedMessage.deleted_at || new Date().toISOString()
            };
            
            messages.value[messageIndex] = updatedMessage;
            console.log('✅ Chat Store: Message marked as deleted via real-time');

            // Update chat room's latest message if this is the latest message
            const chatRoomIndex = chatRooms.value.findIndex(room => room.id === deletedMessage.chat_room_id);
            if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].latest_message?.id === deletedMessage.id) {
                // If the deleted message was the latest, show it as deleted
                chatRooms.value[chatRoomIndex].latest_message = updatedMessage;
                console.log('✅ Chat Store: Chat room latest message updated after delete');
            }
        }
    };

    // Helper function to find previous non-deleted message
    const findPreviousMessage = (chatRoomId, excludeMessageId) => {
        const roomMessages = messages.value
            .filter(m => m.chat_room_id === chatRoomId && m.id !== excludeMessageId && !m.is_deleted)
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        return roomMessages.length > 0 ? roomMessages[0] : null;
    };

    const updateTypingUsers = (user, isTyping) => {
        if (isTyping) {
            if (!typingUsers.value.find(u => u.id === user.id)) {
                typingUsers.value.push(user);
            }
        } else {
            const index = typingUsers.value.findIndex(u => u.id === user.id);
            if (index !== -1) {
                typingUsers.value.splice(index, 1);
            }
        }
    };

    const addOnlineUser = (user) => {
        if (!onlineUsers.value.find(u => u.id === user.id)) {
            onlineUsers.value.push(user);
        }
    };

    const removeOnlineUser = (user) => {
        const index = onlineUsers.value.findIndex(u => u.id === user.id);
        if (index !== -1) {
            onlineUsers.value.splice(index, 1);
        }
    };

    const addChatRoom = (chatRoom) => {
        console.log('🏪 Chat Store: addChatRoom called with:', chatRoom);
        console.log('🏪 Current chat rooms count:', chatRooms.value.length);

        // Check if room already exists
        const existingIndex = chatRooms.value.findIndex(room => room.id === chatRoom.id);
        console.log('🏪 Existing room index:', existingIndex);

        if (existingIndex === -1) {
            chatRooms.value.unshift(chatRoom);
            console.log('✅ Chat room added! New count:', chatRooms.value.length);
        } else {
            console.log('⚠️ Chat room already exists, not adding');
        }
    };

    const updateUserAvatarInChats = (userData) => {
        console.log('🖼️ Chat Store: Updating user avatar in chats:', userData);

        // Update in chat rooms participants
        chatRooms.value.forEach((room, roomIndex) => {
            if (room.participants) {
                room.participants.forEach((participant, participantIndex) => {
                    if (participant.id === userData.id) {
                        chatRooms.value[roomIndex].participants[participantIndex] = {
                            ...participant,
                            ...userData
                        };
                        console.log(`✅ Avatar updated for participant ${userData.id} in room ${room.id}`);
                    }
                });
            }
        });

        // Update in current chat participants
        const participantIndex = participants.value.findIndex(p => p.id === userData.id);
        if (participantIndex !== -1) {
            participants.value[participantIndex] = {
                ...participants.value[participantIndex],
                ...userData
            };
            console.log(`✅ Avatar updated for current chat participant ${userData.id}`);
        }

        // Update in current chat room data
        if (currentChatRoom.value && currentChatRoom.value.participants) {
            const currentParticipantIndex = currentChatRoom.value.participants.findIndex(p => p.id === userData.id);
            if (currentParticipantIndex !== -1) {
                currentChatRoom.value.participants[currentParticipantIndex] = {
                    ...currentChatRoom.value.participants[currentParticipantIndex],
                    ...userData
                };
                console.log(`✅ Avatar updated for current room participant ${userData.id}`);
            }
        }
    };

    // Alias function to handle potential cache issues
    const handleUserAvatarUpdate = updateUserAvatarInChats;

    const updateChatRoomLastMessage = (chatRoomId, message) => {
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === chatRoomId);
        if (chatRoomIndex !== -1) {
            chatRooms.value[chatRoomIndex].latest_message = message;
            chatRooms.value[chatRoomIndex].updated_at = message.created_at;

            // Update unread count if not in current chat room
            if (currentChatRoom.value?.id !== chatRoomId) {
                chatRooms.value[chatRoomIndex].unread_count = (chatRooms.value[chatRoomIndex].unread_count || 0) + 1;
            }
        }
    };

    const resetUnreadCount = (chatRoomId) => {
        console.log('🏪 Chat Store: resetUnreadCount called for room:', chatRoomId);
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === chatRoomId);
        if (chatRoomIndex !== -1) {
            const oldCount = chatRooms.value[chatRoomIndex].unread_count || 0;
            chatRooms.value[chatRoomIndex].unread_count = 0;
            console.log(`✅ Unread count reset from ${oldCount} to 0 for room ${chatRoomId}`);
        } else {
            console.warn(`⚠️ Room ${chatRoomId} not found for unread count reset`);
        }
    };

    // Group management functions
    const updateGroupInfo = (groupId, newInfo) => {
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === groupId);
        if (chatRoomIndex !== -1) {
            chatRooms.value[chatRoomIndex] = {
                ...chatRooms.value[chatRoomIndex],
                ...newInfo
            };
        }
        
        // Update current chat room if it's the same group
        if (currentChatRoom.value?.id === groupId) {
            currentChatRoom.value = {
                ...currentChatRoom.value,
                ...newInfo
            };
        }
    };

    const addMemberToGroup = (groupId, member) => {
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === groupId);
        if (chatRoomIndex !== -1) {
            if (!chatRooms.value[chatRoomIndex].participants) {
                chatRooms.value[chatRoomIndex].participants = [];
            }
            chatRooms.value[chatRoomIndex].participants.push(member);
        }
        
        // Update current participants if it's the current chat room
        if (currentChatRoom.value?.id === groupId) {
            if (!participants.value) {
                participants.value = [];
            }
            participants.value.push(member);
        }
    };

    const removeMemberFromGroup = (groupId, memberId) => {
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === groupId);
        if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].participants) {
            chatRooms.value[chatRoomIndex].participants = 
                chatRooms.value[chatRoomIndex].participants.filter(p => p.id !== memberId);
        }
        
        // Update current participants if it's the current chat room
        if (currentChatRoom.value?.id === groupId) {
            participants.value = participants.value.filter(p => p.id !== memberId);
        }
    };

    const updateMemberRole = (groupId, memberId, newRole) => {
        const chatRoomIndex = chatRooms.value.findIndex(room => room.id === groupId);
        if (chatRoomIndex !== -1 && chatRooms.value[chatRoomIndex].participants) {
            const memberIndex = chatRooms.value[chatRoomIndex].participants.findIndex(p => p.id === memberId);
            if (memberIndex !== -1) {
                chatRooms.value[chatRoomIndex].participants[memberIndex].role = newRole;
            }
        }
        
        // Update current participants if it's the current chat room
        if (currentChatRoom.value?.id === groupId) {
            const memberIndex = participants.value.findIndex(p => p.id === memberId);
            if (memberIndex !== -1) {
                participants.value[memberIndex].role = newRole;
            }
        }
    };

    const removeGroupFromList = (groupId) => {
        chatRooms.value = chatRooms.value.filter(room => room.id !== groupId);
        
        // Clear current chat if it's the deleted group
        if (currentChatRoom.value?.id === groupId) {
            clearCurrentChat();
        }
    };

    // Clear functions
    const clearCurrentChat = () => {
        currentChatRoom.value = null;
        messages.value = [];
        participants.value = [];
        typingUsers.value = [];
    };

    const clearAll = () => {
        chatRooms.value = [];
        currentChatRoom.value = null;
        messages.value = [];
        participants.value = [];
        typingUsers.value = [];
        onlineUsers.value = [];
    };

    return {
        // State
        chatRooms,
        currentChatRoom,
        messages,
        participants,
        typingUsers,
        onlineUsers,
        loading,
        messagesLoading,

        // Computed
        sortedChatRooms,
        sortedMessages,
        currentChatParticipants,

        // Actions
        fetchChatRooms,
        fetchChatRoom,
        fetchMessages,
        sendMessage,
        editMessage,
        deleteMessage,
        createChatRoom,
        addParticipant,
        removeParticipant,
        sendTypingStatus,
        markMessageAsRead,

        // Real-time handlers
        addMessage,
        updateMessage,
        markMessageAsDeleted,
        updateTypingUsers,
        addOnlineUser,
        removeOnlineUser,
        addChatRoom,
        updateChatRoomLastMessage,
        resetUnreadCount,
        findPreviousMessage,
        updateUserAvatarInChats,
        handleUserAvatarUpdate,

        // Group management functions
        updateGroupInfo,
        addMemberToGroup,
        removeMemberFromGroup,
        updateMemberRole,
        removeGroupFromList,

        // Clear functions
        clearCurrentChat,
        clearAll
    };
});
