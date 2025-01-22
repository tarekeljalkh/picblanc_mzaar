@extends('layouts.guest')

@section('title', 'Picblanc - Reset Password')

@section('content')
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Password Reset Card -->
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
              <h4 class="mb-1">Reset Password 🔒</h4>
              <p class="mb-6">Please enter your email and new password.</p>

              <!-- Laravel Password Reset Form -->
              <form id="formAuthentication" class="mb-6" method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Input -->
                <div class="mb-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                  @error('email')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Password Input -->
                <div class="mb-6 form-password-toggle">
                  <label for="password" class="form-label">New Password</label>
                  <div class="input-group input-group-merge">
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                  @error('password')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-6 form-password-toggle">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <div class="input-group input-group-merge">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                  @error('password_confirmation')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Submit Button -->
                <button class="btn btn-primary d-grid w-100">Reset Password</button>
              </form>
            </div>
          </div>
          <!-- /Password Reset Card -->
        </div>
      </div>
    </div>

      <!-- / Content -->

@endsection
