<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Account</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/admindashboard.blade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminAccount.blade.css') }}">
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
                <button class="toggle-btn" id="menu-toggle">
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
            
            <div class="dashboard-container">
                <!-- Header Section -->
                <div class="mb-4">
                    <h1 class="text-3xl font-bold text-gray-800">Account Management</h1>
                    <p class="text-gray-600 mt-2">Manage your profile and all staff accounts</p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Left Side - Admin Profile -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">Your Profile</h5>
                            </div>
                            <div class="card-body text-center">
                                @if (isset($user))
                                    <div class="profile-image mb-3">
                                        <img src="{{ asset($user->image ?: 'uploads/default-avatar.png') }}" 
                                             alt="Admin Image" 
                                             class="rounded-circle" 
                                             width="150" 
                                             height="150"
                                             style="object-fit: cover;">
                                    </div>
                                    <h4>{{ $user->username }}</h4>
                                    <p class="text-muted"><strong>Role:</strong> {{ $user->role }}</p>
                                    <p class="text-muted"><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
                                    <p class="text-muted"><strong>Contact:</strong> {{ $user->cp_number ?? 'N/A' }}</p>
                                    
                                    <button onclick="openAdminModal()" class="btn btn-primary mt-3">
                                        <i class="fas fa-edit"></i> Edit Profile
                                    </button>
                                @else
                                    <div class="alert alert-danger">
                                        User profile could not be loaded. Please try logging in again.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Staff Users Table -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Staff Accounts</h5>
                                <div class="d-flex gap-2">
                                    <!-- Search Form -->
                                    <form action="{{ route('adminAccount.search') }}" method="GET" class="d-flex">
                                        <input type="text" name="search" class="form-control form-control-sm" 
                                               placeholder="Search staff..." value="{{ request('search') }}">
                                        <button type="submit" class="btn btn-light btn-sm ms-2">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                    <!-- Create Button -->
                                    <button onclick="openCreateStaffModal()" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Create
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($staffUsers as $staff)
                                                <tr>
                                                    <td>
                                                        <img src="{{ asset($staff->image ?: 'uploads/default-avatar.png') }}" 
                                                             alt="Staff Image" 
                                                             class="rounded-circle" 
                                                             width="40" 
                                                             height="40"
                                                             style="object-fit: cover;">
                                                    </td>
                                                    <td>{{ $staff->name }}</td>
                                                    <td>{{ $staff->email }}</td>
                                                    <td>{{ $staff->cp_number ?? 'N/A' }}</td>
                                                    <td>{{ $staff->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <button onclick="openEditStaffModal({{ $staff }})" 
                                                                class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button onclick="confirmDelete({{ $staff->id }})" 
                                                                class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No staff users found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Edit Modal -->
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <h3>Edit Profile</h3>
            <form action="{{ route('adminAccount.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $user->id ?? '' }}">

                <div class="mb-3">
                    <label for="image" class="form-label">Profile Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" name="username" value="{{ $user->username ?? '' }}" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" value="{{ $user->email ?? '' }}" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="cp_number" class="form-label">Contact Number:</label>
                    <input type="text" name="cp_number" value="{{ $user->cp_number ?? '' }}" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role:</label>
                    <input type="text" name="role" value="{{ $user->role ?? '' }}" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" placeholder="Enter new password (optional)" class="form-control">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" onclick="closeAdminModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Staff Modal -->
    <div id="createStaffModal" class="modal">
        <div class="modal-content">
            <h3>Create Staff User</h3>
            <form action="{{ route('adminAccount.staff.create') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-3">
                    <label for="staff_image" class="form-label">Profile Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" name="name" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="cp_number" class="form-label">Contact Number:</label>
                    <input type="text" name="cp_number" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" required class="form-control">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Create Staff</button>
                    <button type="button" onclick="closeCreateStaffModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="modal">
        <div class="modal-content">
            <h3>Edit Staff User</h3>
            <form id="editStaffForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="edit_staff_image" class="form-label">Profile Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="edit_name" class="form-label">Name:</label>
                    <input type="text" name="name" id="edit_name" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="edit_email" class="form-label">Email:</label>
                    <input type="email" name="email" id="edit_email" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="edit_cp_number" class="form-label">Contact Number:</label>
                    <input type="text" name="cp_number" id="edit_cp_number" required class="form-control">
                </div>

                <div class="mb-3">
                    <label for="edit_password" class="form-label">Password:</label>
                    <input type="password" name="password" id="edit_password" placeholder="Leave blank to keep current" class="form-control">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Update Staff</button>
                    <button type="button" onclick="closeEditStaffModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this staff user?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.getElementById('wrapper').classList.toggle('toggled');
                });
            }
        });

        // Modal functions for Admin
        function openAdminModal() {
            document.getElementById('adminModal').style.display = 'block';
        }

        function closeAdminModal() {
            document.getElementById('adminModal').style.display = 'none';
        }

        // Modal functions for Create Staff
        function openCreateStaffModal() {
            document.getElementById('createStaffModal').style.display = 'block';
        }

        function closeCreateStaffModal() {
            document.getElementById('createStaffModal').style.display = 'none';
        }

        // Modal functions for Edit Staff
        function openEditStaffModal(staff) {
            document.getElementById('editStaffModal').style.display = 'block';
            document.getElementById('edit_name').value = staff.name;
            document.getElementById('edit_email').value = staff.email;
            document.getElementById('edit_cp_number').value = staff.cp_number || '';
            
            // Set form action
            const form = document.getElementById('editStaffForm');
            form.action = '{{ url("adminAccount/staff/update") }}/' + staff.id;
        }

        function closeEditStaffModal() {
            document.getElementById('editStaffModal').style.display = 'none';
        }

        // Modal functions for Delete Confirmation
        function confirmDelete(staffId) {
            document.getElementById('deleteModal').style.display = 'block';
            const form = document.getElementById('deleteForm');
            form.action = '{{ url("adminAccount/staff/delete") }}/' + staffId;
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['adminModal', 'createStaffModal', 'editStaffModal', 'deleteModal'];
            modals.forEach(modalId => {
                let modal = document.getElementById(modalId);
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            });
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>