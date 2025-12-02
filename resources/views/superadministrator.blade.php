<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('KG2025 (2).png') }}" type="image/png">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/admindashboard.blade.css') }}">
</head>
<body>
     <div class="container">
    <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">
      <div>
        <div class="logo-container">
          <img
            src="KG2025 (2).png"
            alt="LegalConnect logo with black background and golden scales of justice icon"
            width="80"
            height="80"
          />
          <p>LegalConnect</p>
        </div>
        <nav>
          <a href="{{ url('/admindashboard')}}"class="active" tabindex="0">Dashboard</a>
          <a href="" > Log ins Info</a>
          <a href="" tabindex="0">Clients</a>
          <a href="" tabindex="0">Banned Accounts</a>
          <a href="" tabindex="0">Account</a>
        </nav>
      </div>
      
    </aside>
    <main>
      <div class="top-bar" role="banner">
        
        <div class="adminSign">Super-Administrator Dashboard</div>
      <div>
          <form id="logout-form" action="{{ route('custom.logout') }}" method="POST" style="display: none;">
              @csrf
          </form>
          <button type="button" class="btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt"></i> Log out
          </button>
      </div>
        
    </main>
  </div>



</body>
</html>