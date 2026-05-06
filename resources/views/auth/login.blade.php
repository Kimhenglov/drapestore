{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div style="max-width:420px;margin:72px auto;padding:0 24px;">
    <div style="text-align:center;margin-bottom:36px;">
        <div class="serif" style="font-size:32px;font-weight:400;margin-bottom:8px;">Welcome back</div>
        <p style="color:var(--stone);font-size:14px;">Sign in to your DrapeStore account</p>
    </div>

    <div style="background:white;border:1px solid var(--sand);padding:36px;">
        @if($errors->any())
            <div class="flash flash-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('auth.login.post') }}">
            @csrf
            <div style="margin-bottom:18px;">
                <label class="form-label">Email Address</label>
                {{-- REQ 8.2.1: Unique identifier (email) for each user --}}
                <input type="email" name="email" class="form-input" required autocomplete="email"
                    value="{{ old('email') }}" placeholder="you@example.com">
            </div>
            <div style="margin-bottom:24px;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required autocomplete="current-password" placeholder="••••••••••••">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:14px;text-align:center;">Sign In</button>
        </form>

        <p style="text-align:center;font-size:13px;color:var(--stone);margin-top:20px;">
            Don't have an account? <a href="{{ route('auth.register') }}" style="color:var(--bark);">Register</a>
        </p>

        {{-- Test credentials hint --}}


        <!-- <div style="margin-top:20px;padding:12px;background:#f8f7f5;border:1px solid var(--sand);font-size:12px;color:var(--stone);">
            <strong>Admin test:</strong> admin@drapestore.com / Admin@123456<br>
            <strong>Customer test:</strong> customer@test.com / Customer@123456
        </div> -->
            
    </div>

    {{-- REQ 8 explanation for assignment --}}
    <div style="margin-top:16px;padding:14px;background:rgba(196,132,60,0.08);border:1px solid rgba(196,132,60,0.2);font-size:12px;color:var(--bark);">
        <strong>PCI REQ 8 Controls active on this page:</strong><br>
        • Max 5 failed attempts → 30 min lockout<br>
        • Session expires after 15 min idle<br>
        • Every attempt is logged with timestamp + IP
    </div>
</div>
@endsection
