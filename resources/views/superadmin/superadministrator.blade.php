<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Administrator</title>

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
                        <h1>Administrator Overview</h1>
                        <p>Monitor user access and recent login activity across the LegalConnect platform.</p>
                    </div>

                    <div class="stats-container">
                        <div class="stat-card card-total">
                            <div class="stat-header">
                                <div class="stat-title">Total Users</div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="stat-value">8</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-chart-line"></i> Platform Accounts
                                </span>
                            </div>
                        </div>

                        <div class="stat-card card-lawyers">
                            <div class="stat-header">
                                <div class="stat-title">Total Lawyers</div>
                                <div class="stat-icon">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>
                            </div>
                            <div class="stat-value">2</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-briefcase"></i> Active Legal Profiles
                                </span>
                            </div>
                        </div>

                        <div class="stat-card card-offices">
                            <div class="stat-header">
                                <div class="stat-title">Total Law Offices</div>
                                <div class="stat-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                            <div class="stat-value">4</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-map-marker-alt"></i> Registered Offices
                                </span>
                            </div>
                        </div>

                        <div class="stat-card card-clients">
                            <div class="stat-header">
                                <div class="stat-title">Total Client Users</div>
                                <div class="stat-icon">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                            </div>
                            <div class="stat-value">4</div>
                            <div class="stat-footer">
                                <span class="stat-trend">
                                    <i class="fas fa-user-check"></i> Verified Client Accounts
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogoutModal() {
            const modalElement = document.getElementById('logoutConfirmationModal');

            if (!modalElement) {
                return;
            }

            const logoutModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            logoutModal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper');
            const menuToggle = document.getElementById('menu-toggle');

            if (!wrapper || !menuToggle) {
                return;
            }

            menuToggle.addEventListener('click', function () {
                wrapper.classList.toggle('toggled');
            });
        });
    </script>
</body>
</html>
