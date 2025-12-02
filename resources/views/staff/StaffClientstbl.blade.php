<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/staff/StaffClientstbl.blade.css') }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Client Management - LegalConnect</title>
    
</head>
<body>
        <div class="container">
        <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">
            <div>
                <div class="logo-container">
                    <img src="{{ asset('KG2025 (2).png') }}" alt="LegalConnect logo" width="80" height="80"/>
                    <p>LegalConnect</p>
                </div>
                <nav>
                    <a href="{{ route('dashboardStaff') }}" class="not-active" tabindex="0">Dashboard</a>
                    <a href="{{ route('staff') }}" class="not-active" tabindex="0">Set Appointment</a>
                    <a href="{{ url('/StaffClientstbl') }}" class="active" tabindex="0">Clients</a>
                    <a href="{{ url('/staffAcceptedRequest') }}" class="not-active" tabindex="0">Accepted Request</a>
                    <a href="{{ route('staff.deniedRequests') }}" class="not-active">Denied Requests</a>
                    <a href="{{ url('/staffAccount') }}" class="not-active"  tabindex="0">Account</a>
                </nav>
            </div>
        </aside>
        
        <main>
            @if(session('success'))
                <div id="flashModal" class="flash-modal">
                    <div class="flash-modal-content">
                        <span class="flash-close" onclick="document.getElementById('flashModal').style.display='none'">&times;</span>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div id="infoModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="document.getElementById('infoModal').style.display='none'">&times;</span>

                    <div class="modal-left">
                        <h3>Client Information</h3>
                        <form id="updateForm" method="POST" action="/appointments/update/ID">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" id="appointment_id" name="id">

                            <label for="fullname">Fullname:</label>
                            <input type="text" name="fullname" id="fullname" required>

                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required>

                            <label for="address">Address:</label>
                            <input type="text" name="address" id="address" required>

                            <label for="phone">Phone:</label>
                            <input type="text" name="phone" id="phone" required>

                            <label for="consulting">Consulting:</label>
                            <input type="text" name="consulting" id="consulting" required>

                            <label for="selected_date">Date:</label>
                            <input type="text" name="selected_date" id="selected_date" required>

                            <label for="selected_time">Time:</label>
                            <input type="text" name="selected_time" id="selected_time" required>

                            <label for="appointment_approval">Approval Status:</label>
                            <input type="text" name="appointment_approval" id="appointment_approval" required>
                        </form>
                    </div>

                    <div class="modal-right">
                        <h4>ID Photos</h4>
                        <div id="id_front_container">
                            <div class="image-placeholder" id="front_placeholder">Front ID Image</div>
                            <img id="id_front_preview" src="#" alt="Front ID" style="display: none;">
                        </div>
                        <div id="id_back_container">
                            <div class="image-placeholder" id="back_placeholder">Back ID Image</div>
                            <img id="id_back_preview" src="#" alt="Back ID" style="display: none;">
                        </div>
                        <div id="imageError" style="display: none; color: red; text-align: center;">
                            Image not available
                        </div>
                    </div>
                </div>
            </div>

            <nav class="top-bar" role="banner">
                <div class="nav-logo">
                    <img src="{{ asset('KG2025 (2).png') }}" alt="Legal Connect Logo">
                </div>

                <div class="burger-menu">
                    <button type="button" id="burgerBtn" class="burger-btn" aria-label="Open sidebar">
                        <div class="text-btn">☰ Menu</div>
                    </button>
                </div>
                
                <div class="top-bar-spacer"></div>

                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <div class="page-title">
            </div>
            
            <section class="table-wrapper">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fullname</th>
                                <th>Address</th>
                                <th>Consulting</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->id }}</td>
                                    <td>{{ $appointment->fullname }}</td>
                                    <td>{{ $appointment->address }}</td>
                                    <td>{{ $appointment->consulting }}</td>
                                    <td>
                                        @if($appointment->appointment_approval === 'pending')
                                            <span class="status-badge status-pending">Pending</span>
                                        @elseif($appointment->appointment_approval === 'approved')
                                            <span class="status-badge status-approved">Approved</span>
                                        @elseif($appointment->appointment_approval === 'denied')
                                            <span class="status-badge status-denied">Denied</span>
                                        @else
                                            {{ $appointment->appointment_approval }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Approve Button -->
                                            <form action="{{ route('appointments.approve', $appointment->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="info-btn" type="submit" title="Approve">
                                                    <i class="fas fa-check-circle"></i> APPROVE
                                                </button>
                                            </form>

                                            <!-- Deny Button -->
                                            <form action="{{ route('appointments.deny', $appointment->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="deny-btn" type="submit" title="Deny" onclick="return confirm('Are you sure you want to deny this appointment?')">
                                                    DENY
                                                </button>
                                            </form>

                                            <!-- View Button -->
                                            <button class="info-btn view-btn" title="See Info" data-id="{{ $appointment->id }}">
                                                <i class="fas fa-eye"></i> VIEW INFORMATION
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <script src="{{ asset('js/staff/StaffClientstbl.js') }}"></script>
</body>
</html>