<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Archived Appointments</title>
    <link rel="stylesheet" href="{{ asset('css/archivedAppointments.blade.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <a href="{{ url('/admindashboard') }}" class="not-active">Dashboard</a>
                    <a href="{{ url('/administrator') }}" class="not-active">Set Appointment</a>
                    <a href="{{ url('/clientstbl') }}" class="not-active">Pending Request</a>
                    <a href="{{ url('/adminAcceptedRequest') }}" class="not-active">Accepted Request</a>
                    <a href="{{ url('/adminDeniedRequest') }}" class="not-active">Denied Request</a>
                    <a href="{{ url('/admin/archived') }}" class="active">Archived</a>
                    <a href="{{ route('messages.page') }}" class="not-active">Messages</a>
                    <a href="{{ url('/adminAccount') }}" class="not-active">Account</a>
                </nav>
            </div>
        </aside>

        <main>
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

            <!-- Backups Section -->
            <div class="dashboard-container">
                <h2 class="page-title">Database Backups</h2>
                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Created</th>
                                    <th>Size (KB)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $b)
                                    <tr>
                                        <td>{{ $b['name'] ?? $b->file_name }}</td>
                                        <td>{{ $b['mtime'] ?? $b->created_at }}</td>
                                        <td>{{ isset($b['size']) ? round($b['size']/1024, 2) : '-' }}</td>
                                        <td>
                                            @php
                                                $filename = isset($b['name']) ? $b['name'] : ($b->file_name ?? null);
                                            @endphp

                                            @if ($filename)
                                                <a href="{{ route('admin.download.backup', ['filename' => $filename]) }}" class="btn btn-success btn-sm">
                                                    Download
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">No backups available yet.</td></tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Archived Appointments Section -->
            <div class="dashboard-container">
                <h2 class="page-title">Archived Appointments</h2>

                <div class="table-container">
                    <div class="table-wrapper">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Address</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Consulting</th>
                                    <th>Selected Date</th>
                                    <th>Selected Time</th>
                                    <th>Terms Status</th>
                                    <th>Approval Status</th>
                                    <th>ID Front</th>
                                    <th>ID Back</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentArchived as $appointment)
                                <tr>
                                    <td>{{ $appointment->fullname }}</td>
                                    <td>{{ $appointment->address }}</td>
                                    <td>{{ $appointment->phone }}</td>
                                    <td>{{ $appointment->email }}</td>
                                    <td>{{ $appointment->consulting }}</td>
                                    <td>{{ $appointment->selected_date }}</td>
                                    <td>{{ $appointment->selected_time }}</td>
                                    <td>{{ $appointment->term_status }}</td>
                                    <td>{{ $appointment->appointment_approval }}</td>
                                    <td>
                                        <button type="button" class="image-btn" data-src="{{ asset('storage/' . $appointment->id_front) }}">
                                            View Front
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" class="image-btn" data-src="{{ asset('storage/' . $appointment->id_back) }}">
                                            View Back
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="image-modal" aria-hidden="true" role="dialog" aria-label="ID image dialog">
        <div class="image-modal-backdrop" tabindex="-1"></div>
        <div class="image-modal-content" role="document" aria-modal="true">
            <button class="image-modal-close" aria-label="Close">&times;</button>
            <figure class="image-modal-figure">
                <img id="popupImage" class="image-modal-img" src="" alt="ID Image">
                <figcaption id="imageCaption" class="image-caption" style="display:none"></figcaption>
            </figure>
        </div>
    </div>

    <script src="{{ asset('js/admindashboard.js') }}"></script>

    <!-- Sortable Columns -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const table = document.querySelector(".admin-table");
        const headers = table.querySelectorAll("th");
        let sortDirection = true;
        headers.forEach((header, index) => {
            header.addEventListener("click", () => {
                const rows = Array.from(table.querySelectorAll("tbody tr"));
                const type = header.textContent.includes("Date") || header.textContent.includes("Time") ? "date" : "text";
                rows.sort((a, b) => {
                    const aText = a.children[index].textContent.trim();
                    const bText = b.children[index].textContent.trim();
                    if (type === "date") {
                        return sortDirection
                            ? new Date(aText) - new Date(bText)
                            : new Date(bText) - new Date(aText);
                    }
                    return sortDirection
                        ? aText.localeCompare(bText)
                        : bText.localeCompare(aText);
                });
                const tbody = table.querySelector("tbody");
                tbody.innerHTML = "";
                rows.forEach(row => tbody.appendChild(row));
                sortDirection = !sortDirection;
            });
        });
    });
    </script>

    <!-- Image Modal Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('popupImage');
        const closeBtn = modal.querySelector('.image-modal-close');
        const backdrop = modal.querySelector('.image-modal-backdrop');

        function openModal(url) {
            modalImg.src = url || '';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeModal() {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modalImg.src = '';
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }

        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.image-btn');
            if (btn) openModal(btn.getAttribute('data-src'));
        });

        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });
    });
    </script>
</body>
</html>
