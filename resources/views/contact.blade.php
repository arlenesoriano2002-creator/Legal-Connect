<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Legal Connect</title>
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.3/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/contact.blade.css') }}">
</head>
<body>
   <div class="container">
      <header>
        <a href="#" class="logo">
        <img class="logo-icon" src="{{ asset('logo6.png')}}" alt="">
          <div class="logo-text">Legal Connect</div>
        </a>
        <button class="burger-btn" onclick="toggleNav()">
          <span class="bar"></span>
          <span class="bar"></span>
          <span class="bar"></span>
        </button>
        <nav id="main-nav">
            <a href="{{ url('/welcome') }}" class="admin-login">Home</a>
            <a href="{{ url('/about') }}" class="admin-login">About Us</a>
            <a href="{{ url('/testimonial') }}" class="admin-login">Testimonials</a>
            <a href="{{ url('/contact') }}" class="admin-login">Contact</a>

            <!-- Profile Icon with Dropdown -->
            @auth
                <div class="profile-dropdown">
                    <button type="button" onclick="toggleDropdown(event)">
                        Welcome, {{ Auth::user()->name }}!!
                    </button>
                    <div class="dropdown-content" id="dropdownContent">
                        <span>{{ Auth::user()->name }} &nbsp;<i class="fas fa-user"></i></span>
                        <hr>
                        <a href="#" onclick="openNotificationModal()">Notification</a>
                        <a href="#" onclick="openAccountModal()">Account</a>
                        <hr>
                        <a href="{{ route('logout') }}"
                          onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ url('/login') }}" class="admin-login">Login/Register</a>
            @endauth
        </nav>


      </header>
    </div>
    <div class="hero">
     <img class="bg-img" src="{{ asset('d2.jpg') }}" alt="Hero background image" />
    <div class="container1">
      
      <div class="message">
        <span class="subtitle select-none">Legal Connect</span>
        <h1>Contact Information.</h1>
        <p>Get in touch with us for reliable legal assistance.</p>
      </div>
    </div>
  </div>
  <!-- Contact Info and Map Section -->
<section class="container contact-section" aria-label="Contact information and location map">
  <div class="contact-content">
    <div class="contact-left">
      <h2>Get in touch</h2>
      <p class="description">Get in touch with us today for expert legal advice, consultation, and trusted notarial services near you.</p>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="contact-text">
          <h3>Head Office</h3>
          <p>Diffun, Quirino, Philippines</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="contact-text">
          <h3>Email us</h3>
          <p>LegalConnect@gmail.com</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="icon-box" aria-hidden="true">
          <i class="fas fa-phone-alt"></i>
        </div>
        <div class="contact-text">
          <h3>Call us</h3>
          <p>Phone : +6221.2002.2012,<br />Fax : +6221.2002.2913</p>
        </div>
      </div>
    </div>

    <div class= "maps">
      <div class="contact-right" aria-label="Office building photo and location map">
      <img src="{{ asset('maps.png') }}" alt="Hero backgroung image"/>
    </div>
      <div class="contact-right1" aria-label="Office building photo and location map">
        <img src="{{ asset('Ss1.png') }}" alt="Hero backgroung image"/>
      </div>
    </div>
  </div>
</section>

<!-- Send us a message Section -->
<section class="message-section" aria-label="Send us a message form">
  <h2>Send us a message</h2>
  <p>Have questions or concerns? Send us a message anytime below.</p>

  <form class="message-form" action="{{ route('message.store') }}" method="POST" novalidate>
    @csrf

    <div class="form-row">
      <div class="form-group">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" placeholder="Name" />
      </div>
      
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" placeholder="Phone" />
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="Email" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label for="subject">Subject</label>
        <input id="subject" name="subject" type="text" placeholder="Subject" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group full-width">
        <label for="message">Message</label>
        <textarea id="message" name="message" placeholder="Message" rows="4"></textarea>
      </div>
    </div>

    <button type="submit" class="send-button" aria-label="Send message">
      <i class="fas fa-paper-plane" aria-hidden="true"></i> SEND MESSAGE
    </button>
  </form>
</section>

<!-- Social Media Section 
<section class="social-section" aria-label="Follow our social media">
  <p>Follow our social media</p>
  <div class="social-icons">
    <a href="#" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a href="#" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
    <a href="#" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
    <a href="#" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube"></i></a>
  </div>
</section>-->

<footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <h3>Legal Connect</h3>
          <ul class="footer-links">
            <li><a href="{{ url('/welcome') }}">Home</a></li>
            <li><a href="{{ url('/about') }}">About</a></li>
            <li><a href="{{ url('/testimonial') }}">Testimonials</a></li>
            <li><a href="{{ url('/contact') }}">Contact</a></li>
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
  </footer>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
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
        return fetch('{{ route("notifications.approval-history") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
            name: "{{ Auth::user()->name ?? 'User' }}",
            email: "{{ Auth::user()->email ?? 'user@example.com' }}",
            cp_number: "{{ Auth::user()->cp_number ?? 'Not provided' }}",
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
    <span class="close" onclick="closeAccountModal(event)">&times;</span>
        <div id="accountInfo">
            <!-- Account information will be loaded here -->
        </div>
    </div>
</div>

<style>
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
  }

  .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }

  .close:hover,
  .close:focus {
    color: black;
    text-decoration: none;
  }

  #notificationList {
    list-style-type: none;
    padding: 0;
  }

  .notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
  }

  .notification-item p {
    margin: 0 0 5px 0;
  }

  .notification-item small {
    color: #666;
  }

.account-icon {
    font-size: 3rem;
    color: #352a6e;
    margin-bottom: 1rem;
}

.account-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.account-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #eee;
}

.account-table td:first-child {
    font-weight: bold;
    width: 40%;
}

.account-details {
    text-align: center;
}

.account-header {
    margin-bottom: 1rem;
}
</style>

</body>
</html>
