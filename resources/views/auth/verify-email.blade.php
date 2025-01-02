@extends('layouts.guest')

@section('title', 'Picblanc - Verify Email')

@section('content')
    <!-- Content -->

    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <!-- Email Verification Card -->
          <div class="card px-sm-6 px-0">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-6">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <img src="{{ asset('logo.png') }}" alt="logo" width="150">
                  </span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1">Verify Your Email Address</h4>
              <p class="mb-4">Thanks for signing up! Please verify your email address by clicking the link we just emailed to you. If you didn’t receive the email, we can send another.</p>

              <!-- Display success message if the verification link is resent -->
              @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success mb-4">
                  A new verification link has been sent to the email address you provided during registration.
                </div>
              @endif

              <!-- Resend Verification Link Form -->
              <form id="resendVerificationForm" class="mb-4" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-primary d-grid w-100" type="submit">Resend Verification Email</button>
              </form>

              <!-- Logout Form -->
              <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary d-grid w-100">Log Out</button>
              </form>

            </div>
          </div>
          <!-- /Email Verification Card -->
        </div>
      </div>
    </div>

    <!-- / Content -->
@endsection
