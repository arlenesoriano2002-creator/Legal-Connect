<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Appointment Management</h1>
            
            <!-- Filter Dropdown -->
            <div class="flex items-center space-x-4">
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Appointments</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="denied">Denied</option>
                </select>
                
                <button onclick="loadAppointments()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consulting</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsTable" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Loading State -->
            <div id="loadingState" class="p-8 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-gray-600">Loading appointments...</p>
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="hidden p-8 text-center">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">No appointments found.</p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Appointment Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50">
                <button onclick="closeModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentAppointmentId = null;

        // Load appointments when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadAppointments();
        });

        // Load appointments based on filter
        async function loadAppointments() {
            const status = document.getElementById('statusFilter').value;
            const tableBody = document.getElementById('appointmentsTable');
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');

            // Show loading, hide others
            loadingState.classList.remove('hidden');
            tableBody.innerHTML = '';
            emptyState.classList.add('hidden');

            try {
                const response = await fetch(`/api/appointments?status=${status}`);
                const appointments = await response.json();

                loadingState.classList.add('hidden');

                if (appointments.length === 0) {
                    emptyState.classList.remove('hidden');
                    return;
                }

                // Populate table
                appointments.forEach(appointment => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50 transition duration-150';
                    
                    // Status badge styling
                    let statusClass = '';
                    let statusText = '';
                    switch(appointment.appointment_approval) {
                        case 'approved':
                            statusClass = 'bg-green-100 text-green-800';
                            statusText = 'Approved';
                            break;
                        case 'denied':
                            statusClass = 'bg-red-100 text-red-800';
                            statusText = 'Denied';
                            break;
                        default:
                            statusClass = 'bg-yellow-100 text-yellow-800';
                            statusText = 'Pending';
                    }

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${appointment.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${appointment.fullname}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${appointment.email}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${appointment.consulting}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${appointment.selected_date} ${appointment.selected_time}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewAppointment(${appointment.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <div class="inline-block relative">
                                <button class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 hidden z-10 border">
                                    <button onclick="updateStatus(${appointment.id}, 'approved')" class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                        <i class="fas fa-check mr-2"></i>Approve
                                    </button>
                                    <button onclick="updateStatus(${appointment.id}, 'denied')" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                        <i class="fas fa-times mr-2"></i>Deny
                                    </button>
                                    <button onclick="updateStatus(${appointment.id}, 'pending')" class="block w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                        <i class="fas fa-clock mr-2"></i>Set Pending
                                    </button>
                                </div>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

            } catch (error) {
                console.error('Error loading appointments:', error);
                loadingState.classList.add('hidden');
                emptyState.innerHTML = '<p class="text-red-600">Error loading appointments</p>';
                emptyState.classList.remove('hidden');
            }
        }

        // View appointment details
        async function viewAppointment(id) {
            currentAppointmentId = id;
            
            try {
                const response = await fetch(`/api/appointments/${id}`);
                const appointment = await response.json();

                const modalContent = document.getElementById('modalContent');
                
                // Create image URLs (assuming images are stored in storage)
                const frontImage = appointment.id_front ? `/storage/${appointment.id_front}` : '/images/default-id.png';
                const backImage = appointment.id_back ? `/storage/${appointment.id_back}` : '/images/default-id.png';

                modalContent.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Personal Information</h4>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Full Name</label>
                                <p class="mt-1 text-gray-900">${appointment.fullname}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Email</label>
                                <p class="mt-1 text-gray-900">${appointment.email}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Phone</label>
                                <p class="mt-1 text-gray-900">${appointment.phone}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Address</label>
                                <p class="mt-1 text-gray-900">${appointment.address}</p>
                            </div>
                        </div>

                        <!-- Appointment Details -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Appointment Details</h4>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Consulting Type</label>
                                <p class="mt-1 text-gray-900">${appointment.consulting}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Selected Date</label>
                                <p class="mt-1 text-gray-900">${appointment.selected_date}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Selected Time</label>
                                <p class="mt-1 text-gray-900">${appointment.selected_time}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Status</label>
                                <p class="mt-1">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                        appointment.appointment_approval === 'approved' ? 'bg-green-100 text-green-800' :
                                        appointment.appointment_approval === 'denied' ? 'bg-red-100 text-red-800' :
                                        'bg-yellow-100 text-yellow-800'
                                    }">
                                        ${appointment.appointment_approval}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- ID Images -->
                        <div class="md:col-span-2 space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Identification Documents</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 block mb-2">Front ID</label>
                                    <img src="${frontImage}" alt="Front ID" class="w-full h-48 object-cover rounded-lg border shadow-sm" onerror="this.src='/images/default-id.png'">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 block mb-2">Back ID</label>
                                    <img src="${backImage}" alt="Back ID" class="w-full h-48 object-cover rounded-lg border shadow-sm" onerror="this.src='/images/default-id.png'">
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="md:col-span-2 space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Timestamps</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Created At</label>
                                    <p class="mt-1 text-gray-900">${new Date(appointment.created_at).toLocaleString()}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Updated At</label>
                                    <p class="mt-1 text-gray-900">${new Date(appointment.updated_at).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Show modal
                document.getElementById('appointmentModal').classList.remove('hidden');

            } catch (error) {
                console.error('Error loading appointment details:', error);
                alert('Error loading appointment details');
            }
        }

        // Update appointment status
        async function updateStatus(id, status) {
            if (!confirm(`Are you sure you want to set this appointment as ${status}?`)) {
                return;
            }

            try {
                const response = await fetch(`/api/appointments/${id}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: status })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Status updated successfully!');
                    loadAppointments(); // Refresh the table
                } else {
                    alert('Error: ' + result.error);
                }

            } catch (error) {
                console.error('Error updating status:', error);
                alert('Error updating status');
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('appointmentModal').addEventListener('click', function(e) {
            if (e.target.id === 'appointmentModal') {
                closeModal();
            }
        });
    </script>
</body>
</html>