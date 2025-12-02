<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/staff/staff.blade.css') }}">
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
                   <a href="{{ route('dashboardStaff') }}" class="not-active" tabindex="0">Dashboard</a>
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
            <div class="page-title">

            </div>
                            <!-- Edit Modal -->
                <div id="editModal" class="overlay-model" style="display:none;">
                <div class="box-modal">
                    <h2>Edit Appointment Slot</h2>
                    <form id="editSlotForm">
                    <input type="hidden" id="editSlotId" name="id">
                    <input type="hidden" id="editSlotDate" name="date">

                    <div id="editTimeSlots" style="margin:15px 0;"></div>

                    <div id="editMessageBox" style="margin:10px 0; font-size:14px;"></div>

                    <!-- Button wrapper aligned right -->
                    <div style="text-align:right; margin-top:15px;">
                        <button type="button" id="closeEditModal" class="cancel-btn">
                        Cancel
                        </button>
                        <button type="submit" class="submit-btn">
                        Save Changes
                        </button>
                    </div>
                    </form>
                </div>
                </div>


                <div>
                    <div class="table-container"><!---ONLY THIS DIV YOU WILL EDIT AND MAKE USE YOU WILL BASE THE IMAGE TO CHange the design-->
                    <div class="modal-overlay">
                        <div class="modal-box">
                        <!-- Calendar -->
                        <div class="calendar-wrapper">
                            <div class="calendar">
                            <div class="calendar-header">
                                <button type="button" class="next-btn" id="prevMonthBtn">&lt;</button>
                                <div class="month-year" id="monthYearLabel">March 2024</div>
                                <button type="button" class="next-btn" id="nextMonthBtn">&gt;</button>
                            </div>
                            <div class="weekdays">
                                <div>Sun</div><div>Mon</div><div>Tue</div>
                                <div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                            </div>
                            <div class="dates-grid" id="calendarGrid"></div>
                            <div class="calendar-footer">
                                <input type="text" id="monthYearInput" placeholder="mm/yyyy"/>
                                <button type="button" id="goToMonthBtn">Go</button>
                                <button type="button" id="todayBtn">Today</button>
                            </div>
                            </div>
                        </div>

                        <!-- Time slots -->
                        <section class="time-section">
                            <div class="time-header">
                            <h2>Choose Available Times</h2>
                            </div>
                            <div id="messageContainer"></div>
                            <div class="time-slots" id="timeSlots"></div>
                            <div style="margin-top:20px; text-align:center;">
                            <button id="submitAvailabilityBtn" 
                                style="padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:5px;">
                                Submit Selected Times
                            </button>
                            </div>
                        </section>
                        
                                </section>
                                <!-----<aside class="color-indicator" aria-label="Color indicator legend">
                                <h3>Color Indicator</h3>
                                <div class="color-row">
                                    <div class="color-circle color-red" aria-hidden="true"></div>
                                    <span>Not Available</span>
                                </div>
                                <div class="color-row">
                                    <div class="color-circle color-yellow" aria-hidden="true"></div>
                                    <span>Holiday</span>
                                </div>
                                <div class="color-row">
                                    <div class="color-circle color-blue" aria-hidden="true"></div>
                                    <span>Available</span>
                                </div>
                                </aside>-->
                            </div>
                        </div>
                        <table class="admin-table">
                        <thead>
                            <tr>
                            <th class="column-date">Date</th>
                            <th class="column-time">Time</th>
                            <th class="column-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="slots-table-body">
                        
                
                            <td>
                                <!-- Use this buttons to edit and delete the data inside the table -->
                                <button class="edit-btn" aria-label="">
                                <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="edit-btn" aria-label="">
                                <i class="fas fa-edit"></i> Delete
                                </button>
                            </td>
                            </tr>
                            
                        </tbody>
                        </table>
                    </div>
                </div>
                </main>
            </div>
            
        </main>
    </div>

  <script>
    // Prevent back and forward navigation after logout or sensitive actions
    function preventNavigation() {
        // Push multiple states to prevent both back and forward navigation
        history.pushState(null, null, location.href);
        history.pushState(null, null, location.href);
        history.pushState(null, null, location.href);

        // Handle any navigation attempt (back or forward)
        window.onpopstate = function(event) {
            // Push state again to prevent navigation
            history.pushState(null, null, location.href);
            // Show alert for any navigation attempt
            alert('Navigation is disabled for security reasons.');
        };
    }

    // Disable browser navigation buttons and shortcuts
    function disableBrowserButtons() {
        // Disable back button
        history.pushState(null, null, location.href);
        // Disable forward button by manipulating history
        history.replaceState(null, null, location.href);

        // Prevent context menu back/forward
        document.addEventListener('contextmenu', function(e) {
            // Allow context menu but prevent back/forward actions
            setTimeout(() => {
                history.pushState(null, null, location.href);
            }, 0);
        });

        // Prevent keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Prevent Alt+Left (back), Alt+Right (forward), Ctrl+Left, Ctrl+Right
            if ((e.altKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) ||
                (e.ctrlKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight'))) {
                e.preventDefault();
                alert('Navigation is disabled for security reasons.');
                return false;
            }
        });
    }

    // Call functions to disable navigation
    document.addEventListener('DOMContentLoaded', function() {
        preventNavigation();
        disableBrowserButtons();
    });
  </script>
  <script src="{{ asset('js/staff/staff.js') }}"></script>

</body>
</html>
