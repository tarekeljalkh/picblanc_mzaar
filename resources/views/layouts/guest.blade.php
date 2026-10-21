<!doctype html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free" data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <!-- Dynamic Title -->
    <title>@yield('title', 'Picblanc')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    @stack('styles')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content -->
    @yield('content')
    <!-- / Content -->

    @stack('scripts')
    <script>
      document.querySelectorAll('.form-password-toggle').forEach(function (wrapper) {
        const input = wrapper.querySelector('input[type="password"], input[type="text"]');
        const toggle = wrapper.querySelector('[data-password-toggle]');

        if (!input || !toggle) return;

        toggle.addEventListener('click', function () {
          const isPassword = input.type === 'password';
          input.type = isPassword ? 'text' : 'password';
          toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
          toggle.querySelector('i')?.classList.toggle('bx-hide', !isPassword);
          toggle.querySelector('i')?.classList.toggle('bx-show', isPassword);
        });
      });
    </script>
  </body>
</html>
