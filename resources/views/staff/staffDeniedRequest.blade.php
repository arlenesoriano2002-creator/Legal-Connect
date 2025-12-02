<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/staff/staffDeniedRequest.blade.css') }}">
</head>
<body>
     <div class="container">
   <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">

        <div>
          <div class="logo-container">
            <img src="KG2025 (2).png" alt="LegalConnect logo" width="80" height="80"/>
            <p>LegalConnect</p>
          </div>
          <nav>
             <a href="{{ route('dashboardStaff') }}" class="not-active" tabindex="0">Dashboard</a>
            <a href="{{ route('staff') }}" class="not-active" tabindex="0">Set Appointment</a>
            <a href="{{ url('/StaffClientstbl') }}" class="not-active" tabindex="0">Clients</a>
            <a href="{{ url('/staffAcceptedRequest') }}" class="not-active" tabindex="0">Accepted Request</a>
            <a href="{{ route('staff.deniedRequests') }}" class="active">Denied Requests</a>
            <a href="{{ url('/staffAccount') }}" class="not-active"  tabindex="0">Account</a>
          </nav>
        </div>
      </aside>
    <main>
     <nav class="top-bar" role="banner">
            <div class="nav-logo">
                    <img src="{{ asset('KG2025 (2).png') }}" alt="Legal Connect Logo">
            </div>

            <div class="burger-menu">
              <!-- Burger Button -->
              <button type="button" id="burgerBtn" class="burger-btn" aria-label="Open sidebar">
                  <div class="text-btn">☰ Menu</div>
              </button>
            </div>
            <!-- Spacer to push logout to the right -->
            <div class="top-bar-spacer"></div>

            <!-- Log Out -->
            <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
          </nav>
      <div id="infoModal" class="modal" style="display: none;">
            <div class="modal-content">
              <span class="close" onclick="document.getElementById('infoModal').style.display='none'">&times;</span>

              <div class="modal-left">
                <form id="updateForm" method="POST" action="/appointments/update/ID">
                  @csrf
                  @method('PATCH')
                  <input type="hidden" id="appointment_id" name="id">

                  <label for="fullname">Fullname:</label>
                  <input type="text" name="fullname" id="fullname" required>

                  <label for="address">Address:</label>
                  <input type="text" name="address" id="address" required>

                  <label for="phone">Phone:</label>
                  <input type="text" name="phone" id="phone" required>

                  <label for="consulting">Consulting:</label>
                  <input type="text" name="consulting" id="consulting" required>

                  <label for="selected_date">Date:</label>
                  <input type="date" name="selected_date" id="selected_date" required>

                  <label for="selected_time">Time:</label>
                  <input type="time" name="selected_time" id="selected_time" required>

                  <label for="term_status">Term Status:</label>
                  <input type="text" name="term_status" id="term_status" required>

                  <button type="submit">Update Info</button>
                </form>
              </div>

              <div class="modal-right">
                <img id="id_front_preview" src="#" alt="Front ID">
                <img id="id_back_preview" src="#" alt="Back ID">
              </div>
            </div>
        </div>
      </div>
        <div>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Fullname</th>
                  <th>Address</th>
                  <th>Phone</th>
                  <th>Consulting</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($appointments ?? [] as $appointment)

                  @if(strtolower($appointment->appointment_approval) === 'denied')
                        <tr>
                        <td>{{ $appointment->id }}</td>
                        <td>{{ $appointment->fullname }}</td>
                        <td>{{ $appointment->address }}</td>
                        <td>{{ $appointment->phone }}</td>
                        <td>{{ $appointment->consulting }}</td>
                        <td>{{ ucfirst($appointment->appointment_approval) }}</td>
                        <td>
                            <button title="See Info" data-id="{{ $appointment->id }}" class="btn-view">
                                <i class="fas fa-eye"></i> VIEW INFORMATION
                            </button>
                            <form method="POST" action="{{ url('/appointments/delete/' . $appointment->id) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn-remove" onclick="return confirm('Are you sure you want to delete this appointment?')">
                                REMOVE
                            </button>
                            </form>

                            <form method="POST" action="{{ url('/appointments/reaccept/' . $appointment->id) }}" style="display:inline;">
                            @csrf
                            @method('PUT')

                            <button type="submit" class="btn-reaccept" onclick="return confirm('Mark this appointment as approved?')">
                                RE-ACCEPT
                            </button>
                            </form>

                        </td>
                        </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">No denied appointments found.</td>
                    </tr>
                @endforelse
              </tbody>
            </table>
            <!-- Archive Confirmation Modal (place once per page, near end of body) -->
            <div id="archiveConfirmModal" class="archive-modal" aria-hidden="true" role="dialog" aria-modal="true">
              <div class="archive-modal-backdrop" data-close></div>
              <div class="archive-modal-box" role="document" aria-labelledby="archiveModalTitle">
                <header>
                  <h3 id="archiveModalTitle">Confirm Archive</h3>
                </header>

                <div class="archive-modal-body">
                  <p>Are you sure you want to archive this appointment?</p>
                  <p class="archive-item-name" style="font-weight:600; margin-top:8px;"></p>
                </div>

                <footer class="archive-modal-footer">
                  <button type="button" class="btn cancel-archive-btn">Cancel</button>
                  <button type="button" class="btn confirm-archive-btn">Yes, Archive</button>
                </footer>
              </div>
            </div>

          </div>

        </div>
    </main>
  </div>
 <script src="{{ asset('js/staff/staffDeniedRequest.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  const modal = document.getElementById('archiveConfirmModal');
  const itemNameEl = modal ? modal.querySelector('.archive-item-name') : null;
  const btnCancel = modal ? modal.querySelector('.cancel-archive-btn') : null;
  const btnConfirm = modal ? modal.querySelector('.confirm-archive-btn') : null;
  const backdrop = modal ? modal.querySelector('[data-close]') : null;

  let pendingForm = null;

  function openModal(name, form) {
    pendingForm = form;
    if (itemNameEl) itemNameEl.textContent = name ? name : '';
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    // focus confirm for accessibility
    btnConfirm && btnConfirm.focus();
  }

  function closeModal() {
    pendingForm = null;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }

  // Attach to all archive forms
  document.querySelectorAll('.archive-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      // intercept submit
      e.preventDefault();

      const fullname = form.getAttribute('data-fullname') || '';
      openModal(fullname, form);
    });
  });

  // Cancel button
  btnCancel && btnCancel.addEventListener('click', function () {
    closeModal();
  });

  // Backdrop click closes
  backdrop && backdrop.addEventListener('click', function () {
    closeModal();
  });

  // Confirm -> submit the form
  btnConfirm && btnConfirm.addEventListener('click', function () {
    if (!pendingForm) { closeModal(); return; }

    // submit programmatically
    // create a clone of form to avoid potential double-submits or location changes
    pendingForm.submit();
    // optional: show a tiny 'processing' state briefly (not required)
    closeModal();
  });

  // close modal on Escape
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && modal.style.display === 'flex') {
      closeModal();
    }
  });

});
</script>


</body>
</html>