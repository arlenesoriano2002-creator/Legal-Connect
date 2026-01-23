<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    // Admin: Get all conversations (with JSON support for AJAX)
    public function adminIndex(Request $request)
{
    $user = Auth::user();
    
    // Check if user is admin or superadmin
    if (!in_array($user->role, ['admin', 'superadmin'])) {
        abort(403, 'Unauthorized access.');
    }
    
    $adminId = Auth::id();
    
    // Get conversations where admin is participant
    $conversations = ChatConversation::with(['client', 'messages' => function($query) {
        $query->orderBy('created_at', 'desc')->limit(1);
    }])
    ->where('admin_id', $adminId)
    ->orderBy('last_message_at', 'desc')
    ->get()
    ->map(function($conversation) use ($adminId) {
        $conversation->unread_count = $conversation->unreadMessagesCount($adminId);
        $conversation->last_message = optional($conversation->messages->first())->message;
        $conversation->last_message_time = optional($conversation->messages->first())->created_at;
        return $conversation;
    });

    // Get clients who don't have conversations yet
    $clientsWithoutConversations = User::where('role', 'client')
        ->whereDoesntHave('conversationsAsClient', function($query) use ($adminId) {
            $query->where('admin_id', $adminId);
        })
        ->get();

    // Return JSON for AJAX requests
    if ($request->has('data') && $request->get('data') === 'conversations') {
        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'clients_without_conversations' => $clientsWithoutConversations
        ]);
    }

    return view('admin.chat.index', compact('conversations', 'clientsWithoutConversations'));
}
public function pollForNewMessages(Request $request)
{
    $user = Auth::user();
    
    if (!in_array($user->role, ['admin', 'superadmin'])) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $conversationId = $request->input('conversation_id');
    $lastMessageId = $request->input('last_message_id', 0);
    
    if ($conversationId) {
        // Get messages newer than lastMessageId
        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->where('id', '>', $lastMessageId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'last_message_id' => $messages->isNotEmpty() ? $messages->last()->id : $lastMessageId
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'No conversation ID provided'
    ], 400);
}

    // Admin: Get messages for a conversation
public function adminGetMessages($conversationId)
{
    $conversation = ChatConversation::with(['messages.sender', 'client'])
        ->findOrFail($conversationId);

    // Mark messages as read for admin
    $conversation->markAsRead(Auth::id());

    return response()->json([
        'success' => true,
        'conversation' => $conversation,
        'messages' => $conversation->messages->sortBy('created_at')->values(), // Ensure sorted
        'client' => $conversation->client
    ]);
}

    // Admin: Send message
    public function adminSendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:file|string|max:2000',
            'file' => 'nullable|file|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $conversation = ChatConversation::findOrFail($conversationId);
        $adminId = Auth::id();

        // Create message
        $messageData = [
            'conversation_id' => $conversationId,
            'sender_id' => $adminId,
            'message_type' => 'text',
            'message' => $request->input('message', ''),
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chat_files', $fileName, 'public');

            $messageData['message_type'] = 'file';
            $messageData['file_path'] = $filePath;
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['file_size'] = $file->getSize();
            $messageData['file_mime'] = $file->getMimeType();
            $messageData['message'] = 'Sent a file: ' . $file->getClientOriginalName();
        }

        $message = ChatMessage::create($messageData);

        // Update conversation last message time
        $conversation->update(['last_message_at' => now()]);

        // Broadcast event for real-time updates
        event(new \App\Events\ChatMessageSent($message));

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    // Admin: Start new conversation
    public function adminStartConversation(Request $request)
{
    \Log::info('Starting conversation with data:', $request->all()); // Add logging
    
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:users,id',
        'message' => 'nullable|string|max:2000', // Change to nullable
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed:', $validator->errors()->toArray()); // Add logging
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $adminId = Auth::id();
    $clientId = $request->input('client_id');

    // Check if conversation already exists
    $existingConversation = ChatConversation::with(['client'])
        ->where('admin_id', $adminId)
        ->where('client_id', $clientId)
        ->first();

    if ($existingConversation) {
        \Log::info('Conversation already exists:', ['conversation_id' => $existingConversation->id]);
        return response()->json([
            'success' => true, // Return success since conversation exists
            'conversation' => $existingConversation,
            'message' => 'Conversation already exists'
        ]);
    }

    // Create conversation
    $conversation = ChatConversation::create([
        'admin_id' => $adminId,
        'client_id' => $clientId,
        'last_message_at' => now()
    ]);

    // Add participants
    ChatParticipant::create([
        'conversation_id' => $conversation->id,
        'user_id' => $adminId,
        'role' => 'admin'
    ]);

    ChatParticipant::create([
        'conversation_id' => $conversation->id,
        'user_id' => $clientId,
        'role' => 'client'
    ]);

    // Create first message if provided
    $message = null;
    if ($request->filled('message')) {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $adminId,
            'message_type' => 'text',
            'message' => $request->input('message')
        ]);
        
        // Broadcast event
        event(new \App\Events\ChatMessageSent($message));
    }

    \Log::info('Conversation created successfully:', ['conversation_id' => $conversation->id]);

    return response()->json([
        'success' => true,
        'conversation' => $conversation->load('client'),
        'message' => $message
    ]);
}

    // Client: Get conversation
    public function clientGetConversation()
    {
        $clientId = Auth::id();
        
        $conversation = ChatConversation::with(['messages.sender', 'admin'])
            ->where('client_id', $clientId)
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'conversation' => null,
                'messages' => []
            ]);
        }

        // Mark messages as read for client
        $conversation->markAsRead($clientId);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $conversation->messages
        ]);
    }

    // Client: Send message
    public function clientSendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required_without:file|string|max:2000',
            'file' => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $clientId = Auth::id();

        // Get or create conversation
        $conversation = ChatConversation::where('client_id', $clientId)->first();

        if (!$conversation) {
            // Get first admin
            $admin = User::where('role', 'admin')->first();
            
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'No admin available'], 404);
            }

            $conversation = ChatConversation::create([
                'admin_id' => $admin->id,
                'client_id' => $clientId,
                'last_message_at' => now()
            ]);

            // Add participants
            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $admin->id,
                'role' => 'admin'
            ]);

            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $clientId,
                'role' => 'client'
            ]);
        }

        // Create message
        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $clientId,
            'message_type' => 'text',
            'message' => $request->input('message', ''),
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chat_files', $fileName, 'public');

            $messageData['message_type'] = 'file';
            $messageData['file_path'] = $filePath;
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['file_size'] = $file->getSize();
            $messageData['file_mime'] = $file->getMimeType();
            $messageData['message'] = 'Sent a file: ' . $file->getClientOriginalName();
        }

        $message = ChatMessage::create($messageData);

        // Update conversation last message time
        $conversation->update(['last_message_at' => now()]);

        // Broadcast event
        event(new \App\Events\ChatMessageSent($message));

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    // Download file
    public function downloadFile($messageId)
    {
        $message = ChatMessage::findOrFail($messageId);
        
        if (!$message->isFile() || !$message->file_path) {
            abort(404);
        }

        // Check if user is participant in the conversation
        $isParticipant = ChatParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isParticipant) {
            abort(403);
        }

        $filePath = storage_path('app/public/' . $message->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $message->file_name);
    }

    // Get unread count for current user
    public function getUnreadCount()
    {
        $userId = Auth::id();
        
        $count = ChatMessage::whereHas('conversation.participants', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('sender_id', '!=', $userId)
        ->whereNull('read_at')
        ->count();

        return response()->json(['count' => $count]);
    }

    // Check for new messages
    public function checkNewMessages($lastMessageId = null)
    {
        $userId = Auth::id();
        
        $query = ChatMessage::whereHas('conversation.participants', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('sender_id', '!=', $userId)
        ->whereNull('read_at');

        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        }

        $newMessages = $query->exists();

        return response()->json(['has_new' => $newMessages]);
    }

    // Mark a specific message as read
    public function markMessageAsRead($messageId)
    {
        $userId = Auth::id();
        
        $message = ChatMessage::find($messageId);
        
        if ($message && $message->sender_id != $userId) {
            $message->update(['read_at' => now()]);
        }
        
        return response()->json(['success' => true]);
    }

    // Mark entire conversation as read
    public function markConversationAsRead($conversationId)
    {
        $userId = Auth::id();
        
        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    // Get admin dashboard data (for admin dashboard)
    public function getAdminChatData()
    {
        $adminId = Auth::id();
        
        if (!Auth::user()->hasRole(['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $totalConversations = ChatConversation::where('admin_id', $adminId)->count();
        $unreadMessages = ChatMessage::whereHas('conversation', function($query) use ($adminId) {
            $query->where('admin_id', $adminId);
        })
        ->where('sender_id', '!=', $adminId)
        ->whereNull('read_at')
        ->count();

        $recentConversations = ChatConversation::with(['client', 'messages' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(1);
        }])
        ->where('admin_id', $adminId)
        ->orderBy('last_message_at', 'desc')
        ->limit(5)
        ->get();

        return response()->json([
            'success' => true,
            'total_conversations' => $totalConversations,
            'unread_messages' => $unreadMessages,
            'recent_conversations' => $recentConversations
        ]);
    }

    // Delete conversation (admin only)
    public function deleteConversation($conversationId)
    {
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversation = ChatConversation::findOrFail($conversationId);
        
        // Check if the admin owns this conversation
        if ($conversation->admin_id != $user->id && !$user->hasRole('superadmin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Delete all messages first
        ChatMessage::where('conversation_id', $conversationId)->delete();
        
        // Delete participants
        ChatParticipant::where('conversation_id', $conversationId)->delete();
        
        // Delete conversation
        $conversation->delete();

        return response()->json(['success' => true, 'message' => 'Conversation deleted successfully']);
    }

    // Search conversations (admin only)
    public function searchConversations(Request $request)
    {
        $adminId = Auth::id();
        $searchTerm = $request->get('q', '');
        
        if (!Auth::user()->hasRole(['admin', 'superadmin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $conversations = ChatConversation::with(['client'])
            ->where('admin_id', $adminId)
            ->whereHas('client', function($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->orWhereHas('messages', function($query) use ($searchTerm) {
                $query->where('message', 'like', "%{$searchTerm}%");
            })
            ->orderBy('last_message_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }
    // In your ChatController.php - adminGetConversations method
public function adminGetConversations(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    
    $adminId = Auth::id();
    
    // Get conversations where admin is participant
    $conversations = ChatConversation::with(['client', 'messages' => function($query) {
        $query->orderBy('created_at', 'desc')->limit(1);
    }])
    ->where('admin_id', $adminId)
    ->orderBy('last_message_at', 'desc')
    ->get()
    ->map(function($conversation) use ($adminId) {
        $conversation->unread_count = $conversation->unreadMessagesCount($adminId);
        $conversation->last_message = optional($conversation->messages->first())->message;
        $conversation->last_message_time = optional($conversation->messages->first())->created_at;
        return $conversation;
    });

    // Get ALL clients (not just those without conversations)
     $allClients = User::where('role', 'client')
        ->select('id', 'name', 'email', 'role', 'created_at')
        ->orderBy('name', 'asc')
        ->get();

    return response()->json([
        'success' => true,
        'conversations' => $conversations,
        'all_clients' => $allClients,
        'conversations_count' => $conversations->count(),
        'clients_count' => $allClients->count()
    ]);
}
public function adminSendMessageWithFiles(Request $request, $conversationId)
{
    $validator = Validator::make($request->all(), [
        'message' => 'nullable|string|max:2000',
        'files.*' => 'nullable|file|max:10240', // 10MB max per file
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $conversation = ChatConversation::findOrFail($conversationId);
    $adminId = Auth::id();

    $messageData = [
        'conversation_id' => $conversationId,
        'sender_id' => $adminId,
        'message_type' => 'text',
        'message' => $request->input('message', ''),
    ];

    // Handle multiple file uploads
    if ($request->hasFile('files')) {
        $files = $request->file('files');
        $fileMessages = [];
        
        foreach ($files as $file) {
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('chat_files', $fileName, 'public');

            // Create separate message for each file
            $fileMessage = ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_id' => $adminId,
                'message_type' => 'file',
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
                'message' => 'Sent a file: ' . $file->getClientOriginalName(),
            ]);

            $fileMessages[] = $fileMessage;
            
            // Broadcast each file message
            event(new \App\Events\ChatMessageSent($fileMessage));
        }
    }

    // Create text message if there's any text
    if (!empty($request->input('message'))) {
        $message = ChatMessage::create($messageData);
        
        // Update conversation last message time
        $conversation->update(['last_message_at' => now()]);
        
        // Broadcast event for real-time updates
        event(new \App\Events\ChatMessageSent($message));
        
        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => $fileMessages[0]->load('sender') ?? null
    ]);
}
public function handleTyping(Request $request)
{
    $validator = Validator::make($request->all(), [
        'conversation_id' => 'required|exists:chat_conversations,id',
        'is_typing' => 'required|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $userId = Auth::id();
    $conversationId = $request->input('conversation_id');
    $isTyping = $request->input('is_typing');

    // Get conversation
    $conversation = ChatConversation::find($conversationId);
    
    if (!$conversation) {
        return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
    }

    // Check if user is participant
    $isParticipant = ChatParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $userId)
        ->exists();

    if (!$isParticipant) {
        return response()->json(['success' => false, 'message' => 'Not a participant'], 403);
    }

    // Get user info for broadcast
    $user = Auth::user();
    
    // Broadcast typing event
    if ($isTyping) {
        $eventData = [
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'user_name' => $user->name,
            'is_typing' => true,
            'client_name' => $user->name
        ];
        
        // For admin, broadcast to client
        if ($user->role === 'admin') {
            $client = $conversation->client;
            event(new \App\Events\ChatTyping($eventData, $client->id));
        } else {
            // For client, broadcast to admin
            event(new \App\Events\ChatTyping($eventData, $conversation->admin_id));
        }
    }

    return response()->json(['success' => true, 'is_typing' => $isTyping]);
}

// Add this method to get a single conversation
public function adminGetConversation($conversationId)
{
    if (auth()->user()->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $conversation = ChatConversation::with(['client', 'messages' => function($query) {
        $query->orderBy('created_at', 'desc');
    }])->find($conversationId);

    if (!$conversation) {
        return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
    }

    // Mark messages as read for admin
    $conversation->markAsRead(Auth::id());

    return response()->json([
        'success' => true,
        'conversation' => $conversation,
        'messages' => $conversation->messages,
        'client' => $conversation->client
    ]);
}
public function pollNewMessages(Request $request)
{
    $user = Auth::user();
    
    if (!in_array($user->role, ['admin', 'superadmin'])) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $conversationId = $request->input('conversation_id');
    $lastMessageId = $request->input('last_message_id', 0);
    
    if ($conversationId) {
        // Get new messages for specific conversation
        $messages = ChatMessage::where('conversation_id', $conversationId)
            ->where('id', '>', $lastMessageId)
            ->where('sender_id', '!=', $user->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
            
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'last_message_id' => $messages->last() ? $messages->last()->id : $lastMessageId
        ]);
    }
    
    // Get all unread messages count for the admin
    $unreadCount = ChatMessage::whereHas('conversation', function($query) use ($user) {
        $query->where('admin_id', $user->id);
    })
    ->where('sender_id', '!=', $user->id)
    ->whereNull('read_at')
    ->count();
    
    return response()->json([
        'success' => true,
        'unread_count' => $unreadCount,
        'last_message_id' => $lastMessageId
    ]);
}
}