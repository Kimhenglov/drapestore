@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:40px 24px;">
    <a href="{{ route('shop.index') }}" style="font-size:13px;color:var(--bark);text-decoration:none;">← Back to Shop</a>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-top:32px;">
        {{-- Image --}}
        <div style="background:var(--sand);">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:3/4;object-fit:cover;">
        </div>

        {{-- Details --}}
        <div>
            <div style="font-size:11px;letter-spacing:0.15em;text-transform:uppercase;color:var(--stone);margin-bottom:12px;">{{ ucfirst($product->category) }}</div>
            <h1 class="serif" style="font-size:32px;font-weight:400;margin-bottom:16px;">{{ $product->name }}</h1>
            <div style="font-size:24px;font-weight:500;margin-bottom:24px;">${{ number_format($product->price, 2) }}</div>
            <p style="color:var(--bark);line-height:1.7;font-size:15px;margin-bottom:32px;">{{ $product->description }}</p>

            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div style="margin-bottom:20px;">
                    <label class="form-label">Size</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach(explode(',', $product->size_options) as $i => $size)
                            <label style="cursor:pointer;">
                                <input type="radio" name="size" value="{{ trim($size) }}" {{ $i === 0 ? 'checked' : '' }} style="display:none;" class="size-radio">
                                <span style="display:inline-block;border:1px solid var(--sand);padding:8px 16px;font-size:13px;background:white;">{{ trim($size) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label class="form-label">Quantity</label>
                    <select name="quantity" class="form-input" style="width:100px;">
                        @for($i = 1; $i <= min(10, $product->stock); $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;padding:16px;">Add to Cart — ${{ number_format($product->price, 2) }}</button>
            </form>

            <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--sand);font-size:12px;color:var(--stone);line-height:1.8;">
                ✓ Free shipping on orders over $100<br>
                ✓ 30-day returns<br>
                ✓ Secure PCI DSS compliant checkout
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if($related->isNotEmpty())
    <div style="margin-top:80px;">
        <h2 class="serif" style="font-size:24px;font-weight:400;margin-bottom:24px;">You may also like</h2>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;">
            @foreach($related as $r)
            <a href="{{ route('shop.show', $r->id) }}" style="text-decoration:none;color:inherit;">
                <div style="background:var(--sand);overflow:hidden;margin-bottom:12px;">
                    <img src="{{ $r->image_url }}" style="width:100%;aspect-ratio:3/4;object-fit:cover;">
                </div>
                <div style="font-size:14px;margin-bottom:4px;">{{ $r->name }}</div>
                <div style="font-size:14px;font-weight:500;">${{ number_format($r->price, 2) }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
