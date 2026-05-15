<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Staff Chat | LegalConnect</title>
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="<?php echo e(asset('css/messaging/sendMessage.css')); ?>">
</head>
<body>
<div class="chat-container">
    <!-- Sidebar for Users (excluding Admins) -->
    <aside class="sidebar">
        <h2>Users</h2>
        <input type="text" id="searchUser" placeholder="Search user..." onkeyup="filterUsers()">
        <ul id="userList">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="user-item" onclick="selectUser('<?php echo e($user->id); ?>', '<?php echo e($user->name); ?>', '<?php echo e($user->email); ?>')">
                    <strong><?php echo e($user->name); ?></strong>
                    <span><?php echo e($user->email); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            <?php if(isset($messages) && count($messages)): ?>
                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="message <?php echo e($msg->sender_role); ?>">
                        <strong><?php echo e(ucfirst($msg->sender_role)); ?>:</strong> <?php echo e($msg->message); ?>

                        <small><?php echo e($msg->created_at->format('M d, Y h:i A')); ?></small>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <p class="no-messages">No messages yet.</p>
            <?php endif; ?>
        </div>

        <!-- Send Message Form -->
        <form id="sendMessageForm" method="POST" action="<?php echo e(route('client.sendMessage')); ?>">
            <?php echo csrf_field(); ?>
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
        <?php if(session('success')): ?>
            <div class="alert success"><?php echo e(session('success')); ?></div>
        <?php elseif(session('error')): ?>
            <div class="alert error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
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
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\messaging\sendMessage.blade.php ENDPATH**/ ?>