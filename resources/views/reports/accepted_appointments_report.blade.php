<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Appointments Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #0d6efd;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }
        
        .filters {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        
        .filters h4 {
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .filter-item strong {
            color: #0d6efd;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        table thead {
            background: #0d6efd;
            color: #fff;
        }
        
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #0d6efd;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tbody tr:nth-child(odd) {
            background: #f8f9fa;
        }
        
        table tbody tr:hover {
            background: #e9ecef;
        }
        
        .summary {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .summary p {
            margin: 5px 0;
        }
        
        footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #666;
        }
        
        .text-muted {
            color: #666;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $branch }} - Accepted Appointments Report</h1>
        <p>Generated on: {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="filters">
        <h4>Applied Filters:</h4>
        <div class="filter-item">
            <strong>Date:</strong> {{ $filterInfo['date'] }}
        </div>
        <div class="filter-item">
            <strong>Time Slot:</strong> {{ $filterInfo['time'] }}
        </div>
        <div class="filter-item">
            <strong>Category:</strong> {{ $filterInfo['category'] }}
        </div>
        <div class="filter-item">
            <strong>Search:</strong> {{ $filterInfo['search'] ?? 'No search filter' }}
        </div>
    </div>

    @if($appointments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 15%;">Client Name</th>
                    <th style="width: 12%;">Phone</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 12%;">Category</th>
                    <th style="width: 12%;">Case</th>
                    <th style="width: 13%;">Date & Time</th>
                    <th style="width: 7%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $index => $appointment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $appointment->fullname }}</strong></td>
                        <td>{{ $appointment->phone }}</td>
                        <td>{{ $appointment->email }}</td>
                        <td>{{ $appointment->category }}</td>
                        <td>{{ $appointment->case_name ?? 'N/A' }}</td>
                        <td>
                            {{ $appointment->selected_date ?? 'N/A' }}
                            <br>
                            <span class="text-muted">{{ $appointment->selected_time ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="status-approved">{{ ucfirst($appointment->appointment_approval) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <p><strong>Report Summary:</strong></p>
            <p>Total Accepted Appointments: <strong>{{ $appointments->count() }}</strong></p>
            <p>Report Scope: <strong>{{ $branch }}</strong></p>
            <p style="margin-top: 10px; color: #999; font-size: 12px;">
                This report contains all appointments matching the applied filters.
            </p>
        </div>
    @else
        <div style="text-align: center; padding: 30px; color: #666;">
            <p><strong>No appointments found</strong></p>
            <p>There are no accepted appointments matching the applied filters.</p>
        </div>
    @endif

    <footer>
        <p>LegalConnect - Accepted Appointments Report</p>
        <p>This is an automatically generated report. For more information, please contact your administrator.</p>
    </footer>
</body>
</html>
