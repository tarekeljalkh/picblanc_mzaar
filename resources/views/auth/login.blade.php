@extends('layouts.guest')

@section('title', 'Picblanc - Login')

@push('styles')
<style>
    .login-page {
        min-height: 100svh;
        display: grid;
        place-items: center;
        padding: 2rem 1rem;
        background:
            radial-gradient(circle at 8% 12%, rgba(105, 108, 255, .24), transparent 32%),
            radial-gradient(circle at 92% 88%, rgba(3, 195, 236, .18), transparent 30%),
            #f5f7fb;
    }

    .login-card {
        width: min(100%, 980px);
        display: grid;
        grid-template-columns: minmax(280px, .9fr) minmax(320px, 1.1fr);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: 1.5rem;
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 1.5rem 4rem rgba(67, 89, 113, .14);
        backdrop-filter: blur(14px);
    }

    .login-intro {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 560px;
        padding: 3rem;
        overflow: hidden;
        color: #fff;
        background: linear-gradient(145deg, #696cff 0%, #4b4fd7 55%, #30349b 100%);
    }

    .login-intro::before,
    .login-intro::after {
        position: absolute;
        content: '';
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 50%;
    }

    .login-intro::before {
        width: 360px;
        height: 360px;
        right: -140px;
        bottom: -110px;
    }

    .login-intro::after {
        width: 210px;
        height: 210px;
        right: 35px;
        bottom: 25px;
    }

    .login-logo {
        position: relative;
        z-index: 1;
        width: 150px;
        height: auto;
        filter: brightness(0) invert(1);
    }

    .login-intro-content {
        position: relative;
        z-index: 1;
        max-width: 360px;
    }

    .login-intro h1 {
        margin-bottom: 1rem;
        color: #fff;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        line-height: 1.1;
    }

    .login-intro p {
        margin: 0;
        color: rgba(255, 255, 255, .78);
        font-size: 1rem;
        line-height: 1.7;
    }

    .login-form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 3rem clamp(1.5rem, 5vw, 4.5rem);
    }

    .login-form h2 {
        margin-bottom: .5rem;
        color: #384551;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .login-form .subtitle {
        margin-bottom: 2rem;
        color: #8592a3;
    }

    .login-form .form-label {
        color: #566a7f;
        font-weight: 600;
    }

    .login-form .form-control {
        min-height: 3.1rem;
        border-radius: .75rem;
        border-color: #d9dee3;
    }

    .login-form .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 .2rem rgba(105, 108, 255, .14);
    }

    .login-form .input-group-text {
        cursor: pointer;
        border-color: #d9dee3;
        border-radius: 0 .75rem .75rem 0;
        background: #fff;
    }

    .login-submit {
        min-height: 3.1rem;
        border: 0;
        border-radius: .75rem;
        font-weight: 600;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .login-submit:not(:disabled):hover {
        transform: translateY(-1px);
        box-shadow: 0 .5rem 1rem rgba(105, 108, 255, .22);
    }

    .login-submit .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: .15em;
    }

    @media (max-width: 767.98px) {
        .login-page {
            padding: 0;
        }

        .login-card {
            min-height: 100svh;
            grid-template-columns: 1fr;
            border: 0;
            border-radius: 0;
        }

        .login-intro {
            min-height: 235px;
            padding: 2rem 1.5rem;
        }

        .login-intro h1 {
            font-size: 2rem;
        }

        .login-intro p {
            display: none;
        }

        .login-form {
            justify-content: flex-start;
            padding: 2.25rem 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<main class="login-page">
    <div class="login-card">
        <section class="login-intro" aria-label="Picblanc introduction">
            <a href="{{ url('/') }}" aria-label="Picblanc home">
                <img src="{{ asset('logo.png') }}" alt="Picblanc" class="login-logo">
            </a>

            <div class="login-intro-content">
                <h1>Rental operations, made simple.</h1>
                <p>Manage customers, products, invoices, and daily or seasonal rentals from one clear workspace.</p>
            </div>
        </section>

        <section class="login-form">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to continue to your workspace.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="bx bx-error-circle me-1"></i>
                    Please check your email and password and try again.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label">Email address</label>
                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-password-toggle">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-merge">
                        <input
                            type="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="input-group-text" data-password-toggle aria-label="Show password">
                            <i class="bx bx-hide"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" name="remember">
                        <span class="form-check-label">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                    @endif
                </div>

                <button class="btn btn-primary d-grid w-100 login-submit" type="submit" id="login-submit">
                    <span class="login-submit-label">Sign in</span>
                    <span class="spinner-border text-white d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>

            @if (Route::has('register'))
                <p class="text-center mt-4 mb-0 text-muted">
                    New here?
                    <a href="{{ route('register') }}">Create an account</a>
                </p>
            @endif
        </section>
    </div>
</main>

<script>
    document.getElementById('login-form')?.addEventListener('submit', function () {
        const button = document.getElementById('login-submit');
        const label = button?.querySelector('.login-submit-label');
        const spinner = button?.querySelector('.spinner-border');

        if (!button) return;

        button.disabled = true;
        if (label) label.textContent = 'Signing in…';
        spinner?.classList.remove('d-none');
    });
</script>
@endsection
