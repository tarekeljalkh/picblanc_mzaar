@extends('layouts.guest')

@section('title', 'Picblanc - Register')

@section('content')
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Register Card -->
          <div class="card px-sm- px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-2">
                <a href="{{ url('/') }}" class="app-brand-link gap-">
                  <span class="app-brand-logo demo">
                    <img src="{{ asset('logo.png') }}" alt="logo" width="150">
                  </span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Adventure starts here 🚀</h4>
              <p class="mb-6">Make your app management easy and fun!</p>

              <!-- Laravel Registration Form -->
              <form id="formAuthentication" method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-6">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" value="{{ old('name') }}" required autofocus />
                  @error('name')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required />
                  @error('email')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Password -->
                <div class="mb-6 form-password-toggle">
                  <label class="form-label" for="password">Password</label>
                  <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                  @error('password')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required />
                  @error('password_confirmation')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Terms and Conditions -->
                <div class="my-8">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                    <label class="form-check-label" for="terms-conditions">
                      I agree to
                      <a href="javascript:void(0);">privacy policy & terms</a>
                    </label>
                  </div>
                </div>

                <!-- Register Button -->
                <button class="btn btn-primary d-grid w-100">Sign up</button>
              </form>

              <p class="text-center mt-4">
                <span>Already have an account?</span>
                <a href="{{ route('login') }}">
                  <span>Sign in instead</span>
                </a>
              </p>
            </div>
          </div>
          <!-- /Register Card -->
        </div>
      </div>
    </div>

      <!-- / Content -->

@endsection
