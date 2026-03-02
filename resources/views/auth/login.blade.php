<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <title>PSG Ordering System</title>
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
 
  <!-- css template -->
  <link href="https://order.psgtso.online/bootstrap/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="https://order.psgtso.online/bootstrap/css/all.min.css" rel="stylesheet">
  <link href="https://order.psgtso.online/bootstrap/css/tokenize2.min.css" rel="stylesheet">  
  <!-- end css template -->

</head>

<body id="page-top" style="background-image: linear-gradient(#086972, #086972, #17b978);">
<div class="container" style="margin-top: 130px;">
    <!-- Outer Row -->
    <div class="row justify-content-center" id="login_container">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <!-- PSG LOGO -->
              <div class="col-lg-6 d-none d-lg-block bg-login-image shadow-lg p-0">
                <div class="container transbox"></div>
                <img src="/images/system_img.jpg" class="centered" style="width:100%;height:100%">
              </div>
              <!-- LOG IN FORM -->
              <div class="col-lg-6">
                <div style="padding:80px;">
                  <div class="text-center">
                    <h1 class="h4 text-secondary mb-2 mt-2">Welcome!</h1>
                    <h1 class="h4 text-success mb-4">PSG ORDERING SYSTEM</h1>
                  </div>
  
                  <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <input id="email" type="email" class="form-control text-center" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <input id="password" type="password" class="form-control text-center" placeholder="Password" name="password" required>
                    </div>
                    <button type="submit" id="login_button" class="btn btn-success btn-user btn-block">
                      Login
                    </button>
                    <div class="text-left mb-2">
                      @error('email')
                        <small class="text-danger">{{ $message }}</small>
                      @enderror
                      @error('password')
                        <small class="text-danger">{{ $message }}</small>
                      @enderror
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- js template -->
  <script src="https://order.psgtso.online/bootstrap/js/jquery.min.js"></script>
  <script src="https://order.psgtso.online/bootstrap/js/bootstrap.min.js"></script>
  <script src="https://order.psgtso.online/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://order.psgtso.online/bootstrap/js/jquery.easing.min.js"></script>
  <script src="https://order.psgtso.online/bootstrap/js/sb-admin-2.min.js"></script>
  <script src="https://order.psgtso.online/bootstrap/js/tokenize2.min.js"></script>
  <!-- end of js template -->

</body>

</html>
