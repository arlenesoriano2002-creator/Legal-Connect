<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Administrator | Law Offices</title>
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
                        <h1>Law Offices</h1>
                        <p>Manage law office records from the dedicated law_offices table.</p>
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
                            <input type="search" id="officeSearch" placeholder="Search law offices by office, lawyer, or address">
                        </div>
                        <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#createLawOfficeModal">
                            <i class="fas fa-plus"></i> Create Law Office
                        </button>
                    </div>

                    <section class="office-grid">
                        @forelse ($offices as $office)
                            <article class="office-card office-item" data-search="{{ strtolower(trim(($office->law_office ?? '') . ' ' . ($office->lawyer ?? '') . ' ' . ($office->address ?? ''))) }}">
                                <h3>{{ $office->law_office }}</h3>
                                <p>{{ $office->address }}</p>
                                <p class="mt-2"><strong>Lawyer:</strong> System Generated</p>
                                <div class="office-card-actions">
                                    <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#editOfficeModal{{ $office->id }}">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <button class="action-btn action-btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteOfficeModal{{ $office->id }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="office-card">
                                <h3>No law offices found</h3>
                                <p>The law_offices table does not have any records yet.</p>
                            </div>
                        @endforelse
                    </section>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="createLawOfficeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title"><i class="fas fa-building-circle-check me-2"></i>Create Law Office</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="content-modal">
                    <p class="modal-description">Create a new law office record in the law_offices table.</p>
                    <form class="modal-form-grid" method="POST" action="{{ route('superadmin.lawoffices.store') }}">
                        @csrf
                        <input type="hidden" name="form_mode" value="create">
                        <div>
                            <label>Lawyer</label>
                            <span>System Generated</span>
                            <input type="hidden" name="lawyer" value="Atty. Lawyer {{ count($offices) + 1 }}">
                        </div>
                        <div>
                            <label for="createOfficeAddress">Address</label>
                            <textarea id="createOfficeAddress" name="address" required>{{ old('address') }}</textarea>
                        </div>
                        <div>
                            <label for="createOfficeName">Law Office</label>
                            <input id="createOfficeName" name="law_office" type="text" value="{{ old('law_office') }}" required>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Law Office</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @foreach ($offices as $office)
        <div class="modal fade" id="editOfficeModal{{ $office->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Law Office</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Update the current law office record for {{ $office->law_office }}.</p>
                        <form class="modal-form-grid" method="POST" action="{{ route('superadmin.lawoffices.update', $office) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="form_mode" value="edit">
                            <div>
                                <label>Lawyer</label>
                                <span>{{ $office->lawyer }}</span>
                                <input type="hidden" name="lawyer" value="{{ $office->lawyer }}">
                            </div>
                            <div>
                                <label for="officeAddress{{ $office->id }}">Address</label>
                                <textarea id="officeAddress{{ $office->id }}" name="address" required>{{ $office->address }}</textarea>
                            </div>
                            <div>
                                <label for="officeName{{ $office->id }}">Law Office</label>
                                <input id="officeName{{ $office->id }}" name="law_office" type="text" value="{{ $office->law_office }}" required>
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

        <div class="modal fade" id="deleteOfficeModal{{ $office->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Law Office</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   <center>
                     <div class="content-modal">
                        <div class="logout-warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h4 class="mb-3">Confirm Deletion</h4>
                        <p>Are you sure you want to delete <strong>{{ $office->law_office }}</strong> assigned to {{ $office->lawyer }}?</p>
                    </div>
                   </center>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="{{ route('superadmin.lawoffices.destroy', $office) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i>Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <center>
                    <div class="content-modal">
                        <div class="logout-warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
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
            const officeSearch = document.getElementById('officeSearch');
            const officeItems = document.querySelectorAll('.office-item');

            if (wrapper && menuToggle) {
                menuToggle.addEventListener('click', function () {
                    wrapper.classList.toggle('toggled');
                });
            }

            if (officeSearch) {
                officeSearch.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    officeItems.forEach(function (item) {
                        const haystack = item.dataset.search || item.textContent.toLowerCase();
                        item.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            @if ($errors->any() && old('form_mode') === 'create')
                const createLawOfficeModal = document.getElementById('createLawOfficeModal');
                if (createLawOfficeModal) {
                    bootstrap.Modal.getOrCreateInstance(createLawOfficeModal).show();
                }
            @endif
        });
    </script>
</body>
</html>
