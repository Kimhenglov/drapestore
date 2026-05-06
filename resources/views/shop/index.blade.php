@extends('layouts.app')
@section('title', 'Shop All')

@section('content')
{{-- Hero Banner --}}
<div style="background:var(--ink); color:var(--cream); padding:60px 24px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1400&q=60') center/cover;opacity:0.15;"></div>
    <div style="position:relative;">
        <div style="font-size:11px;letter-spacing:0.25em;text-transform:uppercase;color:var(--stone);margin-bottom:12px;">New Collection</div>
        <h1 class="serif" style="font-size:clamp(36px,5vw,64px);font-weight:400;margin-bottom:16px;line-height:1.1;">Dressed for the<br><em>modern moment</em></h1>
        <p style="font-size:15px;color:var(--stone);max-width:480px;margin:0 auto;">Thoughtfully curated pieces for the discerning wardrobe.</p>
    </div>
</div>

<div style="max-width:1280px;margin:0 auto;padding:40px 24px;">
    <div style="display:grid;grid-template-columns:240px 1fr;gap:48px;">

        {{-- ── SIDEBAR FILTERS ── --}}
        <aside>
            <form method="GET" action="{{ route('shop.index') }}" id="filter-form">
                <div style="margin-bottom:32px;">
                    <div style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;font-weight:500;color:var(--bark);margin-bottom:16px;">Category</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;">
                            <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" style="accent-color:var(--ink);">
                            All Items
                        </label>
                        @foreach(['tops','bottoms','dresses','outerwear','accessories'] as $cat)
                        <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;text-transform:capitalize;">
                            <input type="radio" name="category" value="{{ $cat }}" {{ request('category') === $cat ? 'checked' : '' }} onchange="this.form.submit()" style="accent-color:var(--ink);">
                            {{ ucfirst($cat) }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div style="margin-bottom:32px;">
                    <div style="font-size:11px;letter-spacing:0.12em;text-transform:uppercase;font-weight:500;color:var(--bark);margin-bottom:16px;">Max Price</div>
                    <input type="range" name="max_price" min="0" max="400" step="10"
                        value="{{ request('max_price', 400) }}"
                        oninput="document.getElementById('price-display').textContent='$'+this.value"
                        onchange="this.form.submit()"
                        style="width:100%;accent-color:var(--ink);">
                    <div style="font-size:13px;color:var(--bark);margin-top:6px;">Up to <span id="price-display">${{ request('max_price', 400) }}</span></div>
                </div>

                @if(request('category') || request('max_price') || request('search'))
                    <a href="{{ route('shop.index') }}" style="font-size:13px;color:var(--stone);text-decoration:underline;">Clear filters</a>
                @endif
            </form>
        </aside>

        {{-- ── PRODUCT GRID ── --}}
        <div>
            {{-- Results header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                <div style="font-size:14px;color:var(--bark);">
                    {{ count($products) }} item{{ count($products) !== 1 ? 's' : '' }}
                    @if(request('category')) in <strong>{{ ucfirst(request('category')) }}</strong>@endif
                    @if(request('search')) matching "<strong>{{ request('search') }}</strong>"@endif
                </div>
            </div>

            @if($products->isEmpty())
                <div style="text-align:center;padding:80px 0;color:var(--stone);">
                    <div style="font-size:48px;margin-bottom:16px;">🔍</div>
                    <div class="serif" style="font-size:22px;margin-bottom:8px;">No items found</div>
                    <a href="{{ route('shop.index') }}" class="btn-outline" style="margin-top:16px;">View All</a>
                </div>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:28px;">
                    @foreach($products as $product)
                    <div class="product-card">
                        <a href="{{ route('shop.show', $product->id) }}" style="text-decoration:none;color:inherit;">
                            <div style="overflow:hidden;background:var(--sand);margin-bottom:14px;">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                    style="width:100%;aspect-ratio:3/4;object-fit:cover;"
                                    loading="lazy">
                            </div>
                            <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--stone);margin-bottom:4px;">{{ ucfirst($product->category) }}</div>
                            <div style="font-size:15px;font-weight:400;margin-bottom:6px;line-height:1.3;">{{ $product->name }}</div>
                            <div style="font-size:16px;font-weight:500;color:var(--ink);">${{ number_format($product->price, 2) }}</div>
                        </a>
                        {{-- Quick add to cart --}}
                        <form method="POST" action="{{ route('cart.add') }}" style="margin-top:10px;">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <div style="display:flex;gap:6px;">
                                <select name="size" style="flex:1;border:1px solid var(--sand);padding:7px;font-size:12px;background:white;color:var(--ink);">
                                    @foreach(explode(',', $product->size_options) as $size)
                                        <option value="{{ trim($size) }}">{{ trim($size) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary" style="padding:7px 14px;font-size:11px;">+ Add</button>
                            </div>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
