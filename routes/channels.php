<?php

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('admin.{adminId}', function ($user, $adminId) {
    return (int) $user->id === (int) $adminId;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
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