@extends('layouts.guest')

@section('title', "Sign In — Lord Mayor's Office Records Registry")

@section('content')
<div class="lmo-login-shell">
    <section class="lmo-login-brand">
        <div>
            <x-lmo-crest :size="72" />
        </div>
        <div>
            <h1>Lord Mayor's Office</h1>
            <div class="sub">Records Registry</div>
        </div>
        <p>Register each document as it arrives, keep its scan, and see who handled it.</p>
    </section>

    <section class="lmo-login-form-wrap">
        <div class="lmo-login-card">
            <div>
                <h2>Sign in</h2>
                <p class="muted mb-0 mt-1" style="font-size: 14px;">Sign In to LMO DRMS with your official staff account.</p>
            </div>

            @if(session('status'))
                <div class="lmo-alert ok mb-0">
                    <i class="bi bi-check-circle"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="lmo-alert bad mb-0">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                <div class="lmo-fld">
                    <label for="email">Official email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control"
                        placeholder="name@lmo.go.ug"
                        required
                        autofocus
                    >
                </div>

                <div class="lmo-fld">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label muted" style="font-size: 13.5px;" for="remember">
                        Keep me signed in on this workstation
                    </label>
                </div>

                <button type="submit" class="btn-lmo-primary w-100" style="min-height: 44px;">
                    <i class="bi bi-lock"></i> Enter the registry
                </button>
            </form>

            <div class="lmo-login-note">
                <i class="bi bi-clock mt-1"></i>
                <span>Every registration, update, opened scan, and archive action is recorded against your name.</span>
            </div>

            <details class="lmo-demo-toggle">
                <summary>
                    <span>Demonstration accounts</span>
                    <i class="bi bi-chevron-down"></i>
                </summary>
                <div class="muted mt-2" style="font-size: 12.5px;">
                    Click an account to fill credentials (password: <code>Lmo@2026!</code>)
                </div>
                <div class="lmo-demo-grid">
                    <button type="button" class="lmo-demo-btn" onclick="document.getElementById('email').value='admin@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                        <strong>System Admin</strong>
                        <span>admin@lmo.go.ug</span>
                    </button>
                    <button type="button" class="lmo-demo-btn" onclick="document.getElementById('email').value='manager@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                        <strong>Records Manager</strong>
                        <span>manager@lmo.go.ug</span>
                    </button>
                    <button type="button" class="lmo-demo-btn" onclick="document.getElementById('email').value='officer@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                        <strong>Records Officer</strong>
                        <span>officer@lmo.go.ug</span>
                    </button>
                    <button type="button" class="lmo-demo-btn" onclick="document.getElementById('email').value='executive@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                        <strong>Office Manager</strong>
                        <span>executive@lmo.go.ug</span>
                    </button>
                    <button type="button" class="lmo-demo-btn" style="grid-column: 1 / -1;" onclick="document.getElementById('email').value='readonly@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                        <strong>Read-Only Staff</strong>
                        <span>readonly@lmo.go.ug — Restricted from Confidential records</span>
                    </button>
                </div>
            </details>
        </div>
    </section>
</div>
@endsection
