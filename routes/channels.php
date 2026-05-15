<?php

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('admin.{adminId}', function ($user, $adminId) {
    return (int) $user->id === (int) $adminId;
});

Broadcast::channel('admin-message-notifications.{userId}', function ($user, $userId) {
    // Only allow the specific admin to listen to their own notification channel
    return (int) $user->id === (int) $userId && in_array($user->role, ['admin', 'superadmin']);
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }
    
    // Check if user is either the admin or client in this conversation
    return $user->id === $conversation->admin_id || 
           $user->id === $conversation->client_id;
});

/**
 * Call Channel Authorization
 * Private channel for WebRTC signaling
 * Users can only listen to their own call channel
 */
Broadcast::channel('call.user.{userId}', function ($user, $userId) {
    // Only allow the specific user to listen to their own call channel
    return (int) $user->id === (int) $userId;
});