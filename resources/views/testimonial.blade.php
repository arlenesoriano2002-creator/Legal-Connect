<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/testimonial.blade.css') }}">
<title>Legal Connect</title>
<style>

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
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
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                </div>
            </div>
        @else
            <a href="{{ url('/login') }}" class="admin-login">Login/Register</a>
        @endauth
    </nav>


    </header>
 <section class="hero">
     <img class="bg-img" src="{{ asset('a.jpg') }}" alt="Hero background image" />
    <div class="container1">
      
      <div class="message">
        <span class="subtitle select-none">Legal Connect</span>
        <h1>Book Appointments Easily and Securely</h1>
        <p>Seamless scheduling for legal consultations. Reliable, private, and always accessible.</p>
      </div>
    </div>
  </section>
  
   
    <div class="flex md-flex-row flex-column space-x-3" style="gap: 48px;">
      <!-- Recent Feedbacks -->
      <div class="md-w-1-2" style="flex:1;">
        <h2 style="font-weight:600; font-size:16px; margin-bottom:16px;">Recent Feedbacks</h2>

        @if(isset($averageRating) && $totalReviews > 0)
          <div style="margin-bottom:16px;">
            @for ($i = 1; $i <= 5; $i++)
              <i class="fas fa-star" style="color:{{ $i <= round($averageRating) ? '#f6c23e' : '#ccc' }}"></i>
            @endfor
            <span style="font-weight:600; color:#333;">
              {{ number_format($averageRating, 1) }} / 5
            </span>
            <small style="color:#666;">({{ $totalReviews }} {{ Str::plural('review', $totalReviews) }})</small>
          </div>
        @endif

        <!-- 🧩 Scrollable container -->
        <div class="reviews-scroll">
          @forelse($reviews as $review)
            <div class="review-card" style="margin-bottom:16px;">
              <img src="{{ $review->image ? asset('uploads/reviews/'.$review->image) : asset('default-user.png') }}"
                  alt="Client Image" class="w-16 h-16 rounded-full" />
              <div class="review-content">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                  <span class="review-name">{{ $review->name }}</span>
                  <span>
                    @for ($i = 1; $i <= 5; $i++)
                      <i class="fas fa-star" style="color:{{ $i <= $review->rating ? '#f6c23e' : '#ccc' }}"></i>
                    @endfor
                  </span>
                </div>
                <p class="review-text">{{ $review->review }}</p>
              </div>
            </div>
          @empty
            <p>No feedbacks yet.</p>
          @endforelse
        </div>
      </div>


      <!-- Add a Review -->
      <div class="md-w-1-2" style="flex:1; margin-top: 32px;">
        <h2 class="font-semibold text-base" style="font-weight:600; font-size:16px; margin-bottom:16px;">Send your Feedback</h2>
        <form id="reviewForm" enctype="multipart/form-data">
          @csrf

          @if(Auth::check())
            <div class="mb-4">
              <label for="name">Name <span>*</span></label>
              <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" readonly />
            </div>

            <div class="mb-4">
              <label for="email">Email <span>*</span></label>
              <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" readonly />
            </div>
          @else
            <div class="mb-4">
              <label for="name">Name <span>*</span></label>
              <input type="text" id="name" name="name" placeholder="Login required" disabled />
            </div>

            <div class="mb-4">
              <label for="email">Email <span>*</span></label>
              <input type="email" id="email" name="email" placeholder="Login required" disabled />
            </div>
          @endif

          <div class="mb-4">
            <label for="review">Write Your Review <span>*</span></label>
            <textarea id="review" name="review" rows="3" placeholder="Write here"></textarea>
          </div>

          <div class="mb-4">
            <label>Rating <span>*</span></label>
            <div class="star-rating">
              @for ($i = 5; $i >= 1; $i--)
                <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" required />
                <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
              @endfor
            </div>
          </div>

          <div class="mb-4">
            <label for="image">Upload Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*" />
          </div>

          <button type="submit">Submit</button>
        </form>


      </div>
    </div>
  </div>

 <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <h3>Legal Connect</h3>
          <ul class="footer-links">
            <li><a href="{{ url('/welcome') }}">Home</a></li>
            <li><a href="{{ url('/about') }}">About</a></li>
            <li><a href="{{ url('/testemonial') }}">Testimonials</a></li>
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
   <script>
    function toggleDropdown(event) {
        event.stopPropagation(); // Prevent window click from closing immediately
        var dropdown = document.getElementById("dropdownContent");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

// Close dropdown and modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('notificationModal');
    var accountModal = document.getElementById('accountModal');
    var dropdown = document.getElementById("dropdownContent");

    // Close modal when clicking outside
    if (event.target == modal) {
        modal.style.display = "none";
    }
    if (event.target == accountModal) {
        accountModal.style.display = "none";
    }

    // Close dropdown when clicking outside
    if (!event.target.closest('.profile-dropdown')) {
        dropdown.style.display = "none";
    }
};

    function toggleNav() {
        var nav = document.getElementById("main-nav");
        nav.classList.toggle("active");
    }
  </script>
  <!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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



</body>
</html>
