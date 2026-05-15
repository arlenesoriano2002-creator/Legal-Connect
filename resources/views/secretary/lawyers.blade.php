<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Secretary | Lawyers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/superadmin.css') }}">
</head>
<body>
    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="head-content">
                    <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40">
                    <span>LegalConnect</span>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('dashboardStaff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboardStaff') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('secretary.lawyers') }}" class="list-group-item list-group-item-action {{ request()->routeIs('secretary.lawyers') ? 'active' : '' }}">
                    <i class="fas fa-scale-balanced"></i>
                    <span>Lawyers</span>
                </a>
                
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle" type="button" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-title">Administrator Dashboard</div>
                <div class="top-bar-spacer"></div>
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <main class="superadmin-content">
                <div class="dashboard-container">
                    <div class="dashboard-header">
                        <h1>Lawyers Directory</h1>
                        <p>Manage lawyer records and assigned law offices from the users table.</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please review the form fields.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="page-toolbar">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="search" id="lawyerSearch" placeholder="Search lawyers by name, office, or contact info">
                        </div>
                        <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#createLawyerModal">
                            <i class="fas fa-plus"></i> Create Lawyer
                        </button>
                    </div>

                    <section class="entity-panel">
                        <div class="entity-table-wrapper">
                            <table class="entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact Info</th>
                                        <th>Law Office</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($lawyers as $lawyer)
                                        <tr class="lawyer-row" data-search="{{ strtolower(trim(($lawyer->name ?? '') . ' ' . ($lawyer->address ?? '') . ' ' . ($lawyer->username ?? '') . ' ' . ($lawyer->email ?? '') . ' ' . ($lawyer->cp_number ?? '') . ' ' . ($lawyer->law_office ?? ''))) }}">
                                            <td data-label="Name">{{ $lawyer->name ?? 'N/A' }}</td>
                                            <td data-label="Address">{{ $lawyer->address ?? 'N/A' }}</td>
                                            <td data-label="Contact Info">
                                                <div class="contact-meta">
                                                    <span>{{ $lawyer->email ?? 'N/A' }}</span>
                                                    <span>{{ $lawyer->cp_number ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td data-label="Law Office">{{ $lawyer->lawOffice->law_office ?? $lawyer->law_office ?? 'N/A' }}</td>
                                            <td data-label="Actions">
                                                <div class="action-buttons">
                                                    <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#editLawyerModal{{ $lawyer->id }}">
                                                        <i class="fas fa-pen"></i> Edit
                                                    </button>
                                                    <button class="action-btn action-btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteLawyerModal{{ $lawyer->id }}">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No lawyers found in the users table.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="createLawyerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Create Lawyer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="content-modal">
                    <p class="modal-description">Create a new lawyer account and store it in the users table.</p>
                    <form class="modal-form-grid" method="POST" action="{{ route('secretary.lawyers.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">
                        <div>
                            <label for="createLawyerName">Name</label>
                            <input id="createLawyerName" name="name" type="text" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label for="createLawyerAddress">Address</label>
                            <textarea id="createLawyerAddress" name="address" required>{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label for="createLawyerUsername">Username</label>
                            <input id="createLawyerUsername" name="username" type="text" value="{{ old('username') }}" required>
                        </div>
                        <div>
                            <label for="createLawyerPhone">Contact Number</label>
                            <input id="createLawyerPhone" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="{{ old('cp_number') }}" placeholder="09XXXXXXXXX" required>
                        </div>
                        <div>
                            <label for="createLawyerEmail">Email</label>
                            <input id="createLawyerEmail" name="email" type="email" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label for="createLawyerPassword">Password</label>
                            <input id="createLawyerPassword" name="password" type="password" required>
                        </div>
                        <div>
                            <label for="createLawyerImage">Image</label>
                            <input id="createLawyerImage" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Lawyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach ($lawyers as $lawyer)
        <div class="modal fade" id="editLawyerModal{{ $lawyer->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Lawyer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Update the current lawyer profile information for {{ $lawyer->name ?? 'this lawyer' }}.</p>
                        <form class="modal-form-grid" method="POST" action="{{ route('secretary.lawyers.update', $lawyer) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_mode" value="edit">
                            <div>
                                <label for="lawyerName{{ $lawyer->id }}">Name</label>
                                <input id="lawyerName{{ $lawyer->id }}" name="name" type="text" value="{{ $lawyer->name ?? '' }}" required>
                            </div>
                            <div>
                                <label for="lawyerAddress{{ $lawyer->id }}">Address</label>
                                <textarea id="lawyerAddress{{ $lawyer->id }}" name="address" required>{{ $lawyer->address ?? '' }}</textarea>
                            </div>
                            <div>
                                <label for="lawyerUsername{{ $lawyer->id }}">Username</label>
                                <input id="lawyerUsername{{ $lawyer->id }}" name="username" type="text" value="{{ $lawyer->username ?? '' }}" required>
                            </div>
                            <div>
                                <label for="lawyerContact{{ $lawyer->id }}">Contact Info</label>
                                <input id="lawyerContact{{ $lawyer->id }}" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="{{ $lawyer->cp_number ?? '' }}" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div>
                                <label for="lawyerEmail{{ $lawyer->id }}">Email</label>
                                <input id="lawyerEmail{{ $lawyer->id }}" name="email" type="email" value="{{ $lawyer->email ?? '' }}" required>
                            </div>
                            <div>
                                <label for="lawyerPassword{{ $lawyer->id }}">Password</label>
                                <input id="lawyerPassword{{ $lawyer->id }}" name="password" type="password" value="" placeholder="Leave blank to keep current password">
                            </div>
                            <div>
                                <label for="lawyerImage{{ $lawyer->id }}">Image</label>
                                <input id="lawyerImage{{ $lawyer->id }}" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
                            </div>
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteLawyerModal{{ $lawyer->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Lawyer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <center>
                        <div class="content-modal">
                        <div class="logout-warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4 class="mb-3">Confirm Deletion</h4>
                        <p>Are you sure you want to delete {{ $lawyer->name ?? 'this lawyer' }} from the lawyers list?</p>
                    </div>
                    </center>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <center>
                    <div class="content-modal">
                        <div class="logout-warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4 class="mb-3">Confirm Logout</h4>
                        <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                    </div>
                </center>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-1"></i> Log Out</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogoutModal() {
            const modalElement = document.getElementById('logoutConfirmationModal');
            if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper');
            const menuToggle = document.getElementById('menu-toggle');
            const lawyerSearch = document.getElementById('lawyerSearch');
            const lawyerRows = document.querySelectorAll('.lawyer-row');

            if (wrapper && menuToggle) {
                menuToggle.addEventListener('click', function () {
                    wrapper.classList.toggle('toggled');
                });
            }

            if (lawyerSearch) {
                lawyerSearch.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    lawyerRows.forEach(function (row) {
                        const haystack = row.dataset.search || row.textContent.toLowerCase();
                        row.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            @if ($errors->any() && old('form_mode') === 'create')
                const createLawyerModal = document.getElementById('createLawyerModal');
                if (createLawyerModal) {
                    bootstrap.Modal.getOrCreateInstance(createLawyerModal).show();
                }
            @endif
        });
    </script>
</body>
</html>
