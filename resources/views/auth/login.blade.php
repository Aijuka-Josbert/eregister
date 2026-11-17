@extends('layouts.guest')

@section('title', 'Staff Sign In — LMO Digital Records Management System')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="lmo-card overflow-hidden">
            <div class="p-4 text-white" style="background-color: var(--lmo-deep-navy); border-bottom: 3px solid var(--lmo-gold-accent);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge mb-2" style="background-color: var(--lmo-civic-blue);">Authorized Staff Access</span>
                        <h2 class="h5 fw-bold mb-1">Sign In to LMO DRMS</h2>
                        <p class="small text-white-50 mb-0">Enter your institutional credentials to access the digital register.</p>
                    </div>
                    <i class="bi bi-file-earmark-lock2 fs-1" style="color: var(--lmo-gold-accent);"></i>
                </div>
            </div>

            <div class="p-4">
                @if(session('status'))
                    <div class="alert alert-info small py-2">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger small py-2">
                        @foreach($errors->all() as $error)
                            <div><i class="bi bi-exclamation-circle me-1"></i> {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Official Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
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
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter password"
                                required
                            >
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small text-muted" for="remember">
                                Keep session active on this workstation
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lmo-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Register
                    </button>
                </form>
            </div>

            <!-- Prototype Demonstration Quick-Fill Accounts -->
            <div class="bg-light border-top p-3">
                <div class="fw-bold small text-uppercase mb-2" style="color: var(--lmo-deep-navy); font-size: 0.72rem; letter-spacing: 0.05em;">
                    <i class="bi bi-display me-1"></i> Prototype Demonstration Accounts (Password: <code>Lmo@2026!</code>)
                </div>
                <div class="row g-1 small">
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" onclick="document.getElementById('email').value='admin@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                            <strong>System Admin</strong><br><span class="text-muted" style="font-size:0.7rem;">admin@lmo.go.ug</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" onclick="document.getElementById('email').value='manager@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                            <strong>Records Manager</strong><br><span class="text-muted" style="font-size:0.7rem;">manager@lmo.go.ug</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" onclick="document.getElementById('email').value='officer@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                            <strong>Records Officer</strong><br><span class="text-muted" style="font-size:0.7rem;">officer@lmo.go.ug</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" onclick="document.getElementById('email').value='executive@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                            <strong>Office Manager</strong><br><span class="text-muted" style="font-size:0.7rem;">executive@lmo.go.ug</span>
                        </button>
                    </div>
                    <div class="col-12 mt-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start py-1" onclick="document.getElementById('email').value='readonly@lmo.go.ug'; document.getElementById('password').value='Lmo@2026!';">
                            <strong>Read-Only Staff</strong> — <span class="text-muted" style="font-size:0.7rem;">readonly@lmo.go.ug (Restricted from Confidential records)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
