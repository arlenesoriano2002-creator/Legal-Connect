{{-- Base Layout with Global Error Handler --}}
{{-- This layout provides consistent error handling across the application --}}
{{-- using Toastr notifications matching the cordon logbook style --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Legal Connect')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    {{-- Custom styles --}}
    @yield('styles')

    {{-- Global Error Handler --}}
    @include('partials.global-error-handler')
</head>
<body>
    {{-- Navigation --}}
    @yield('nav')

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @yield('footer')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom scripts --}}
    @yield('scripts')
</body>
</html>
