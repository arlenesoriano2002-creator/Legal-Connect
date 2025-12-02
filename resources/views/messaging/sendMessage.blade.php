<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Staff Chat | LegalConnect</title>
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/messaging/sendMessage.css') }}">
</head>
<body>
<div class="chat-container">
    <!-- Sidebar for Users (excluding Admins) -->
    <aside class="sidebar">
        <h2>Users</h2>
        <input type="text" id="searchUser" placeholder="Search user..." onkeyup="filterUsers()">
        <ul id="userList">
            @foreach($users as $user)
                <li class="user-item" onclick="selectUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}')">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->email }}</span>
                </li>
            @endforeach
        </ul>
    </aside>

    <!-- Main Chat Area -->
    <main class="chat-main">
        <div class="chat-header">
            <h3 id="selectedUserName">Select a User</h3>
            <p id="selectedUserEmail" class="email-info"></p>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            @if(isset($messages) && count($messages))
                @foreach($messages as $msg)
                    <div class="message {{ $msg->sender_role }}">
                        <strong>{{ ucfirst($msg->sender_role) }}:</strong> {{ $msg->message }}
                        <small>{{ $msg->created_at->format('M d, Y h:i A') }}</small>
                    </div>
                @endforeach
            @else
                <p class="no-messages">No messages yet.</p>
            @endif
        </div>

        <!-- Send Message Form -->
        <form id="sendMessageForm" method="POST" action="{{ route('client.sendMessage') }}">
            @csrf
            <input type="hidden" name="receiver_id" id="receiverId" required>

            <div class="form-group">
                <label>Subject:</label>
                <input type="text" name="subject" id="subject" placeholder="Enter subject..." required>
            </div>

            <div class="form-group">
                <label>Message:</label>
                <textarea name="message" id="message" rows="4" placeholder="Type your message..." required></textarea>
            </div>

            <button type="submit" class="btn-send">Send Message</button>
        </form>

        <!-- Success / Error Alerts -->
        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif
    </main>
</div>

<script>
    /**
     * Select a user from the sidebar and set receiver_id
     */
    function selectUser(id, name, email) {
        document.getElementById('selectedUserName').innerText = name;
        document.getElementById('selectedUserEmail').innerText = email;
        document.getElementById('receiverId').value = id;
    }

    /**
     * Filter users dynamically by name or email
     */
    function filterUsers() {
        const search = document.getElementById('searchUser').value.toLowerCase();
        const users = document.querySelectorAll('.user-item');
        users.forEach(user => {
            const text = user.innerText.toLowerCase();
            user.style.display = text.includes(search) ? '' : 'none';
        });
    }

    /**
     * Prevent form submission if no receiver selected
     */
    document.getElementById('sendMessageForm').addEventListener('submit', function(event) {
        const receiverId = document.getElementById('receiverId').value;
        if (!receiverId) {
            alert('Please select a user to send a message.');
            event.preventDefault();
        }
    });
</script>

</body>
</html>
