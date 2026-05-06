@extends('layouts.app')
@section('title', 'Create Account')
@section('content')
<div style="max-width:420px;margin:72px auto;padding:0 24px;">
    <div style="text-align:center;margin-bottom:36px;">
        <div class="serif" style="font-size:32px;font-weight:400;margin-bottom:8px;">Create Account</div>
        <p style="color:var(--stone);font-size:14px;">Join DrapeStore</p>
    </div>

    <div style="background:white;border:1px solid var(--sand);padding:36px;">
        @if($errors->any())
            <div class="flash flash-error" style="margin-bottom:20px;">
                @foreach($errors->all() as $err) <div>• {{ $err }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.register.post') }}">
            @csrf
            <div style="margin-bottom:18px;">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" required value="{{ old('name') }}" placeholder="Jane Smith">
            </div>
            <div style="margin-bottom:18px;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required value="{{ old('email') }}" placeholder="you@example.com">
            </div>
            <div style="margin-bottom:18px;">
                <label class="form-label">Password (min 12 chars)</label>
                <input type="password" name="password" class="form-input" required placeholder="At least 12 characters">
                <p style="font-size:11px;color:var(--stone);margin-top:6px;">Must include uppercase, lowercase, number, and special char</p>
            </div>
            <div style="margin-bottom:24px;">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:14px;text-align:center;">Create Account</button>
        </form>

        <p style="text-align:center;font-size:13px;color:var(--stone);margin-top:20px;">
            Already have an account? <a href="{{ route('auth.login') }}" style="color:var(--bark);">Sign in</a>
        </p>
    </div>
</div>
@endsection
