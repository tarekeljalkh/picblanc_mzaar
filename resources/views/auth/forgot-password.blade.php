@extends('layouts.guest')

@section('title', 'Picblanc - Forgot Password')

@section('content')
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Forgot Password Card -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-2">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <img src="{{ asset('logo.png') }}" alt="logo" width="150">
                  </span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Forgot Password? 🔒</h4>
              <p class="mb-6">Enter your email and we'll send you instructions to reset your password</p>

              <!-- Laravel Forgot Password Form -->
              <form id="formAuthentication" class="mb-6" method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus />
                  @error('email')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Submit Button -->
                <button class="btn btn-primary d-grid w-100">Send Reset Link</button>
              </form>

              <!-- Back to Login Link -->
              <div class="text-center">
                <a href="{{ route('login') }}" class="d-flex justify-content-center">
                  <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
                  Back to login
                </a>
              </div>
            </div>
          </div>
          <!-- /Forgot Password Card -->
        </div>
      </div>
    </div>

      <!-- / Content -->

@endsection
