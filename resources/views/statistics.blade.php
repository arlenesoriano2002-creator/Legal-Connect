<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="user-role" content="{{ Auth::user()->role ?? '' }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Statistics | LegalConnect</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admindashboard.blade.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            width: 100%;
            background: #f4f7fb;
            color: #172b4d;
            font-family: 'Inter', sans-serif;
        }
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        #sidebar-wrapper {
            min-height: 100vh;
            width: 220px;
            background: #2f4050;
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        #page-content-wrapper {
            width: calc(100% - 220px);
            margin-left: 220px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            #sidebar-wrapper {
                transform: translateX(-100%);
            }
            #wrapper.toggled #sidebar-wrapper {
                transform: translateX(0);
            }
            #page-content-wrapper {
                width: 100%;
                margin-left: 0;
            }
        }
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .page-subtitle {
            color: #576774;
            margin-bottom: 1.5rem;
            max-width: 620px;
        }
        .metric-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 16px 30px rgba(35, 47, 63, 0.08);
        }
        .metric-card .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(75, 123, 236, 0.12);
            color: #4b7bec;
        }
        .chart-panel {
            border-radius: 20px;
            box-shadow: 0 16px 30px rgba(35, 47, 63, 0.08);
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #fff;
            padding: 1.5rem;
        }
        .stats-table {
            min-width: 100%;
        }
        .table thead th {
            border-bottom: 2px solid #eef2f6;
        }
        .table tbody tr:hover {
            background: #f8fafc;
        }
        .badge-status {
            border-radius: 999px;
            font-size: 0.78rem;
            padding: 0.4rem 0.72rem;
        }
        .badge-approved { background: #d7f5e3; color: #1b6d3a; }
        .badge-pending { background: #fff4d6; color: #8a5a00; }
        .badge-rejected { background: #ffe2e2; color: #9d1f28; }
        .breadcrumb-item+.breadcrumb-item::before {
            content: '>';
        }
        .top-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            margin-bottom: 1.5rem;
        }
        .top-bar-spacer {
            flex: 1;
        }
        .top-bar button,
        .top-bar .notification-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 14px;
            border: 1px solid transparent;
            padding: 0.65rem 0.95rem;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .top-bar button#menu-toggle {
            min-width: 46px;
            min-height: 46px;
            border-color: #dde6f0;
            background: #f7f9fd;
            color: #172b4d;
        }
        .top-bar .notification-btn {
            background: #f7f9fd;
            border-color: #dde6f0;
            color: #172b4d;
        }
        .top-bar .logout-btn {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }
        .top-bar .logout-btn:hover {
            background: #1b40d5;
            border-color: #1b40d5;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 10px;">
                <div class="head-content" style="display: flex; flex-direction: row; align-items: center;">
                    <img src="{{ asset('logo6.png') }}" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                    <span>LegalConnect</span>
                </div>
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
                <a href="{{ route('admin.walkins') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.walkins') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-Ins logs</span>
                </a>
                <a href="{{ route('statistics') }}" class="list-group-item list-group-item-action {{ request()->routeIs('statistics') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics</span>
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
                    <a href="{{ route('messages.sms') }}" class="list-group-item list-group-item-action {{ request()->is('sms-chat') ? 'active' : '' }}">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('admin.system-chat') }}" class="list-group-item list-group-item-action {{ request()->is('admin/system-chat') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="{{ url('/practice-areas') }}" class="list-group-item list-group-item-action {{ request()->is('practice-areas') ? 'active' : '' }}">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Services</span>
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
                    <i class="fa-solid fa-user-group"></i>
                    <span>All Staff Accounts</span>
                </a>
                <a href="{{ route('admin.account.settings') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('admin.account.settings') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Account Setting</span>
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn" id="menu-toggle" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-spacer"></div>
                <button type="button" class="notification-btn" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                </button>
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    Log out
                </button>
            </nav>
            <div class="container-fluid py-4">
                <div class="d-sm-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                <h1 class="page-title">Statistics</h1>
                <p class="page-subtitle">View the latest appointment and walk-in performance metrics for your team. Use this dashboard to track trends and identify growth opportunities.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Export report
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">Total Appointments</h6>
                            <h2 class="mb-0">{{ number_format($totalAppointments) }}</h2>
                        </div>
                        <span class="card-icon"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up"></i> 14.2% this month</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">Walk-Ins</h6>
                            <h2 class="mb-0">{{ number_format($walkinsCount) }}</h2>
                        </div>
                        <span class="card-icon"><i class="fas fa-door-open"></i></span>
                    </div>
                    <p class="text-primary mb-0"><i class="fas fa-arrow-up"></i> 8.7% this week</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">Pending Requests</h6>
                            <h2 class="mb-0">{{ number_format($pendingCount) }}</h2>
                        </div>
                        <span class="card-icon"><i class="fas fa-hourglass-half"></i></span>
                    </div>
                    <p class="text-warning mb-0"><i class="fas fa-arrow-up"></i> Updated live</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">Accepted Requests</h6>
                            <h2 class="mb-0">{{ number_format($approvedCount) }}</h2>
                        </div>
                        <span class="card-icon"><i class="fas fa-thumbs-up"></i></span>
                    </div>
                    <p class="text-success mb-0"><i class="fas fa-arrow-up"></i> Accepted this month</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="chart-panel h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Appointment Trend</h5>
                            <p class="text-muted mb-0">Monthly requests and walk-ins over the last 6 months.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary">Last 6 months</button>
                    </div>
                    <div class="position-relative" style="min-height: 320px;">
                        <canvas id="appointmentsTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="chart-panel h-100">
                    <div class="mb-3">
                        <h5 class="mb-1">Request Status</h5>
                        <p class="text-muted mb-0">Approved, Pending, and Rejected requests.</p>
                    </div>
                    <div class="position-relative" style="min-height: 300px;">
                        <canvas id="statusBreakdownChart"></canvas>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <span class="badge badge-approved">Approved</span>
                        <span class="badge badge-pending">Pending</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-xl-7">
                <div class="chart-panel">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Top Law Offices</h5>
                            <p class="text-muted mb-0">Most active offices by request volume.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle stats-table mb-0">
                            <thead>
                                <tr>
                                    <th>Law Office</th>
                                    <th>Requests</th>
                                    <th>Approval</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topLawOffices as $office)
                                    <tr>
                                        <td>{{ $office['law_office'] }}</td>
                                        <td>{{ $office['requests'] }}</td>
                                        <td>{{ $office['approval'] }}%</td>
                                        <td><span class="badge-status {{ $office['badge'] }}">{{ $office['status'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="chart-panel">
                    <div class="mb-3">
                        <h5 class="mb-1">Today at a Glance</h5>
                        <p class="text-muted mb-0">Key activity metrics for the current day.</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-light p-3 border-0">
                                <h6 class="text-muted">New Requests</h6>
                                <h3 class="mb-0">{{ number_format($todayNewRequests) }}</h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light p-3 border-0">
                                <h6 class="text-muted">Approved</h6>
                                <h3 class="mb-0">{{ number_format($todayApproved) }}</h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light p-3 border-0">
                                <h6 class="text-muted">Walk-Ins</h6>
                                <h3 class="mb-0">{{ number_format($todayWalkIns) }}</h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light p-3 border-0">
                                <h6 class="text-muted">Rejected</h6>
                                <h3 class="mb-0">{{ number_format($todayRejected) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                document.getElementById('wrapper').classList.toggle('toggled');
            });
        }

        const trendCtx = document.getElementById('appointmentsTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [
                    {
                        label: 'Appointment Requests',
                        data: @json($appointmentTrend),
                        borderColor: '#4b7bec',
                        backgroundColor: 'rgba(75, 123, 236, 0.18)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#4b7bec'
                    },
                    {
                        label: 'Walk-Ins',
                        data: @json($walkinTrend),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.16)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#1cc88a'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(15, 23, 42, 0.07)' }, beginAtZero: true }
                }
            }
        });

        const statusCtx = document.getElementById('statusBreakdownChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [{{ $approvedCount }}, {{ $pendingCount }}, {{ $rejectedCount }}],
                    backgroundColor: ['#4b7bec', '#f9c851', '#ff6b6b'],
                    hoverOffset: 10,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
