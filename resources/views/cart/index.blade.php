@extends('layouts.app')
@section('title', 'Your Cart')

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:48px 24px;">
    <h1 class="serif" style="font-size:36px;font-weight:400;margin-bottom:8px;">Your Cart</h1>
    <p style="color:var(--stone);font-size:14px;margin-bottom:40px;">{{ $total['item_count'] }} item{{ $total['item_count'] !== 1 ? 's' : '' }} in your bag</p>

    @if(empty($cart))
        <div style="text-align:center;padding:80px 0;">
            <div style="font-size:64px;margin-bottom:20px;">🛍</div>
            <div class="serif" style="font-size:26px;margin-bottom:12px;">Your cart is empty</div>
            <p style="color:var(--stone);margin-bottom:28px;">Discover our latest collection.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary">Continue Shopping</a>
        </div>
    @else
        <div style="display:grid;grid-template-columns:1fr 360px;gap:40px;align-items:start;">

            {{-- Cart Items --}}
            <div>
                @foreach($cart as $key => $item)
                <div style="display:grid;grid-template-columns:100px 1fr auto;gap:20px;padding:24px 0;border-bottom:1px solid var(--sand);align-items:center;">
                    {{-- Product image --}}
                    <div style="background:var(--sand);overflow:hidden;">
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" style="width:100%;aspect-ratio:3/4;object-fit:cover;">
                    </div>

                    {{-- Product info --}}
                    <div>
                        <div style="font-size:16px;font-weight:400;margin-bottom:4px;">{{ $item['name'] }}</div>
                        <div style="font-size:13px;color:var(--stone);margin-bottom:12px;">Size: {{ $item['size'] }}</div>
                        <div style="font-size:16px;font-weight:500;">${{ number_format($item['price'], 2) }}</div>

                        {{-- Quantity control --}}
                        <form method="POST" action="{{ route('cart.update') }}" style="display:flex;align-items:center;gap:8px;margin-top:12px;">
                            @csrf
                            <input type="hidden" name="cart_key" value="{{ $key }}">
                            <button type="submit" name="quantity" value="{{ max(0, $item['quantity'] - 1) }}"
                                style="width:28px;height:28px;border:1px solid var(--sand);background:white;cursor:pointer;font-size:16px;">−</button>
                            <span style="font-size:14px;width:28px;text-align:center;">{{ $item['quantity'] }}</span>
                            <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}"
                                style="width:28px;height:28px;border:1px solid var(--sand);background:white;cursor:pointer;font-size:16px;">+</button>
                        </form>
                    </div>

                    {{-- Line total + remove --}}
                    <div style="text-align:right;">
                        <div style="font-size:17px;font-weight:500;margin-bottom:12px;">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                        <form method="POST" action="{{ route('cart.remove') }}">
                            @csrf
                            <input type="hidden" name="cart_key" value="{{ $key }}">
                            <button type="submit" style="background:none;border:none;font-size:12px;color:var(--stone);cursor:pointer;text-decoration:underline;">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach

                {{-- Clear cart link --}}
                <form method="POST" action="{{ route('cart.clear') }}" style="margin-top:16px;">
                    @csrf
                    <button type="submit" style="background:none;border:none;font-size:13px;color:var(--stone);cursor:pointer;text-decoration:underline;">Clear entire cart</button>
                </form>
            </div>

            {{-- Order Summary Panel --}}
            <div style="background:white;border:1px solid var(--sand);padding:28px;position:sticky;top:90px;">
                <div class="serif" style="font-size:20px;margin-bottom:24px;">Order Summary</div>

                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:10px;">
                    <span style="color:var(--bark);">Subtotal</span>
                    <span>${{ number_format($total['subtotal'], 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:10px;">
                    <span style="color:var(--bark);">Tax (7%)</span>
                    <span>${{ number_format($total['tax'], 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:10px;color:var(--bark);">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div style="border-top:1px solid var(--sand);margin:16px 0;"></div>
                <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:500;margin-bottom:24px;">
                    <span>Total</span>
                    <span>${{ number_format($total['total'], 2) }}</span>
                </div>

                <a href="{{ route('checkout.index') }}" class="btn-accent" style="display:block;text-align:center;width:100%;padding:15px;">
                    Proceed to Checkout
                </a>

                {{-- REQ 4: Show security assurance --}}
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;font-size:12px;color:var(--stone);">
                    🔒 Secure checkout powered by Stripe
                </div>

                <div style="margin-top:20px;border-top:1px solid var(--sand);padding-top:16px;">
                    <a href="{{ route('shop.index') }}" style="font-size:13px;color:var(--bark);text-decoration:none;">← Continue Shopping</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
