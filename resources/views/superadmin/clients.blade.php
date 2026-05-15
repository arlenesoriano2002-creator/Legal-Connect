<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Administrator | Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/superadmin.css') }}">
</head>
<body>
    <div id="wrapper">
        @include('layouts.superadmin-sidebar')

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
                        <h1>Clients Directory</h1>
                        <p>Manage client accounts directly from the users table.</p>
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
                            <input type="search" id="clientSearch" placeholder="Search clients by name, address, or contact info">
                        </div>
                    </div>

                    <section class="entity-panel">
                        <div class="entity-table-wrapper">
                            <table class="entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact Info</th>
                                        <th>Law Office</th> <!-- ✅ ADDED -->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($clients as $client)
                                        <tr class="client-row" data-search="{{ strtolower(trim(($client->name ?? '') . ' ' . ($client->address ?? '') . ' ' . ($client->email ?? '') . ' ' . ($client->cp_number ?? '') . ' ' . ($client->law_office ?? ''))) }}">
                                            <td data-label="Name">{{ $client->name ?? 'N/A' }}</td>
                                            <td data-label="Address">{{ $client->address ?? 'N/A' }}</td>
                                            <td data-label="Contact Info">
                                                <div class="contact-meta">
                                                    <span>{{ $client->email ?? 'N/A' }}</span>
                                                    <span>{{ $client->cp_number ?? 'N/A' }}</span>
                                                </div>
                                            </td>

                                            <!-- ✅ ADDED -->
                                            <td data-label="Law Office">
                                                {{ !empty($client->law_office) ? $client->law_office : 'N/A' }}
                                            </td>

                                            <td data-label="Actions">
                                                <div class="action-buttons">
                                                    <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                                        <i class="fas fa-pen"></i> Edit
                                                    </button>
                                                    <button class="action-btn action-btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteClientModal{{ $client->id }}">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No client records found in the users table.</td>
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

    @foreach ($clients as $client)
        <div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Update the current client profile for {{ $client->name ?? 'this client' }}.</p>
                        <form class="modal-form-grid" method="POST" action="{{ route('superadmin.clients.update', $client) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_mode" value="edit">
                            <div>
                                <label for="clientName{{ $client->id }}">Name</label>
                                <input id="clientName{{ $client->id }}" name="name" type="text" value="{{ $client->name ?? '' }}" required>
                            </div>
                            <div>
                                <label for="clientAddress{{ $client->id }}">Address</label>
                                <textarea id="clientAddress{{ $client->id }}" name="address" required>{{ $client->address ?? '' }}</textarea>
                            </div>
                            <div>
                                <label for="clientContact{{ $client->id }}">Contact Number</label>
                                <input id="clientContact{{ $client->id }}" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="{{ $client->cp_number ?? '' }}" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div>
                                <label for="clientEmail{{ $client->id }}">Email</label>
                                <input id="clientEmail{{ $client->id }}" name="email" type="email" value="{{ $client->email ?? '' }}" required>
                            </div>
                            <div>
                                <label for="clientPassword{{ $client->id }}">Password</label>
                                <input id="clientPassword{{ $client->id }}" name="password" type="password" value="" placeholder="Leave blank to keep current password">
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

        <div class="modal fade" id="deleteClientModal{{ $client->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   <center>
                     <div class="content-modal">
                        <div class="logout-warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h4 class="mb-3">Confirm Deletion</h4>
                        <p>Are you sure you want to delete {{ $client->name ?? 'this client' }} from the users table?</p>
                    </div>
                   </center>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="{{ route('superadmin.clients.destroy', $client) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2"></i>Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <center>
                    <div class="content-modal">
                        <div class="logout-warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h4>Confirm Logout</h4>
                        <p>Are you sure you want to log out?</p>
                    </div>
                </center>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" onclick="document.getElementById('logout-form').submit();">Log Out</button>
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
            const clientSearch = document.getElementById('clientSearch');
            const clientRows = document.querySelectorAll('.client-row');

            if (wrapper && menuToggle) {
                menuToggle.addEventListener('click', function () {
                    wrapper.classList.toggle('toggled');
                });
            }

            if (clientSearch) {
                clientSearch.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    clientRows.forEach(function (row) {
                        const haystack = row.dataset.search || row.textContent.toLowerCase();
                        row.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>