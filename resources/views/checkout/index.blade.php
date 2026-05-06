@extends('layouts.app')
@section('title', 'Secure Checkout')

@section('content')
<style>
    /* ──────── Modern checkout styling ──────── */
    .checkout-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 24px 80px;
    }

    .checkout-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--sand);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .back-link {
        color: var(--bark);
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }
    .back-link:hover { color: var(--ink); }

    .checkout-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        font-weight: 400;
    }

    .secure-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #bbf7d0;
        color: #15803d;
        padding: 8px 14px;
        border-radius: 24px;
        font-size: 12px;
        font-weight: 500;
    }

    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 48px;
        align-items: start;
    }

    /* ──────── Steps ──────── */
    .step-card {
        background: white;
        border: 1px solid var(--sand);
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .step-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--ink);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .step-title {
        font-size: 16px;
        font-weight: 500;
        color: var(--ink);
    }

    .step-desc {
        font-size: 12px;
        color: var(--stone);
        margin-top: 2px;
    }

    /* ──────── Form fields ──────── */
    .field-group {
        margin-bottom: 18px;
    }

    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 18px;
    }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: var(--bark);
        margin-bottom: 8px;
        letter-spacing: 0.05em;
    }

    .field-input {
        width: 100%;
        border: 1.5px solid #e5e0d6;
        background: white;
        padding: 12px 14px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--ink);
        border-radius: 8px;
        outline: none;
        transition: all 0.2s;
    }
    .field-input:focus {
        border-color: var(--ink);
        box-shadow: 0 0 0 3px rgba(28,26,24,0.08);
    }
    .field-input::placeholder { color: #c4b8a8; }

    /* ──────── Stripe element ──────── */
    #stripe-element {
        border: 1.5px solid #e5e0d6;
        background: white;
        padding: 14px;
        border-radius: 8px;
        transition: all 0.2s;
        min-height: 44px;
    }
    #stripe-element.StripeElement--focus {
        border-color: var(--ink);
        box-shadow: 0 0 0 3px rgba(28,26,24,0.08);
    }
    #stripe-element.StripeElement--invalid {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.08);
    }

    .card-error {
        color: #dc2626;
        font-size: 13px;
        margin-top: 8px;
        min-height: 18px;
    }

    /* ──────── Test card hint ──────── */
    .test-hint {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
        padding: 14px 16px;
        font-size: 13px;
        color: #92400e;
        margin-top: 16px;
        margin-bottom: 24px;
        border-radius: 8px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .test-hint code {
        background: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'DM Mono', monospace;
        font-weight: 500;
    }

    /* ──────── Pay button ──────── */
    .pay-btn {
        width: 100%;
        background: var(--ink);
        color: white;
        border: none;
        padding: 16px;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
        font-family: 'DM Sans', sans-serif;
    }
    .pay-btn:hover:not(:disabled) {
        background: var(--bark);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .pay-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: inline-block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .terms-note {
        font-size: 12px;
        color: var(--stone);
        text-align: center;
        margin-top: 16px;
        line-height: 1.6;
    }

    /* ──────── Order summary ──────── */
    .summary-card {
        background: white;
        border: 1px solid var(--sand);
        border-radius: 12px;
        padding: 28px;
        position: sticky;
        top: 90px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .summary-title {
        font-family: 'Playfair Display', serif;
        font-size: 22px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--sand);
    }

    .summary-item {
        display: flex;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px dashed #e8e0d4;
    }
    .summary-item:last-of-type { border-bottom: none; }

    .summary-img-wrap {
        position: relative;
        flex-shrink: 0;
    }
    .summary-img {
        width: 60px;
        height: 76px;
        object-fit: cover;
        border-radius: 6px;
        background: var(--sand);
    }
    .qty-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: var(--ink);
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .summary-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .summary-name { font-size: 14px; line-height: 1.3; margin-bottom: 4px; }
    .summary-meta { font-size: 12px; color: var(--stone); }
    .summary-price { font-size: 14px; font-weight: 500; align-self: center; }

    .summary-totals {
        margin-top: 8px;
        padding-top: 16px;
        border-top: 2px solid var(--sand);
    }
    .total-line {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin-bottom: 10px;
        color: var(--bark);
    }
    .total-grand {
        display: flex;
        justify-content: space-between;
        font-size: 20px;
        font-weight: 600;
        color: var(--ink);
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--sand);
    }

    .security-list {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--sand);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .security-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: var(--bark);
    }
    .security-icon {
        width: 18px; height: 18px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #15803d;
        flex-shrink: 0;
    }

    @media (max-width: 900px) {
        .checkout-grid { grid-template-columns: 1fr; }
        .field-row { grid-template-columns: 1fr; }
        .summary-card { position: static; }
    }
</style>

<div class="checkout-wrap">

    {{-- Header --}}
    <div class="checkout-header">
        <div class="header-left">
            <a href="{{ route('cart.index') }}" class="back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Cart
            </a>
            <h1 class="checkout-title">Secure Checkout</h1>
        </div>
        <div class="secure-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            256-bit TLS Encrypted
        </div>
    </div>

    <div class="checkout-grid">

        {{-- ──────── LEFT: FORM ──────── --}}
        <div>
            <form id="payment-form" action="{{ route('checkout.process') }}" method="POST">
                @csrf
                {{-- REQ 3: Hidden field receives Stripe TOKEN, not card number --}}
                <input type="hidden" name="payment_method_id" id="payment_method_id">
                <input type="hidden" name="country" value="US">

                {{-- ── STEP 1: Shipping ── --}}
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <div>
                            <div class="step-title">Shipping Information</div>
                            <div class="step-desc">Where should we deliver your order?</div>
                        </div>
                    </div>

                    <div class="field-row">
                        <div>
                            <label class="field-label">Full Name *</label>
                            <input type="text" name="name" class="field-input" required
                                value="{{ old('name', Auth::user()?->name) }}"
                                placeholder="Jane Smith" autocomplete="name">
                        </div>
                        <div>
                            <label class="field-label">Email Address *</label>
                            <input type="email" name="email" class="field-input" required
                                value="{{ old('email', Auth::user()?->email) }}"
                                placeholder="jane@example.com" autocomplete="email">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Shipping Address *</label>
                        <input type="text" name="address" class="field-input" required
                            placeholder="123 Main Street, Apt 4B"
                            value="{{ old('address') }}" autocomplete="street-address">
                    </div>

                    <div class="field-row">
                        <div>
                            <label class="field-label">City *</label>
                            <input type="text" name="city" class="field-input" required
                                placeholder="New York" value="{{ old('city') }}">
                        </div>
                        <div>
                            <label class="field-label">Postal Code *</label>
                            <input type="text" name="postal_code" class="field-input" required
                                placeholder="10001" value="{{ old('postal_code') }}">
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: Payment ── --}}
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <div>
                            <div class="step-title">Payment Details</div>
                            <div class="step-desc">Your card is processed securely by Stripe</div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Card Information *</label>
                        {{-- Stripe.js mounts a secure iframe inside this div --}}
                        <div id="stripe-element"></div>
                        <div id="card-error" class="card-error" role="alert"></div>
                    </div>

                    <div class="test-hint">
                        <span style="font-size:18px;">💡</span>
                        <div>
                            <strong>Test Mode</strong> — Use this fake card for testing:<br>
                            <code>4242 4242 4242 4242</code> · Any future date · Any 3-digit CVC
                        </div>
                    </div>

                    <button type="submit" id="pay-btn" class="pay-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="btn-icon">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <span id="btn-text">Pay ${{ number_format($total['total'], 2) }} Securely</span>
                    </button>

                    <p class="terms-note">
                        By completing this purchase, you agree to our Terms of Service.<br>
                        🔒 Your card details are never stored on our servers (PCI DSS REQ 3)
                    </p>
                </div>
            </form>
        </div>

        {{-- ──────── RIGHT: ORDER SUMMARY ──────── --}}
        <div>
            <div class="summary-card">
                <div class="summary-title">Order Summary</div>

                @foreach($cart as $item)
                <div class="summary-item">
                    <div class="summary-img-wrap">
                        <img src="{{ $item['image_url'] }}" class="summary-img" alt="{{ $item['name'] }}">
                        <span class="qty-badge">{{ $item['quantity'] }}</span>
                    </div>
                    <div class="summary-info">
                        <div class="summary-name">{{ $item['name'] }}</div>
                        <div class="summary-meta">Size: {{ $item['size'] }}</div>
                    </div>
                    <div class="summary-price">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                </div>
                @endforeach

                <div class="summary-totals">
                    <div class="total-line">
                        <span>Subtotal</span>
                        <span>${{ number_format($total['subtotal'], 2) }}</span>
                    </div>
                    <div class="total-line">
                        <span>Tax (7%)</span>
                        <span>${{ number_format($total['tax'], 2) }}</span>
                    </div>
                    <div class="total-line">
                        <span>Shipping</span>
                        <span style="color:#15803d;">Free</span>
                    </div>
                    <div class="total-grand">
                        <span>Total</span>
                        <span>${{ number_format($total['total'], 2) }}</span>
                    </div>
                </div>

                <div class="security-list">
                    <div class="security-item">
                        <span class="security-icon">✓</span>
                        <span>256-bit TLS encryption (REQ 4)</span>
                    </div>
                    <div class="security-item">
                        <span class="security-icon">✓</span>
                        <span>Card data never stored (REQ 3)</span>
                    </div>
                    <div class="security-item">
                        <span class="security-icon">✓</span>
                        <span>PCI DSS v4.0 compliant</span>
                    </div>
                    <div class="security-item">
                        <span class="security-icon">✓</span>
                        <span>Powered by Stripe</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════ --}}
{{--   STRIPE.JS — Loaded directly here (no @push needed!)        --}}
{{--   PCI REQ 6.4.3: Loaded only from official Stripe CDN        --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Verify Stripe loaded
    if (typeof Stripe === 'undefined') {
        document.getElementById('card-error').textContent =
            '⚠️ Stripe.js failed to load. Check your internet connection and disable any ad-blocker.';
        return;
    }

    // REQ 4: Initialize Stripe with publishable key
    const stripe = Stripe('{{ $stripe_key }}');

    // Verify the API key is set
    if (!'{{ $stripe_key }}' || '{{ $stripe_key }}'.indexOf('pk_') !== 0) {
        document.getElementById('card-error').textContent =
            '⚠️ Stripe publishable key is missing. Check your .env file.';
        return;
    }

    // Create Stripe Elements
    const elements = stripe.elements();

    // Build the secure card input
    // REQ 3: Card data goes directly to Stripe — never our server
    const card = elements.create('card', {
        style: {
            base: {
                color: '#1C1A18',
                fontFamily: '"DM Sans", sans-serif',
                fontSize: '15px',
                fontSmoothing: 'antialiased',
                '::placeholder': { color: '#c4b8a8' },
            },
            invalid: { color: '#dc2626', iconColor: '#dc2626' },
        },
        hidePostalCode: true,
    });

    // Mount the card iframe into our div
    card.mount('#stripe-element');

    // Real-time validation as user types
    card.on('change', function (event) {
        const errorDiv = document.getElementById('card-error');
        errorDiv.textContent = event.error ? event.error.message : '';
    });

    // Handle form submission
    const form    = document.getElementById('payment-form');
    const payBtn  = document.getElementById('pay-btn');
    const btnIcon = document.getElementById('btn-icon');
    const btnText = document.getElementById('btn-text');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Show loading state
        payBtn.disabled = true;
        btnIcon.outerHTML = '<span class="spinner" id="btn-icon"></span>';
        btnText.textContent = 'Processing payment...';

        // Step 1: Tokenize the card via Stripe
        // REQ 3: Card details NEVER touch our server
        const result = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
            billing_details: {
                name:  form.querySelector('[name=name]').value,
                email: form.querySelector('[name=email]').value,
            },
        });

        if (result.error) {
            // Card validation failed
            document.getElementById('card-error').textContent = result.error.message;

            // Reset button
            payBtn.disabled = false;
            document.getElementById('btn-icon').outerHTML =
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="btn-icon"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>';
            btnText.textContent = 'Pay ${{ number_format($total['total'], 2) }} Securely';
            return;
        }

        // Step 2: Send the TOKEN (not card number!) to our server
        // REQ 3: Only the pm_xxx token is sent — never PAN or CVV
        document.getElementById('payment_method_id').value = result.paymentMethod.id;
        form.submit();
    });
});
</script>
@endsection