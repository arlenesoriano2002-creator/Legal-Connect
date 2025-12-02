<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/partials/archived-table.blade.css') }}">
</head>
<body>
    

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
          <th>Delete</th> <!-- NEW -->
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

            <!-- Keep these two -->
            <td><button class="image-btn" data-src="{{ asset('storage/' . $appointment->id_front) }}">View Front</button></td>
            <td><button class="image-btn" data-src="{{ asset('storage/' . $appointment->id_back) }}">View Back</button></td>

            <!-- NEW: Delete icon button (does NOT auto-submit) -->
            <td>
              <form action="{{ route('archived.delete', $appointment->id) }}"
                    method="POST"
                    class="delete-archived-form"
                    data-name="{{ $appointment->fullname }}">
                @csrf
                @method('DELETE')
                <button type="button" class="icon-delete-btn" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>


      <!--  <h2 class="page-title">Database Backups</h2>

      <div class="table-container">
        <div class="table-wrapper">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Filename</th>
                <th>Date Created</th>
                <th style="text-align:center;">Actions</th>
              </tr>
            </thead>

            <tbody>
              @forelse($backups as $backup)
                <tr>
                  <td>{{ $backup->file_name }}</td>
                  <td>{{ $backup->created_at }}</td>

                  <td style="text-align:center; display:flex; gap:10px; justify-content:center;">-->

                    <!-- ✅ Download Backup as ENCRYPTED PDF -->
                   <!-- <a href="{{ route('admin.backup.download', $backup->file_name) }}"
                      class="btn btn-small" 
                      style="background:#007bff;color:#fff;border-radius:4px;padding:6px 10px;">
                      <i class="fa-solid fa-file-pdf"></i> PDF
                    </a>-->

                    <!-- ✅ DELETE Backup (file + DB row) 
                    <form action="{{ route('admin.backup.delete', $backup->file_name) }}"
                        method="POST"
                        class="delete-backup-form"
                        data-file="{{ $backup->file_name }}">
                      @csrf
                      @method('DELETE')

                      <button type="button" class="btn btn-small backup-delete-btn"
                              style="background:#d9534f;color:#fff;border-radius:4px;padding:6px 10px;">
                          <i class="fa-solid fa-trash"></i>
                      </button>
                  </form>


                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" style="text-align:center;">No backups stored yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>-->

  </div>
</div>

<!-- Existing Image Modal -->
<div id="archiveImageModal" class="image-modal">
  <div class="image-modal-backdrop"></div>
  <div class="image-modal-content">
    <button type="button" class="image-modal-close" aria-label="Close">&times;</button>
    <figure class="image-modal-figure">
      <img id="popupImage" class="image-modal-img" src="" alt="ID Image">
    </figure>
  </div>
</div>

<!-- NEW: Delete Confirmation Modal--> 
<div id="deleteConfirmModal" class="delete-modal">
  <div class="delete-modal-backdrop"></div>
  <div class="delete-modal-box">
    <div class="delete-modal-header">
      <h3>Confirm Delete</h3>
    </div>

    <p id="deleteItemName" class="delete-modal-message"></p>

    <div class="delete-modal-actions">
      <button type="button" class="cancel-delete-btn">Cancel</button>
      <button type="button" class="confirm-delete-btn">Delete</button>
    </div>
  </div>
</div>

<!-- BACKUP DELETE CONFIRMATION MODAL -->
<div id="deleteBackupModal" class="delete-modal">
  <div class="delete-modal-backdrop"></div>
  <div class="delete-modal-box">
    <div class="delete-modal-header">
      <h3>Confirm Backup Deletion</h3>
    </div>

    <p id="deleteBackupName" class="delete-modal-message"></p>

    <div class="delete-modal-actions">
      <button type="button" class="cancel-backup-delete-btn">Cancel</button>
      <button type="button" class="confirm-backup-delete-btn">Delete</button>
    </div>
  </div>
</div>


<script src="{{ asset('js/archived-table.js') }}"></script>

</body>
</html>