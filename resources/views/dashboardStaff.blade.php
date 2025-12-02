<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/staff/dashboardStaff.blade.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <div class="container">
        <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">
            <div>
                <div class="logo-container">
                    <img src="KG2025 (2).png" alt="LegalConnect logo" width="80" height="80"/>
                    <p>LegalConnect</p>
                </div>
                <nav>
                   <a href="{{ route('dashboardStaff') }}" class="active" tabindex="0">Dashboard</a>
                    <a href="{{ route('staff') }}" class="not-active" tabindex="0">Set Appointment</a>
                    <a href="{{ url('/StaffClientstbl') }}" class="not-active" tabindex="0">Clients</a>
                    <a href="{{ url('/staffAcceptedRequest') }}" class="not-active" tabindex="0">Accepted Request</a>
                    <a href="{{ route('staff.deniedRequests') }}" class="not-active">Denied Requests</a>
                    <a href="{{ url('/staffAccount') }}" class="not-active"  tabindex="0">Account</a>
                </nav>
            </div>
        </aside>

        <main>
            <nav class="top-bar" role="banner">
                <div class="nav-logo">
                    <img src="{{ asset('KG2025 (2).png') }}" alt="Legal Connect Logo">
                </div>

                <div class="burger-menu">
                    <!-- Burger Button -->
                    <button type="button" id="burgerBtn" class="burger-btn" aria-label="Open sidebar">
                        <div class="text-btn">☰ Menu</div>
                    </button>
                </div>
                <!-- Spacer to push logout to the right -->
                <div class="top-bar-spacer"></div>

                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <div class="dashboard-container">
                <h1 class="dashboard-title">Appointment Analytics Dashboard</h1>
                
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
                    <div class="stat-card card-pending clickable-card" onclick="window.location.href='{{ route('staff.clients.pending') }}'">
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
                    <div class="stat-card card-approved clickable-card" onclick="window.location.href='{{ route('staff.acceptedRequests') }}'">
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
                    <div class="stat-card card-denied clickable-card" onclick="window.location.href='{{ route('staff.deniedRequests') }}'">
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
                </div>
                
                <!-- Recent Activity Section -->
                <div class="recent-activity">
                    <h2 class="activity-title">Recent Appointment Requests</h2>
                    <ul class="activity-list">
                        @foreach($recentAppointments as $appointment)
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-name">{{ $appointment->fullname }}</div>
                                <div class="activity-time">{{ $appointment->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <span class="activity-status status-{{ $appointment->appointment_approval }}">
                                {{ ucfirst($appointment->appointment_approval) }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </main>
    </div>

 <script src="{{ asset('js/staff/dashboardStaff.js') }}"></script>
  <script>
    console.log("Current route: {{ Request::path() }}");
    // Check if there are any dynamic elements that might be trying to use dashboardStaff.page
    document.addEventListener('DOMContentLoaded', function() {
        const allElements = document.querySelectorAll('*');
        allElements.forEach(el => {
            if (el.outerHTML.includes('dashboardStaff.page')) {
                console.log('Found reference to dashboardStaff.page in:', el);
            }
        });
    });
</script>
</body>
</html>