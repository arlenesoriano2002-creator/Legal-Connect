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
    
    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
    
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
                   <a href="{{ route('dashboardStaff') }}" class="not-active" tabindex="0">Dashboard</a>
                    <a href="{{ route('staff') }}" class="not-active" tabindex="0">Set Appointment</a>
                    <a href="{{ url('/StaffClientstbl') }}" class="not-active" tabindex="0">Clients</a>
                    <a href="{{ url('/staffAcceptedRequest') }}" class="not-active" tabindex="0">Accepted Request</a>
                    <a href="{{ route('staff.deniedRequests') }}" class="not-active">Denied Requests</a>
                    <a href="{{ url('/staffAccount') }}" class="active"  tabindex="0">Account</a>
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
                
            </div>
        </main>
    </div>

 <script src="{{ asset('js/staff/dashboardStaff.js') }}"></script>
<script src="{{ asset('js/staff/diffunNotifications.js') }}"></script>
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