<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Remove the Tailwind CDN and use only Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/admindashboard.blade.css') }}">
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ url('/admindashboard') }}" class="list-group-item list-group-item-action {{ request()->is('admindashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ url('/administrator') }}" class="list-group-item list-group-item-action {{ request()->is('administrator') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                <a href="{{ url('/appointments') }}" class="list-group-item list-group-item-action {{ request()->is('appointments') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action {{ request()->is('email-chat') || request()->is('messages/*') ? 'active' : '' }}"
                data-bs-toggle="collapse" 
                aria-expanded="{{ request()->is('email-chat') || request()->is('messages/*') ? 'true' : 'false' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse {{ request()->is('email-chat') || request()->is('messages/*') ? 'show' : '' }} list-group" id="messagesSubmenu">
                    <a href="{{ route('messages.email') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('messages.system-chat') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="{{ url('/practice-areas') }}" class="list-group-item list-group-item-action {{ request()->is('practice-areas') ? 'active' : '' }}">
                    <i class="fa-solid fa-suitcase"></i>
                    
                    <span>Practice Areas</span>
                </a>
                <a href="#requestsSubmenu" class="list-group-item list-group-item-action" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse list-group {{ request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : '' }}" id="requestsSubmenu">
                    <a href="{{ url('/clientstbl') }}" class="list-group-item list-group-item-action {{ request()->is('clientstbl') ? 'active' : '' }}">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="{{ url('/adminAcceptedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminAcceptedRequest') ? 'active' : '' }}">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="{{ url('/adminDeniedRequest') }}" class="list-group-item list-group-item-action {{ request()->is('adminDeniedRequest') ? 'active' : '' }}">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="{{ url('/adminAccount') }}" class="list-group-item list-group-item-action {{ request()->is('adminAccount') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>All Accounts</span>
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i> 
                </button>
                
                <div class="top-bar-spacer"></div>

                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
        <!-- Updated Header Section -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Appointment Analytics Dashboard</h1>
                        <p class="text-gray-600 mt-2">Provides a clear overview of all appointment activities</p>
                    </div>

                    <!-- Rest of your dashboard content -->
                    <div class="stats-container">
                        <!-- Total Appointments Card -->
                        <div class="stat-card card-total">
                            <div class="stat-header">
                                <div class="stat-title">Total Appointments</div>
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                            <div class="stat-value">{{ $totalAppointments }}</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-chart-line"></i> All Time
                                </span>
                            </div>
                        </div>
                    
                    <!-- Pending Appointments Card - Clickable -->
                    <div class="stat-card card-pending clickable-card" onclick="window.location.href='{{ route('clientstbl') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Pending Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $pendingAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-user-clock"></i> Awaiting Review
                            </span>
                        </div>
                    </div>
                    
                    <!-- Approved Appointments Card - Clickable -->
                    <div class="stat-card card-approved clickable-card" onclick="window.location.href='{{ route('adminAcceptedRequest') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Approved Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $approvedAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-calendar-plus"></i> Confirmed
                            </span>
                        </div>
                    </div>
                    
                    <!-- Denied Appointments Card - Clickable -->
                    <div class="stat-card card-denied clickable-card" onclick="window.location.href='{{ route('adminDeniedRequest') }}'">
                        <div class="stat-header">
                            <div class="stat-title">Denied Requests</div>
                            <div class="stat-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value">{{ $deniedAppointments }}</div>
                        <div class="stat-footer">
                            <span class="stat-trend">
                                <i class="fas fa-ban"></i> Not Approved
                            </span>
                        </div>
                    </div>
                    
                    <!-- logs & backups-->
                    <div class="archive-card" style="width: 18rem;">
                        <div class="card-body">
                            <h5 class="card-title">Logs Records & Backups</h5>
                            <p class="card-text">View Logs appointments and download it.</p>
                            <div class="card-footer" style="text-align:center;">
                                <button onclick="window.location.href='{{ url('/appointments') }}'" class="modal-btn primary" type="button">
                                    View Logs Records
                                </button>
                                <button id="btnViewBackups" class="modal-btn secondary" type="button">View Logs Backups</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="activity-section">
                    <!-- Left: Recent Activity -->
                    <div class="recent-activity">
                        <h3 class="activity-title">Recent Appointment Requests</h3>
                        <ul class="activity-list">
                            @forelse($recentAppointments as $appointment)
                                <li class="activity-item">
                                    <div class="activity-icon"><i class="fas fa-user"></i></div>
                                    <div class="activity-details">
                                        <div class="activity-name">{{ $appointment->fullname ?? 'Unknown Client' }}</div>
                                        <div class="activity-time">
                                            {{ \Carbon\Carbon::parse($appointment->created_at)->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                    <div class="activity-status 
                                        @if($appointment->appointment_approval == 'pending') status-pending
                                        @elseif($appointment->appointment_approval == 'approved') status-approved
                                        @elseif($appointment->appointment_approval == 'denied') status-denied
                                        @endif">
                                        {{ ucfirst($appointment->appointment_approval) }}
                                    </div>
                                </li>
                            @empty
                                <li class="activity-item">
                                    <div class="activity-details">No recent appointments found.</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Right: Feedback Chart -->
                    <div class="feedback-chart">
                        <h3 class="activity-title">Feedback Summary</h3>
                        <canvas id="feedbackBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

           

    <!-- ====================== BACKUP MODAL ====================== -->
<dialog id="backupModal" class="admin-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Database Backups</h2>
            <!-- Add filter dropdown here -->
            <div class="modal-actions">
                 <button class="modal-btn close-modal">×</button>
                <div class="backup-filter-container">
                    <label for="backupFilter">Filter Backups:</label>
                    <select id="backupFilter" class="backup-filter-select">
                        <option value="all">All Backups</option>
                        <option value="pending">Pending</option>
                        <option value="denied">Denied</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-body">
            @include('partials.backup-manager', ['backups' => $backups])
        </div>
    </div>
</dialog>

    <!-- Chat Panel Dropdown -->
    @php
        $users = \App\Models\User::select('id', 'name', 'email')->whereNotNull('email')->get();
    @endphp

    <div class="chat-panel" id="chatPanel">
        <div class="chat-panel-container">
            <!-- Users List -->
            <div class="user-list-section">
                <input type="text" id="searchUser" placeholder="Search user..." onkeyup="filterUsers()">
                <ul id="userList">
                    @foreach($users as $user)
                        <li class="user-item" onclick="selectUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->email }}')">
                            <strong>{{ $user->name }}</strong><br>
                            <span style="font-size: 12px; color: gray;">{{ $user->email }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Section -->
            <div class="message-section">
                <div class="message-display">
                    <p id="noMessageText" style="color: gray;">Select a user to start chat</p>
                </div>
                <form id="sendMessageForm" method="POST" action="{{ route('client.sendMessage') }}">
                    @csrf
                    <input type="hidden" name="receiver_id" id="receiverId">
                    <input type="text" name="subject" placeholder="Subject..." required>
                    <textarea name="message" placeholder="Type your message..." rows="3" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <div id="backupSuccessToast" class="toast-success">
        ✅ Backup Created Successfully!
    </div>

  <!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Simple sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('toggled');
        });
    }

    // Chat functions
    window.selectUser = function(id, name, email) {
        document.getElementById('receiverId').value = id;
        document.getElementById('noMessageText').innerText = "Messaging " + name;
    };

    window.filterUsers = function() {
        const search = document.getElementById('searchUser').value.toLowerCase();
        document.querySelectorAll('.user-item').forEach(user => {
            user.style.display = user.innerText.toLowerCase().includes(search) ? '' : 'none';
        });
    };

    window.toggleChatPanel = function() {
        const chatPanel = document.getElementById("chatPanel");
        if (chatPanel) {
            chatPanel.style.display = chatPanel.style.display === "block" ? "none" : "block";
        }
    };

    // Modal functionality
    const modalArchive = document.getElementById("archiveModal");
    const modalBackup = document.getElementById("backupModal");
    const btnViewBackups = document.getElementById("btnViewBackups");

    if (btnViewBackups && modalBackup) {
        btnViewBackups.onclick = () => modalBackup.showModal();
    }

    // Close modal buttons
    document.querySelectorAll(".close-modal").forEach(btn => {
        btn.addEventListener("click", function(e) {
            const dialog = e.target.closest("dialog");
            if (dialog) dialog.close();
        });
    });

    // Create Backup
    const createBackupBtn = document.getElementById("createBackupBtn");
    if (createBackupBtn) {
        createBackupBtn.onclick = function() {
            fetch("{{ route('admin.createBackup') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.refreshBackupCards) {
                        refreshBackupCards();
                    }
                    const toast = document.getElementById("backupSuccessToast");
                    if (toast) {
                        toast.classList.add("show");
                        setTimeout(() => toast.classList.remove("show"), 2500);
                    }
                }
            });
        };
    }

    // Auto-open archive modal if needed
    @if(session('keepArchiveOpen'))
        if (modalArchive) modalArchive.showModal();
    @endif

    // Feedback chart
    const feedbackCtx = document.getElementById("feedbackBarChart");
    if (feedbackCtx) {
        fetch("/feedback-data")
            .then(response => response.json())
            .then(chartData => {
                new Chart(feedbackCtx, {
                    type: "bar",
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: "Number of Ratings",
                            data: chartData.data,
                            borderWidth: 1,
                            backgroundColor: [
                                "#f72585",
                                "#ff9e01", 
                                "#4cc9f0",
                                "#4895ef",
                                "#550b92"
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: "Feedback Ratings Distribution",
                                font: { size: 16 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Error loading feedback data:", error));
    }

    // Auto-refresh data every 60 seconds
    setInterval(function() {
        console.log("Data refresh triggered");
    }, 60000);
});
// Filter functionality for archive modal
document.addEventListener('DOMContentLoaded', function() {
    const backupFilter = document.getElementById('backupFilter');
    
    // Filter backups when dropdown changes
    if (backupFilter) {
        backupFilter.addEventListener('change', function() {
            const filterValue = this.value;
            filterBackupCards(filterValue);
        });
    }

    // Function to filter backup cards
    function filterBackupCards(filterValue) {
        const backupCards = document.querySelectorAll('.backup-card');
        
        backupCards.forEach(card => {
            const fileName = card.querySelector('.backup-name').textContent.toLowerCase();
            
            if (filterValue === 'all') {
                card.style.display = 'flex';
            } else if (fileName.includes(filterValue)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Show empty message if no cards are visible
        const visibleCards = document.querySelectorAll('.backup-card[style="display: flex"]');
        const emptyMessage = document.querySelector('.backup-empty-message');
        
        if (visibleCards.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        } else if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }

    // Re-attach filter event listener after backup refresh
    window.refreshBackupCards = function () {
        fetch('/admin/backups/refresh')
            .then(res => res.json())
            .then(data => {
                document.getElementById('backupCardsContainer').outerHTML = data.html;
                // Re-attach filter event listener after refresh
                const newFilter = document.getElementById('backupFilter');
                if (newFilter) {
                    newFilter.addEventListener('change', function() {
                        const filterValue = this.value;
                        filterBackupCards(filterValue);
                    });
                }
            })
            .catch(err => console.log(err));
    };
});
</script>
</body>
</html>