<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Administrator | Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/superadmin.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f4f7fb;
            color: #172b4d;
        }
        .page-title {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        .page-subtitle {
            color: #52606d;
            margin-bottom: 1.75rem;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }
        .dashboard-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(15, 23, 42, 0.06);
            padding: 1.5rem;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .dashboard-card .card-title {
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #5e6c84;
            margin-bottom: 1rem;
        }
        .dashboard-card .card-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: #102a43;
        }
        .dashboard-card .card-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #5e6c84;
            margin-top: 1rem;
            font-size: 0.95rem;
        }
        .dashboard-card .card-meta i {
            color: #0069d9;
        }
        .dashboard-body {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .card-panel {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
            padding: 1.5rem;
        }
        .card-panel h3 {
            font-size: 1.15rem;
            margin-bottom: 1rem;
            color: #102a43;
        }
        .chart-card {
            display: flex;
            flex-direction: column;
            min-height: 360px;
        }
        .chart-container {
            position: relative;
            min-height: 260px;
            height: 260px;
            width: 100%;
            margin-bottom: 1rem;
        }
        .chart-container canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .legend-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #42526e;
        }
        .legend-badge {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-table th,
        .stats-table td {
            padding: 0.95rem 0.75rem;
            text-align: left;
            vertical-align: middle;
        }
        .stats-table thead {
            background: #f8fafc;
        }
        .stats-table th {
            color: #334e68;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .stats-table tbody tr:hover {
            background: #f8faff;
        }
        .stats-table td.status-chip {
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 600;
        }
        .status-approved { background: #d7f5e3; color: #1b6d3a; }
        .status-pending { background: #fff4d6; color: #8a5a00; }
        .status-rejected { background: #ffe2e2; color: #9d1f28; }
        @media (max-width: 1150px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, minmax(220px, 1fr));
            }
            .dashboard-body {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 700px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
        $totalLawyers = \App\Models\User::where('role', 'lawyer')->count();
        $totalClients = \App\Models\User::where('role', 'client')->count();
        $totalLawOffices = \App\Models\LawOffice::count();
        $totalAppointments = \App\Models\Appointment::count();
        $pendingAppointments = \App\Models\Appointment::where('appointment_approval', 'pending')->count();
        $approvedAppointments = \App\Models\Appointment::where('appointment_approval', 'approved')->count();
        $todayAppointments = \App\Models\Appointment::whereDate('selected_date', now()->toDateString())->count();
    ?>

    <div id="wrapper">
        <?php echo $__env->make('layouts.superadmin-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle" type="button" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-title">Administrator Dashboard</div>
                <div class="top-bar-spacer"></div>
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <main class="superadmin-content">
                <div class="dashboard-container">
                    <div class="dashboard-header">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <h1 class="page-title">Statistics</h1>
                                <p class="page-subtitle">A quick overview of users, law offices, and appointment activity.</p>
                            </div>
                            <div>
                                <button class="btn btn-outline-primary me-2">Export report</button>
                                <button class="btn btn-primary">Refresh data</button>
                            </div>
                        </div>
                    </div>

                    <section class="dashboard-grid">
                        <article class="dashboard-card">
                            <div>
                                <div class="card-title">Total Lawyers</div>
                                <div class="card-value"><?php echo e($totalLawyers); ?></div>
                            </div>
                            <div class="card-meta">
                                <i class="fas fa-scale-balanced"></i>
                                Active legal profiles
                            </div>
                        </article>
                        <article class="dashboard-card">
                            <div>
                                <div class="card-title">Law Offices</div>
                                <div class="card-value"><?php echo e($totalLawOffices); ?></div>
                            </div>
                            <div class="card-meta">
                                <i class="fas fa-building"></i>
                                Registered branches
                            </div>
                        </article>
                        <article class="dashboard-card">
                            <div>
                                <div class="card-title">Client Users</div>
                                <div class="card-value"><?php echo e($totalClients); ?></div>
                            </div>
                            <div class="card-meta">
                                <i class="fas fa-user-friends"></i>
                                Client account total
                            </div>
                        </article>
                        <article class="dashboard-card">
                            <div>
                                <div class="card-title">Appointments</div>
                                <div class="card-value"><?php echo e($totalAppointments); ?></div>
                            </div>
                            <div class="card-meta">
                                <i class="fas fa-calendar-check"></i>
                                Scheduled requests
                            </div>
                        </article>
                    </section>

                    <section class="dashboard-body">
                        <div class="card-panel chart-card">
                            <h3>Appointment activity</h3>
                            <div class="chart-container">
                                <canvas id="appointmentsChart"></canvas>
                            </div>
                            <div class="legend-list">
                                <div class="legend-item"><span class="legend-badge" style="background:#2f80ed"></span> Approved</div>
                                <div class="legend-item"><span class="legend-badge" style="background:#f2994a"></span> Pending</div>
                                <div class="legend-item"><span class="legend-badge" style="background:#27ae60"></span> Today</div>
                                <div class="legend-item"><span class="legend-badge" style="background:#d9534f"></span> Rejected</div>
                            </div>
                        </div>

                        <div class="card-panel">
                            <h3>Appointment summary</h3>
                            <div class="row gy-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Appointments today</span>
                                        <strong><?php echo e($todayAppointments); ?></strong>
                                    </div>
                                    <div class="progress" style="height: 12px; border-radius: 999px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e(min(100, $todayAppointments * 10)); ?>%"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Pending approvals</span>
                                        <strong><?php echo e($pendingAppointments); ?></strong>
                                    </div>
                                    <div class="progress" style="height: 12px; border-radius: 999px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e(min(100, $pendingAppointments * 10)); ?>%"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Approved appointments</span>
                                        <strong><?php echo e($approvedAppointments); ?></strong>
                                    </div>
                                    <div class="progress" style="height: 12px; border-radius: 999px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e(min(100, $approvedAppointments * 10)); ?>%"></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Top performing office</span>
                                        <strong>Diffun Branch</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card-panel mt-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3>Recent activity</h3>
                            <button class="btn btn-sm btn-outline-secondary">View all</button>
                        </div>
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>New users this week</td>
                                    <td>18</td>
                                    <td><span class="status-chip status-approved">Positive</span></td>
                                </tr>
                                <tr>
                                    <td>Office registrations</td>
                                    <td>4</td>
                                    <td><span class="status-chip status-approved">Stable</span></td>
                                </tr>
                                <tr>
                                    <td>Pending appointment reviews</td>
                                    <td><?php echo e($pendingAppointments); ?></td>
                                    <td><span class="status-chip status-pending">Review</span></td>
                                </tr>
                                <tr>
                                    <td>Delayed confirmations</td>
                                    <td>3</td>
                                    <td><span class="status-chip status-rejected">Attention</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
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

            const ctx = document.getElementById('appointmentsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [
                            {
                                label: 'Approved',
                                data: [24, 32, 28, 41, 36, 47],
                                borderColor: '#2f80ed',
                                backgroundColor: 'rgba(47, 128, 237, 0.14)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.35,
                            },
                            {
                                label: 'Pending',
                                data: [12, 18, 14, 21, 19, 24],
                                borderColor: '#f2994a',
                                backgroundColor: 'rgba(242, 153, 74, 0.14)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.35,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#475569' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#475569' },
                                grid: { color: 'rgba(148, 163, 184, 0.18)' }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\superadmin\statistics.blade.php ENDPATH**/ ?>