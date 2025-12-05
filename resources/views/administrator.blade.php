<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Calendar Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/administrator.blade.css') }}">

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
                <a href="{{ url('/email-chat') }}" class="list-group-item list-group-item-action {{ request()->is('email-chat') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Email Chat</span>
                </a>
                <a href="{{ url('/messages') }}" class="list-group-item list-group-item-action {{ request()->is('messages') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
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

                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <div class="calendar-container">
                <!-- Calendar Views Only (No Color Panel) -->
                <div class="calendar-views" style="flex: 1; min-width: 100%;">
                    <div class="view-tabs">
                        <div class="view-tab active" data-view="month">Month View</div>
                        <!--<div class="view-tab" data-view="week">Week View</div>-->
                    </div>
                    
                    <div class="view-content">
                        <!-- Month View -->
                        <div id="monthView" class="view-pane">
                            <div class="month-calendar">
                                <div class="calendar-header">
                                    <button class="nav-btn" id="prevMonth">&lt;</button>
                                    <h3 id="currentMonthYear">March 2024</h3>
                                    <button class="nav-btn" id="nextMonth">&gt;</button>
                                </div>
                                
                                <div class="weekdays">
                                    <div>Sun</div>
                                    <div>Mon</div>
                                    <div>Tue</div>
                                    <div>Wed</div>
                                    <div>Thu</div>
                                    <div>Fri</div>
                                    <div>Sat</div>
                                </div>
                                
                                <div class="days-grid" id="monthGrid"></div>
                            </div>
                        </div>
                        
                        <!-- Week View 
                        <div id="weekView" class="view-pane" style="display: none;">
                            <div class="week-calendar">
                                <div class="calendar-header">
                                    <button class="nav-btn" id="prevWeek">&lt;</button>
                                    <h3 id="currentWeekRange">Week of March 1-7, 2024</h3>
                                    <button class="nav-btn" id="nextWeek">&gt;</button>
                                </div>
                                
                                <div class="week-grid" id="weekGrid"></div>
                            </div>
                        </div>-->
                    </div>
                    
                    <div id="messageContainer"></div>
                </div>
            </div>
        </div>
    </div>

   <!-- Color Selection Modal -->
<div class="modal fade" id="colorSelectionModal" tabindex="-1" aria-labelledby="colorSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorSelectionModalLabel">
                    <i class="fas fa-calendar-day"></i> 
                    Calendar Settings for <span id="modalDateDisplay"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Date Color Selection Section -->
                <div class="modal-section">
                    <h6><i class="fas fa-palette"></i> Date Color Selection</h6>
                    <p class="text-muted small mb-3">Set the overall color for this entire date</p>
                    
                    <div class="d-flex gap-3 mb-3">
                        <div class="modal-color-option flex-fill" data-color="red">
                            <div class="time-slot-color-indicator color-red"></div>
                            <span style="margin-left: 10px;">Not Available</span>
                        </div>
                        <div class="modal-color-option flex-fill" data-color="orange">
                            <div class="time-slot-color-indicator color-orange"></div>
                            <span style="margin-left: 10px;">Holiday</span>
                        </div>
                        <div class="modal-color-option flex-fill" data-color="green">
                            <div class="time-slot-color-indicator color-green"></div>
                            <span style="margin-left: 10px;">Available</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="dateDescriptionInput" class="form-label">
                            <i class="fas fa-sticky-note"></i> Date Description
                        </label>
                        <textarea class="form-control" id="dateDescriptionInput" rows="2" 
                                  placeholder="Add description for the entire date (e.g., 'Public holiday', 'Office closed')"></textarea>
                    </div>
                </div>

                <!-- Time Color Selection Section -->
                <div class="modal-section">
                    <h6><i class="fas fa-clock"></i> Time Slot Management</h6>
                    <p class="text-muted small mb-3">
                        Click on time slots to select them, then choose a color. 
                        <span class="text-info">Double-click slots to quickly toggle colors.</span>
                    </p>
                    
                    <div class="d-flex gap-3 mb-3">
                        <div class="modal-color-option" data-color="red">
                            <div class="time-slot-color-indicator color-red"></div>
                            <span style="margin-left: 10px;">Not Available</span>
                        </div>
                        <div class="modal-color-option" data-color="green">
                            <div class="time-slot-color-indicator color-green"></div>
                            <span style="margin-left: 10px;">Available</span>
                        </div>
                    </div>
                    
                    <div class="time-slots-container">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time Slot</th>
                                        <th width="80">Slot #</th>
                                        <th>Description</th>
                                        <th width="80">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="timeSlotsTableBody">
                                    <!-- Time slots will be dynamically populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveModalChanges">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ asset('js/administrator.js') }}"></script>
</body>
</html>