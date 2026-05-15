<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalize Appointment</title>
    <link rel="stylesheet" href="{{ asset('css/FinalizeAppointment.blade.css') }}">

    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')

</head>
<body>
    <div class="container">
        <header>
            <h1>Finalize Appointment</h1>
        </header>
        
        <div class="content">
            <!-- Error/Success Messages -->
            @if($errors->any())
                <div class="alert alert-error">
                    <h3>Please fix the following errors:</h3>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

           <div class="appointment-details">
                <h2>Appointment Details</h2>
                <p><strong>Full Name:</strong> {{ $fullname }}</p>
                <p><strong>Address:</strong> {{ $address }}</p>
                <p><strong>Phone:</strong> {{ $phone }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Category:</strong> {{ $selected_category ?? 'Not specified' }}</p>
                <p><strong>Case Type:</strong> {{ $selected_case_name ?? 'Not specified' }}</p>
                <!-- Add Selected Branch Display -->
                <p><strong>Selected Office:</strong> {{ $selected_branch ?? session('branch') ?? 'Not specified' }}</p>
                <p><strong>Terms Approval:</strong> 
                    <span class="terms-status {{ $status_approval == 'approved' ? 'terms-approved' : 'terms-pending' }}">
                        {{ ucfirst($status_approval == 'approved' ? 'accepted' : 'pending') }}
                    </span>
                </p>
                <p><strong>Selected Date:</strong> {{ $date }}</p>
                <p><strong>Selected Time:</strong> {{ $time }}</p>
            </div>
            <form id="finalizeForm" action="{{ route('appointment.finalize') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="fullname" value="{{ $fullname }}">
                <input type="hidden" name="address" value="{{ $address }}">
                <input type="hidden" name="phone" value="{{ $phone }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="category" value="{{ $selected_category }}">
                <input type="hidden" name="case_name" value="{{ $selected_case_name }}">
                <!-- Add hidden input for branch with value -->
                <input type="hidden" name="selected_branch" id="selected_branch" value="{{ $selected_branch }}">

                <strong>Selected Branch:</strong>
                <span id="selectedBranchText">{{ $selected_branch }}</span>
                <input type="hidden" name="selected_date" value="{{ $date }}">
                <input type="hidden" name="selected_time" value="{{ $time }}">
                <input type="hidden" name="term_status" value="{{ $status_approval ?? 'Approved' }}">
                            
                <div class="upload-section">
                    <h2>Identification Documents</h2>
                    
                    <!-- Upload ID Front -->
                    <label for="id_front">Upload ID Front (Max 2MB)</label>
                    <input type="file" id="id_front" name="id_front" accept="image/jpeg,image/png,image/jpg" required>
                    <div class="compression-info" id="frontSizeInfo"></div>
                    @error('id_front')
                        <div class="validation-error">{{ $message }}</div>
                    @enderror
                    <div class="preview-container">
                        <img id="preview_front" alt="Front ID preview">
                        <button type="button" class="btn btn-remove" onclick="resetImage('id_front','preview_front')">Remove Front Image</button>
                    </div>
                    
                    <!-- Upload ID Back -->
                    <label for="id_back">Upload ID Back (Max 2MB)</label>
                    <input type="file" id="id_back" name="id_back" accept="image/jpeg,image/png,image/jpg">
                    <div class="compression-info" id="backSizeInfo"></div>
                    @error('id_back')
                        <div class="validation-error">{{ $message }}</div>
                    @enderror
                    <div class="preview-container">
                        <img id="preview_back" alt="Back ID preview">
                        <button type="button" class="btn btn-remove" onclick="resetImage('id_back','preview_back')">Remove Back Image</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-submit" id="submitBtn">Submit Appointment</button>
                <button type="button" class="btn btn-back" onclick="cancelAndBack()">Back</button>
            </form>
            
        </div>
    </div>

     <script src="{{ asset('js/FinalizeAppointment.js') }}"></script>
     <script>
        const backUrl = "{{ route('getsched') }}";
    </script>
</body>
</html>