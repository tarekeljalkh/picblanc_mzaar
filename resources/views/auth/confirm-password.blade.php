@extends('layouts.guest')

@section('title', 'Picblanc - Confirm Password')

@section('content')
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Password Confirmation Card -->
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
              <h4 class="mb-1">Confirm Your Password 🔒</h4>
              <p class="mb-6">This is a secure area of the application. Please confirm your password before continuing.</p>

              <!-- Laravel Password Confirmation Form -->
              <form id="formAuthentication" class="mb-6" method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password Input -->
                <div class="mb-6">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required autocomplete="current-password" />
                  @error('password')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>

                <!-- Submit Button -->
                <button class="btn btn-primary d-grid w-100">Confirm</button>
              </form>
            </div>
          </div>
          <!-- /Password Confirmation Card -->
        </div>
      </div>
    </div>

      <!-- / Content -->

@endsection
