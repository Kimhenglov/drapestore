{{-- resources/views/checkout/success.blade.php --}}
@extends('layouts.app')
@section('title', 'Order Confirmed')
@section('content')
<div style="max-width:640px;margin:80px auto;padding:0 24px;text-align:center;">
    <div style="font-size:64px;margin-bottom:20px;">🎉</div>
    <h1 class="serif" style="font-size:36px;font-weight:400;margin-bottom:12px;">Order Confirmed!</h1>
    <p style="color:var(--stone);font-size:15px;margin-bottom:32px;">
        Thank you for your purchase. A confirmation email will be sent shortly.
    </p>

    @if($order)
    <div style="background:white;border:1px solid var(--sand);padding:28px;text-align:left;margin-bottom:28px;">
        <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);margin-bottom:16px;">Order Details</div>
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
            <span style="color:var(--bark);">Order ID</span>
            <span>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
            <span style="color:var(--bark);">Total Paid</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:8px;">
            <span style="color:var(--bark);">Status</span>
            <span style="color:#15803d;font-weight:500;">✓ Paid</span>
        </div>
        {{-- REQ 3: Show masked reference only (not card number) --}}
        <div style="display:flex;justify-content:space-between;font-size:14px;">
            <span style="color:var(--bark);">Payment Reference</span>
            <span style="font-family:monospace;font-size:12px;">{{ substr($order->stripe_payment_id, 0, 12) }}...</span>
        </div>
    </div>
    @endif

    <a href="{{ route('shop.index') }}" class="btn-primary">Continue Shopping</a>
</div>
@endsection
