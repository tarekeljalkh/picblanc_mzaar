@extends('layouts.guest')

@section('title', 'Picblanc - Login')

@section('content')
   <!-- Content -->
   <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Login -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-2">
              <a href="{{ url('/') }}" class="app-brand-link gap-2">
                <img src="{{ asset('logo.png') }}" alt="logo" class="app-brand-logo" width="150">
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">Welcome! 👋</h4>
            <p class="mb-6">Please sign in to your account</p>

            <!-- Laravel Form for Login -->
            <form method="POST" action="{{ route('login') }}">
              @csrf

              <div class="mb-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus />
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>

              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password" placeholder="Enter your password" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>

              <div class="mb-8">
                <div class="d-flex justify-content-between">
                  @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                      <span>Forgot Password?</span>
                    </a>
                  @endif
                </div>
              </div>

              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
              </div>
            </form>
            @if (Route::has('register'))
            <p class="text-center">
              <span>New here?</span>
              <a href="{{ route('register') }}">
                <span>Create an account</span>
              </a>
            </p>
            @endif
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>
  </div>
  <!-- / Content -->


@endsection


