<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <!-- PapaParse for CSV parsing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    
    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
    
    <link rel="stylesheet" href="{{ asset('css/staff/walkInsLogs.blade.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/walkInsLogs-table.css') }}">
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
                <!-- Dashboard link - Connected ✓ -->
                <a href="{{ route('dashboardStaff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboardStaff') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Set Time link - Connected ✓ -->
                <a href="{{ route('staff') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                
                <!-- Walk-ins logs - Connected ✓ -->
                <a href="{{ route('staff.walkins.logs') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.walkins.logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-ins logs</span>
                </a>
                
                <!-- Feedbacks - Connected ✓ -->
                <a href="{{ route('staff.feedback.reports') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.feedback.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-comments" style="color: #d7dae0;"></i>
                    <span>Feedbacks</span>
                </a>
                
                <!-- Pending Requests - Connected ✓ -->
                <a href="{{ route('staff.clients.pending') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.clients.pending') ? 'active' : '' }}">
                    <i class="fas fa-clock"></i>
                    <span>Pending Requests</span>
                </a>
                
                <!-- Accepted Requests - Connected ✓ -->
                <a href="{{ route('staff.acceptedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.acceptedRequests') ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i>
                    <span>Accepted Requests</span>
                </a>
                
                <!-- Denied Requests - Connected ✓ -->
                <a href="{{ route('staff.deniedRequests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.deniedRequests') ? 'active' : '' }}">
                    <i class="fas fa-times-circle"></i>
                    <span>Denied Requests</span>
                </a>
                
                <!-- Message Inquiries - Connected ✓ -->
                <a href="{{ route('diffun.message.inquiries') }}" class="list-group-item list-group-item-action {{ request()->routeIs('diffun.message.inquiries') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Message Inquiries</span>
                </a>

                <!-- Account Setting - Connected ✓ -->
                <a href="{{ route('staff.account.settings') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.account.settings') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Account Setting</span>
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
                <!-- Notification container (ensures diffunNotifications bell appears) -->
                <div class="notification-container" id="diffun-notification-container" style="position:relative;margin-left:12px">
                    <button id="diffunNotificationBtn" class="notification-btn btn btn-light" style="position:relative">
                        <i class="fas fa-bell"></i>
                        <span id="diffunNotificationBadge" class="badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#ff4757;color:#fff;padding:2px 6px;border-radius:12px;font-size:11px">0</span>
                    </button>
                    <div id="diffunNotificationDropdown" class="notification-dropdown" style="display:none;position:absolute;right:0;top:40px;z-index:9999;width:360px;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden">
                        <div class="notification-header" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;justify-content:space-between;align-items:center">
                            <strong>Notifications</strong>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button id="diffunMarkAllBtn" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 8px">Mark all as read</button>
                                <small id="diffunNotificationTime" style="color:#888;font-size:12px"></small>
                            </div>
                        </div>
                        <div id="diffunNotificationList" class="notification-list" style="max-height:320px;overflow:auto;padding:8px">No new notifications</div>
                        <div style="padding:8px;border-top:1px solid #f0f0f0;background:#fafafa;text-align:center;font-size:13px;color:#666">
                            <a href="/StaffClientstbl" style="text-decoration:none">View all</a>
                        </div>
                    </div>
                </div>

                <!-- Notification Dropdown -->
                <div class="notification-container">
                     <!--<button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>-->
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Appointment Request Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="{{ route('clientstbl') }}" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>
               
                <!-- Log Out -->
                <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Dashboard Content -->
            <div class="dashboard-container">
                <div class="container-fluid py-4">
                    <!-- Header -->
                   <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Walk-in Logs
                        </h1>
                        <div class="d-flex gap-2">
                            <!-- Logbook Password Button
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#logbookPasswordModal">
                                <i class="fas fa-key me-1"></i> Logbook Password
                            </button>-->
                            <!-- Purpose Choices Button-->
                            <a href="{{ route('staff.purpose.choices') }}" class="btn btn-info">
                                <i class="fas fa-list-check me-1"></i> Purpose Choices
                            </a>
                            <!-- Excel Export Form -->
                            <form id="excelExportForm" action="{{ route('staff.walkins.logs.export.excel') }}" method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="search" id="excelSearch">
                                <input type="hidden" name="purpose" id="excelPurpose">
                                <input type="hidden" name="sort_column" id="excelSortColumn">
                                <input type="hidden" name="sort_order" id="excelSortOrder">
                                <button type="submit" class="btn btn-primary" id="saveExcelBtn">
                                    <i class="fas fa-file-excel me-1"></i> Save as Excel
                                </button>
                            </form>
                            <!-- PDF Export Form -->
                            <form id="pdfExportForm" action="{{ route('staff.walkins.logs.export.pdf') }}" method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="search" id="pdfSearch">
                                <input type="hidden" name="purpose" id="pdfPurpose">
                                <input type="hidden" name="sort_column" id="pdfSortColumn">
                                <input type="hidden" name="sort_order" id="pdfSortOrder">
                                <button type="submit" class="btn btn-danger" id="savePdfBtn">
                                    <i class="fas fa-file-pdf me-1"></i> Save as PDF
                                </button>
                            </form>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#backupLogsModal">
                                <i class="fas fa-archive me-1"></i> View Backup Logs
                            </button>
                        </div>
                    </div>

                    <!-- Search Filter Only -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                               <!-- Search input -->
                                <div class="col-md-6">
                                    <div class="filter-group">
                                        <label class="form-label"><strong>Search</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control" id="searchInput" placeholder="Search walk-ins...">
                                            <!-- Add refresh button here -->
                                            <button class="btn btn-outline-primary" type="button" id="refreshButton" title="Refresh Table">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Purpose Filter Dropdown -->
                                <div class="col-md-6">
                                    <div class="filter-group">
                                        <label class="form-label"><strong>Filter by Purpose</strong></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-filter"></i>
                                            </span>
                                            <select class="form-select" id="purposeFilter">
                                                <option value="">All Purposes</option>
                                                @if(isset($purposes) && count($purposes) > 0)
                                                    @foreach($purposes as $purpose)
                                                        <option value="{{ $purpose->purpose }}">{{ $purpose->purpose }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(isset($purposes) && count($purposes) > 0)
                                                <button class="btn btn-outline-secondary" type="button" id="clearPurposeFilter" title="Clear purpose filter">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table - Without Status Column -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="walkinsTable">
                                    <thead>
                                        <tr>
                                            <th>FULL NAME</th>
                                            <th>ADDRESS</th>
                                            <th>CONTACT</th>
                                            <th>PURPOSE</th>
                                            <th>OFFICE</th>
                                            <th>DATE & TIME</th>
                                            <th>CREATED</th>
                                            <th>ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($walkins) && count($walkins) > 0)
                                            @foreach($walkins as $walkin)
                                            <tr>
                                                <td>{{ $walkin->fullname }}</td>
                                                <td>{{ $walkin->address }}</td>
                                                <td>{{ $walkin->contact_number ?? 'N/A' }}</td>
                                                <td>{{ $walkin->purpose }}</td>
                                                <td>{{ $walkin->office_name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($walkin->date_time)
                                                        {{ date('Y-m-d g:i A', strtotime($walkin->date_time)) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ date('m/d/Y', strtotime($walkin->created_at)) }}</td>
                                                <td>
                                                    <!-- Change from View to Delete -->
                                                    <button class="btn btn-sm btn-danger delete-walkin-btn" 
                                                            data-id="{{ $walkin->id }}" 
                                                            data-name="{{ $walkin->fullname }}">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> No walk-in records found.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>

<!-- Backup Logs Modal -->
<div class="modal fade" id="backupLogsModal" tabindex="-1" aria-labelledby="backupLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="backupLogsModalLabel">
                    <i class="fas fa-history me-2"></i> Backup Logs
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Filename</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupLogsList">
                            @if(isset($backupLogs) && $backupLogs->count() > 0)
                                @foreach($backupLogs as $backup)
                                @php
                                    $fileExtension = strtoupper(pathinfo($backup->decrypted_name, PATHINFO_EXTENSION));
                                @endphp
                                    <tr class="backup-item" 
                                        data-id="{{ $backup->id }}" 
                                        data-name="{{ $backup->decrypted_name }}"
                                        data-date="{{ $backup->formatted_date }}"
                                        data-type="{{ $fileExtension }}"
                                        data-size="{{ $backup->file_size ?? 'N/A' }}">
                                        <td>{{ $backup->decrypted_name }}</td>
                                       
                                        <td>{{ $backup->formatted_date }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- View Button (Eye Icon) -->
                                                <button type="button" 
                                                        class="btn btn-info preview-backup-btn" 
                                                        data-id="{{ $backup->id }}"
                                                        title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <!-- Download Button -->
                                                <button type="button" 
                                                        class="btn btn-success download-backup-btn" 
                                                        data-id="{{ $backup->id }}"
                                                        title="Download">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <!-- Delete Button - NEW -->
                                                <button type="button" 
                                                        class="btn btn-danger delete-backup-btn" 
                                                        data-id="{{ $backup->id }}"
                                                        data-name="{{ $backup->decrypted_name }}"
                                                        title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-3"></i><br>
                                        No backup files found.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div style="font-size: 48px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="text-center mb-3">Delete Backup File</h4>
                <p class="text-center">
                    Are you sure you want to delete the file:<br>
                    <strong id="deleteFileName" class="text-danger"></strong>
                </p>
                <p class="text-muted text-center small">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Walk-in Confirmation Modal -->
<div class="modal fade" id="deleteWalkinModal" tabindex="-1" aria-labelledby="deleteWalkinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteWalkinModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div style="font-size: 48px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h4 class="text-center mb-3">Delete Walk-in Record</h4>
                <p class="text-center">
                    Are you sure you want to delete the walk-in record for:<br>
                    <strong id="deleteWalkinName" class="text-danger"></strong>
                </p>
                <p class="text-muted text-center small">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    This action cannot be undone. All data will be permanently removed.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteWalkinBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Backup Preview Modal (Fullscreen Layout) -->
<div class="modal fade" id="backupPreviewModal" tabindex="-1" aria-labelledby="backupPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="min-height: 100vh;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="backupPreviewModalLabel">
                    <i class="fas fa-file-alt me-2"></i> File Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="container-fluid h-100">
                    <div class="row h-100">
                        <!-- Left Column - File Preview (70% width) -->
                        <div class="col-lg-9 col-md-8 p-0 h-100">
                            <!-- PDF Preview Section -->
                            <div id="pdfPreviewSection" style="display: none; height: 100%;">
                                <div class="h-100 w-100">
                                    <iframe id="pdfPreviewFrame" class="w-100 h-100" style="border: none;"></iframe>
                                </div>
                            </div>

                            <!-- Excel/CSV Preview Section -->
                            <div id="excelPreviewSection" style="display: none; height: 100%; overflow: auto;">
                                <div class="p-4">
                                    <div class="table-responsive">
                                        <table id="excelPreviewTable" class="table table-bordered table-striped table-hover">
                                            <!-- Excel content will be inserted here -->
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- CSV Preview Section -->
                            <div id="csvPreviewSection" style="display: none; height: 100%; overflow: auto;">
                                <div class="p-4">
                                    <div class="table-responsive">
                                        <table id="csvPreviewTable" class="table table-bordered table-striped table-hover">
                                            <!-- CSV content will be inserted here -->
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - File Details (30% width) -->
                        <div class="col-lg-3 col-md-4 p-4" style="background-color: #f8f9fa; border-left: 1px solid #dee2e6;">
                            <div class="file-details-container h-100 d-flex flex-column">
                                <!-- File Info -->
                                <div id="fileInfo" class="mb-4">
                                    <h4 class="mb-3">
                                        <i class="fas fa-file me-2"></i> 
                                        <span id="backupFileName" class="text-truncate d-block fs-4 fw-bold">File Name</span>
                                    </h4>
                                    
                                    <div class="file-info-card card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-1">
                                                    <i class="fas fa-calendar me-2"></i> Created Date
                                                </h6>
                                                <p class="mb-0 fs-5 fw-semibold" id="backupFileDate">Date</p>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-1">
                                                    <i class="fas fa-file-alt me-2"></i> File Type
                                                </h6>
                                                <p class="mb-0 fs-5 fw-semibold" id="backupFileType">Type</p>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <h6 class="text-muted mb-1">
                                                    <i class="fas fa-hashtag me-2"></i> File Size
                                                </h6>
                                                <p class="mb-0 fs-5 fw-semibold" id="backupFileSize">N/A</p>
                                            </div>
                                            
                                            <div class="d-grid gap-2 mt-4">
                                                <a id="downloadPreviewBtn" href="#" class="btn btn-success btn-lg py-3">
                                                    <i class="fas fa-download me-2"></i> Download File
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary py-3" data-bs-dismiss="modal">
                                                    <i class="fas fa-times me-2"></i> Close Preview
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zoom Controls for PDF -->
                                <div id="pdfControls" class="mt-3" style="display: none;">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-search me-2"></i> Zoom Controls
                                    </h6>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary" id="zoomOutBtn">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="resetZoomBtn">
                                            <i class="fas fa-search"></i> 100%
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="zoomInBtn">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div id="previewError" class="alert alert-danger mt-3" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span id="errorMessage"></span>
                                    <div class="mt-3">
                                        <button id="downloadInsteadBtn" class="btn btn-warning w-100 py-2">
                                            <i class="fas fa-download me-1"></i> Download File Instead
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  <!-- Bootstrap Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
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
                    <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
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
    

<!-- Logbook Password Modal -->
<div class="modal fade" id="logbookPasswordModal" tabindex="-1" aria-labelledby="logbookPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="logbookPasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Logbook Password Settings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size: 48px; color: #ffc107;">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4>Update Logbook Login Credentials</h4>
                    <p class="text-muted">Edit the logbook password for Diffun branch (ID: 1)</p>
                </div>
                
                <form id="logbookPasswordForm">
                    @csrf
                    <input type="hidden" id="logbookId" name="id" value="1">
                    
                    <div class="mb-3">
                        <label for="logbookUsername" class="form-label">
                            <i class="fas fa-user me-1"></i> Username
                        </label>
                        <input type="text" class="form-control" id="logbookUsername" name="username" 
                               placeholder="Enter username" required>
                        <div class="form-text">Current logbook username</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logbookPassword" class="form-label">
                            <i class="fas fa-key me-1"></i> New Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="logbookPassword" 
                                name="password" placeholder="Enter new password (leave blank to keep current)" 
                                autocomplete="new-password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" 
                                    data-target="logbookPassword" title="Show password">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        
                        <!-- Password strength meter -->
                        <div class="mt-2" id="passwordStrengthContainer" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Password Strength:</small>
                                <small id="passwordStrengthText" class="fw-bold">Very Weak</small>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div id="passwordStrengthBar" class="progress-bar" role="progressbar" 
                                    style="width: 0%; transition: width 0.3s ease;"></div>
                            </div>
                            <div id="passwordStrengthTips" class="mt-2">
                                <small class="text-muted d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tips: Use at least 8 characters with mix of letters, numbers, and symbols
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-text">Leave empty if you don't want to change the password</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="logbookBranch" class="form-label">
                            <i class="fas fa-building me-1"></i> Branch
                        </label>
                        <input type="text" class="form-control" id="logbookBranch" value="diffun" readonly>
                        <div class="form-text">Branch location (cannot be changed)</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Password field is kept empty for security. Only fill if you want to change the password.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="saveLogbookPasswordBtn">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="{{ asset('js/staff/walkInLogs.js') }}"></script>
    <script src="{{ asset('js/staff/walkInsLogs-table.js') }}"></script>
    <script src="{{ asset('js/staff/diffunNotifications.js') }}"></script>

    
</body>
</html>