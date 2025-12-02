<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/welcome.blade.css')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <title>Legal Connect - Online Legal Appointments</title>
    <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        <meta name="description" content="Book online legal appointments with experienced attorneys at Legal Connect">
    <?php endif; ?>
</head>

<body>
    <!-- Header without container wrapper -->
    <header>
        <a href="#" class="logo">
            <img class="logo-icon" src="<?php echo e(asset('logo6.png')); ?>" alt="">
            <div class="logo-text">Legal Connect</div>
        </a>
        <button class="burger-btn" onclick="toggleNav()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <nav id="main-nav">
            <a href="<?php echo e(url('/welcome')); ?>" class="admin-login">Home</a>
            <a href="<?php echo e(url('/about')); ?>" class="admin-login">About Us</a>
            <a href="<?php echo e(url('/testimonial')); ?>" class="admin-login">Testimonials</a>
            <a href="<?php echo e(url('/contact')); ?>" class="admin-login">Contact</a>

            <!-- Profile Icon with Dropdown -->
            <?php if(auth()->guard()->check()): ?>
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, <?php echo e(Auth::user()->name); ?>!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                        <span><?php echo e(Auth::user()->name); ?> &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openNotificationModal()" class="link-a">Notification</a>
                        <a href="#" onclick="openAccountModal()" class="link-a">Account</a>
                        <hr>
                        <a href="<?php echo e(route('logout')); ?>"
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                          Logout
                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo e(url('/login')); ?>" class="admin-login">Login/Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container1">
                <div class="message">
                    <span class="subtitle select-none">Legal Connect</span>
                    <h1>Legal Expertise When You Need It</h1>
                    <p>Schedule a consultation with our experienced attorneys to discuss your legal needs and get the expert advice you deserve.</p>
                </div>

                <div class="btn-group">
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(url('/Terms')); ?>" class="btn btn-primary">Schedule Appointment</a>
                    <?php endif; ?>
                    <a href="<?php echo e(url('/about')); ?>" class="btn btn-outline">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="container">
                <h2>Why Choose Legal Connect</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>Experienced Attorneys</h3>
                        <p>Our team has decades of combined experience handling complex legal matters across various practice areas.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚖️</div>
                        <h3>Client-Centered Approach</h3>
                        <p>We prioritize your needs and work diligently to achieve the best possible outcomes for your case.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🕒</div>
                        <h3>Convenient Scheduling</h3>
                        <p>Easily book consultations online and connect with our attorneys at times that work for you.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👩‍⚖️</div>
                        <h3>Verified Legal Professionals</h3>
                        <p>Connect only with trusted and verified lawyers, ensuring reliable legal advice every time.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="cta-section">
            <div class="container2">
                <h2>Ready to Get Started?</h2>
                <p>Our attorneys are ready to help with your legal matters. Schedule a consultation today and take the first step toward resolving your legal issues.</p>
                <a href="<?php echo e(url('/contact')); ?>" class="btn btn-primary1">Contact Us Now</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Legal Connect</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(url('/welcome')); ?>">Home</a></li>
                        <li><a href="<?php echo e(url('/about')); ?>">About</a></li>
                        <li><a href="<?php echo e(url('/testimonial')); ?>">Testimonials</a></li>
                        <li><a href="<?php echo e(url('/contact')); ?>">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="#">Family Law</a></li>
                        <li><a href="#">Personal Injury</a></li>
                        <li><a href="#">Real Estate</a></li>
                        <li><a href="#">Business Law</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Legal Connect All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Success Modal -->
    <?php if(session('success')): ?>
    <div id="successModal" class="modal-overlay">
        <div class="modal-box">
            <p><?php echo e(session('success')); ?></p>
            <button onclick="closeModal()">OK</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notification Modal -->
   <div id="notificationModal" class="modal">
    <div class="modal-content">
       
        <h2>Notifications</h2>
        <hr>
        <ul id="notificationList">
            <!-- Notifications will be loaded here -->
        </ul>
    </div>
</div>

    <!-- Account Modal -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div id="accountInfo">
                <!-- Account information will be loaded here -->
            </div>
        </div>
    </div>

    <script>


    // Simple, clean functions without complex parameters
    function toggleDropdown(event) {
        event.stopPropagation();
        var dropdown = document.getElementById("dropdownContent");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    function toggleNav() {
        const nav = document.getElementById('main-nav');
        nav.classList.toggle('active');
    }

    // Function to get status message
    function getStatusMessage(notification) {
        const status = notification.approval_appointment?.toLowerCase();
        const fullname = notification.fullname;
        const date = notification.appointment_date;
        const time = notification.appointment_time;
        
        let datetime = '';
        if (date) {
            const dateObj = new Date(date);
            datetime = " scheduled on " + dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            if (time) {
                datetime += " at " + time;
            }
        }
        
        switch (status) {
            case 'approved':
                return `🎉 Your appointment request for ${fullname} has been approved!${datetime}`;
            case 'denied':
                return `❌ Your appointment request for ${fullname} has been denied.${datetime}`;
            case 'pending':
                return `⏳ Your appointment request for ${fullname} is pending review.${datetime}`;
            default:
                return `📅 Appointment status updated for ${fullname}: ${notification.approval_appointment}${datetime}`;
        }
    }

    // Function to get status color
    function getStatusColor(status) {
        switch (status?.toLowerCase()) {
            case 'approved': return 'green';
            case 'denied': return 'red';
            case 'pending': return 'orange';
            default: return 'blue';
        }
    }

    // Function to get status icon
    function getStatusIcon(status) {
        switch (status?.toLowerCase()) {
            case 'approved': return '✅';
            case 'denied': return '❌';
            case 'pending': return '⏳';
            default: return '📅';
        }
    }

    // Auto-refresh interval variable
    let notificationInterval = null;

    function openNotificationModal() {
        console.log('Opening notification modal');
        const modal = document.getElementById('notificationModal');
        const notificationList = document.getElementById('notificationList');
        
        // Show loading state
        notificationList.innerHTML = '<li style="padding: 20px; text-align: center;">Loading approval history...</li>';

        // Fetch and display notifications
        fetchApprovalHistory()
        .then(data => {
            console.log('Approval history data:', data);
            renderApprovalHistory(data.notifications);
        })
        .catch(error => {
            console.error('Error fetching approval history:', error);
            notificationList.innerHTML = '<li style="padding: 20px; text-align: center; color: red;">Error loading approval history. Please try again.</li>';
        });

        modal.style.display = 'block';

        // Start auto-refresh every 5 seconds when modal is open
        notificationInterval = setInterval(() => {
            console.log('Auto-refreshing approval history...');
            fetchApprovalHistory()
            .then(data => {
                renderApprovalHistory(data.notifications);
                showUpdateIndicator();
            })
            .catch(error => {
                console.error('Error auto-refreshing approval history:', error);
            });
        }, 5000); // Refresh every 5 seconds
    }

    // Function to fetch approval history
    function fetchApprovalHistory() {
        return fetch('<?php echo e(route("notifications.approval-history")); ?>', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
    }

    // Function to render approval history
    function renderApprovalHistory(notifications) {
        const notificationList = document.getElementById('notificationList');
        notificationList.innerHTML = '';

        if (notifications && notifications.length > 0) {
            notifications.forEach(notification => {
                const li = document.createElement('li');
                const status = notification.approval_appointment?.toLowerCase();
                const statusColor = getStatusColor(status);
                const statusIcon = getStatusIcon(status);
                const message = getStatusMessage(notification);
                
                // Format the date
                const createdDate = new Date(notification.created_at);
                const formattedDate = createdDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                li.innerHTML = `
                    <div class="notification-item" style="padding: 15px; border-bottom: 1px solid #eee; background: ${status === 'approved' ? '#f0fff0' : status === 'denied' ? '#fff0f0' : '#fff9e6'};">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="font-size: 18px;">${statusIcon}</div>
                            <div style="flex: 1;">
                                <p style="margin: 0 0 8px 0; font-weight: 500;">${message}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #666;">
                                    <small>${formattedDate}</small>
                                    <span style="color: ${statusColor}; font-weight: bold; text-transform: uppercase; padding: 2px 8px; border: 1px solid ${statusColor}; border-radius: 12px;">
                                        ${notification.approval_appointment || 'Unknown'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                notificationList.appendChild(li);
            });
        } else {
            notificationList.innerHTML = `
                <li style="padding: 40px 20px; text-align: center; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 10px;">📝</div>
                    <p style="margin: 0;">No approval history found.</p>
                    <small>Your appointment approval history will appear here.</small>
                </li>
            `;
        }
    }

    function closeNotificationModal(event) {
        if (event) event.stopPropagation();
        console.log('Closing notification modal');
        document.getElementById('notificationModal').style.display = 'none';
        
        // Stop auto-refresh when modal is closed
        if (notificationInterval) {
            clearInterval(notificationInterval);
            notificationInterval = null;
        }
    }

    function openAccountModal() {
        console.log('Opening account modal');
        
        const userData = {
            name: "<?php echo e(Auth::user()->name ?? 'User'); ?>",
            email: "<?php echo e(Auth::user()->email ?? 'user@example.com'); ?>",
            cp_number: "<?php echo e(Auth::user()->cp_number ?? 'Not provided'); ?>",
            password: "••••••••"
        };

        const modal = document.getElementById('accountModal');
        const accountInfo = document.getElementById('accountInfo');
        accountInfo.innerHTML = `
            <div class="account-details">
                <div class="account-header">
                    <i class="fas fa-user-circle account-icon"></i>
                    <h3>Account Information</h3>
                </div>
                <table class="account-table">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>${userData.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone Number:</strong></td>
                        <td>${userData.cp_number}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${userData.email}</td>
                    </tr>
                    <tr>
                        <td><strong>Password:</strong></td>
                        <td>${userData.password}</td>
                    </tr>
                </table>
            </div>
        `;
        modal.style.display = 'block';
    }

    function closeAccountModal() {
        console.log('Closing account modal');
        document.getElementById('accountModal').style.display = 'none';
    }

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    // Visual indicator for updates
    function showUpdateIndicator() {
        const modalContent = document.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.boxShadow = '0 0 15px rgba(0,150,255,0.5)';
            setTimeout(() => {
                modalContent.style.boxShadow = '';
            }, 1000);
        }
    }

    // Close dropdown and modal when clicking outside
    window.onclick = function(event) {
        var notificationModal = document.getElementById('notificationModal');
        var accountModal = document.getElementById('accountModal');

        // Close modal when clicking outside
        if (event.target == notificationModal) {
            closeNotificationModal(event);
        }
        if (event.target == accountModal) {
            closeAccountModal();
        }
    };

    // Add event listener for Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeNotificationModal();
            closeAccountModal();
        }
    });
    </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/welcome.blade.php ENDPATH**/ ?>