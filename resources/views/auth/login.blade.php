<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="icon" type="favicon" href="{{ asset('mazer/images/favIcon.PNG') }}" />
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="{{ asset('mazer/css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/choices.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/sweetalert2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/extra-component-sweetalert.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/dataTables.bootstrap5.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/datatable.css') }}">
  <link rel="stylesheet" href="{{ asset('mazer/css/iconly.css') }}">

  <style>
    body {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
      font-family: 'Poppins', sans-serif;
    }

    .login-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 380px;
      padding: 2rem;
    }

    .login-card h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 600;
    }

    .login-card .form-control {
      border-radius: 6px;
      font-size: 14px;
    }

    .login-card .btn-primary {
      width: 100%;
      margin-top: 1rem;
      font-weight: 500;
      border-radius: 6px;
    }

    .login-card small {
      display: block;
      text-align: center;
      margin-top: 1.2rem;
      color: #6c757d;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .login-card {
        width: 95%;
        max-width: 350px;
        padding: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 1rem;
        max-width: 320px;
      }
      .login-card h2 {
        font-size: 1.3rem;
      }
      label {
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>

  <div class="login-card">
    <h2>Sign in</h2>

    @if ($errors->any())
      <div class="alert alert-danger py-1 mb-2" role="alert">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
      @csrf
      <div class="mb-2">
        <label for="username">Username</label>
        <input id="username" name="username" class="form-control form-control-sm" type="text" value="{{ old('username') }}" autofocus>
      </div>

      <div class="mb-2">
        <label for="password">Password</label>
        <input id="password" name="password" class="form-control form-control-sm" type="password">
      </div>

      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
        <label class="form-check-label" for="remember">Remember me</label>
      </div>

      <button type="submit" class="btn btn-primary btn-sm">Login</button>
      <small>Powered by Somict-Tech ©<?php echo date('Y'); ?></small>
    </form>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('mazer/js/jquery.js') }}"></script>
  <script src="{{ asset('mazer/js/sweetalert2.min.js') }}"></script>
  <script>
     const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: { popup: 'small-toast-popup' },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function showAlert(text, type) {
        Toast.fire({ icon: type, title: text });
    }

    $(function () {
      $('form').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let btn  = form.find('button');
        let data = form.serialize();

        btn.prop('disabled', true).text('Signing in...');

        $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: data,
          success: function (res) {
            if (res.status === 200) {
              showAlert(res.message || 'Login successful!', "success");
              window.location.href = res.redirect;
            } else {
              showAlert(res.message || 'Something went wrong.', "error");
            }
          },
          error: function (xhr) {
            let msg = xhr.responseJSON?.message || 'Server error. Please try again.';
            showAlert(msg, "error");
          },
          complete: function () {
            btn.prop('disabled', false).text('Login');
          }
        });
      });
    });
  </script>
</body>
</html>
