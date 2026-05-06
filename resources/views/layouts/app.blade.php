<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DrapeStore') — Fashion</title>

    {{-- Google Fonts: Playfair Display (editorial display) + DM Sans (clean body) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --cream:  #FAF8F4;
            --sand:   #E8E0D4;
            --stone:  #C4B8A8;
            --bark:   #6B5D50;
            --ink:    #1C1A18;
            --accent: #C4843C;  /* warm amber — the brand's one bold color */
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--ink);
            min-height: 100vh;
        }
        h1, h2, h3, .serif { font-family: 'Playfair Display', serif; }

        /* ── Navbar ── */
        .navbar {
            position: sticky; top: 0; z-index: 100;
            background: rgba(250,248,244,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--sand);
        }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--ink); color: var(--cream);
            padding: 12px 28px; font-size: 13px;
            letter-spacing: 0.12em; text-transform: uppercase;
            font-weight: 500; border: none; cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            display: inline-block; text-decoration: none;
        }
        .btn-primary:hover { background: var(--bark); }
        .btn-accent {
            background: var(--accent); color: white;
            padding: 12px 28px; font-size: 13px;
            letter-spacing: 0.1em; text-transform: uppercase;
            font-weight: 500; border: none; cursor: pointer;
            transition: opacity 0.2s;
            display: inline-block; text-decoration: none;
        }
        .btn-accent:hover { opacity: 0.88; }
        .btn-outline {
            background: transparent; color: var(--ink);
            border: 1px solid var(--ink);
            padding: 11px 26px; font-size: 13px;
            letter-spacing: 0.1em; text-transform: uppercase;
            font-weight: 500; cursor: pointer;
            transition: all 0.2s; display: inline-block; text-decoration: none;
        }
        .btn-outline:hover { background: var(--ink); color: var(--cream); }

        /* ── Flash messages ── */
        .flash { padding: 14px 20px; font-size: 14px; border-left: 3px solid; }
        .flash-success { background: #f0fdf4; border-color: #16a34a; color: #15803d; }
        .flash-error   { background: #fef2f2; border-color: #dc2626; color: #b91c1c; }
        .flash-warning { background: #fffbeb; border-color: #d97706; color: #b45309; }

        /* ── Card ── */
        .product-card { transition: transform 0.25s ease; }
        .product-card:hover { transform: translateY(-4px); }
        .product-card img { transition: transform 0.4s ease; aspect-ratio: 3/4; object-fit: cover; width: 100%; }
        .product-card:hover img { transform: scale(1.03); }

        /* ── Form inputs ── */
        .form-input {
            width: 100%; border: 1px solid var(--sand); padding: 11px 14px;
            font-family: 'DM Sans', sans-serif; font-size: 14px;
            background: white; color: var(--ink); outline: none;
            transition: border-color 0.15s;
        }
        .form-input:focus { border-color: var(--bark); }
        .form-label { font-size: 11px; font-weight: 500; letter-spacing: 0.1em;
                      text-transform: uppercase; color: var(--bark); margin-bottom: 6px; display: block; }

        /* ── Cart badge ── */
        .cart-badge {
            background: var(--accent); color: white;
            width: 18px; height: 18px; border-radius: 50%;
            font-size: 10px; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: -6px; right: -8px;
        }

        /* ── TLS indicator (REQ 4) ── */
        .tls-indicator {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; color: #15803d; font-weight: 500;
            background: #f0fdf4; border: 1px solid #bbf7d0;
            padding: 3px 10px; border-radius: 20px;
        }
    </style>
    <!-- @stack('styles') -->
</head>
<body>

{{-- ── NAVBAR ── --}}
<nav class="navbar">
    <div style="max-width:1280px; margin:0 auto; padding:0 24px; height:64px; display:flex; align-items:center; justify-content:space-between;">

        {{-- Logo --}}
        <a href="{{ route('shop.index') }}" style="text-decoration:none;">
            <span class="serif" style="font-size:22px; letter-spacing:-0.02em; color:var(--ink);">Drape</span><span class="serif" style="font-size:22px; color:var(--accent); font-style:italic;">Store</span>
        </a>

        {{-- Navigation links --}}
        <div style="display:flex; gap:28px; font-size:13px; letter-spacing:0.08em; text-transform:uppercase;">
            <a href="{{ route('shop.index') }}" style="color:var(--bark); text-decoration:none;">Shop</a>
            <a href="{{ route('shop.index') }}?category=dresses" style="color:var(--bark); text-decoration:none;">Dresses</a>
            <a href="{{ route('shop.index') }}?category=outerwear" style="color:var(--bark); text-decoration:none;">Outerwear</a>
            <a href="{{ route('shop.index') }}?category=accessories" style="color:var(--bark); text-decoration:none;">Accessories</a>
        </div>

        {{-- Right icons --}}
        <div style="display:flex; align-items:center; gap:20px;">
            {{-- Search --}}
            <form action="{{ route('shop.index') }}" method="GET" style="display:flex; align-items:center;">
                <input name="search" placeholder="Search..." value="{{ request('search') }}"
                    style="border:none; border-bottom:1px solid var(--sand); background:transparent; font-size:13px; padding:4px 8px; width:140px; outline:none; color:var(--ink);">
            </form>

            {{-- Auth links --}}
            @auth
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : '#' }}"
                   style="font-size:13px; color:var(--bark); text-decoration:none;">
                    {{ Auth::user()->name }}
                </a>
                <form method="POST" action="{{ route('auth.logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none; border:none; font-size:13px; color:var(--stone); cursor:pointer;">Logout</button>
                </form>
            @else
                <a href="{{ route('auth.login') }}" style="font-size:13px; color:var(--bark); text-decoration:none;">Login</a>
            @endauth

            {{-- Cart icon with item count --}}
            <a href="{{ route('cart.index') }}" style="position:relative; text-decoration:none; color:var(--ink);">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                @php $cartCount = collect(session('cart', []))->sum('quantity'); @endphp
                @if($cartCount > 0)
                    <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</nav>

{{-- ── Flash Messages ── --}}
<div style="max-width:1280px; margin:0 auto; padding:0 24px;">
    @if(session('success'))
        <div class="flash flash-success" style="margin-top:16px;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error" style="margin-top:16px;">✕ {{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="flash flash-warning" style="margin-top:16px;">⚠ {{ session('warning') }}</div>
    @endif
</div>

{{-- ── Main Content ── --}}
<main>
    @yield('content')
</main>

{{-- ── FOOTER ── --}}
<footer style="background:var(--ink); color:var(--stone); margin-top:80px; padding:48px 24px;">
    <div style="max-width:1280px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:32px;">
        <div>
            <div class="serif" style="font-size:20px; color:var(--cream); margin-bottom:12px;">DrapeStore</div>
            <p style="font-size:13px; line-height:1.7;">Curated fashion with security and style at heart.</p>
            {{-- REQ 4: Show PCI compliance badge in footer --}}
            <div style="margin-top:16px; padding:10px 14px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:6px; font-size:11px; color:var(--stone);">
                🔒 PCI DSS v4.0 Compliant<br>
                <span style="font-size:10px; opacity:0.6;">256-bit TLS encryption on all payments</span>
            </div>
        </div>
        <div>
            <div style="font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:var(--cream); margin-bottom:14px;">Shop</div>
            @foreach(['Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Accessories'] as $cat)
                <div style="margin-bottom:8px;"><a href="{{ route('shop.index') }}?category={{ strtolower($cat) }}" style="font-size:13px; color:var(--stone); text-decoration:none;">{{ $cat }}</a></div>
            @endforeach
        </div>
        <div>
            <div style="font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:var(--cream); margin-bottom:14px;">Account</div>
            <div style="margin-bottom:8px;"><a href="{{ route('auth.login') }}" style="font-size:13px; color:var(--stone); text-decoration:none;">Login</a></div>
            <div style="margin-bottom:8px;"><a href="{{ route('auth.register') }}" style="font-size:13px; color:var(--stone); text-decoration:none;">Register</a></div>
            <div style="margin-bottom:8px;"><a href="{{ route('cart.index') }}" style="font-size:13px; color:var(--stone); text-decoration:none;">Cart</a></div>
        </div>
        <div>
            <div style="font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:var(--cream); margin-bottom:14px;">Security</div>
            <div style="font-size:12px; line-height:1.8; color:var(--stone);">
                All transactions protected by Stripe<br>
                No card data stored on our servers<br>
                PCI DSS v4.0 compliant checkout<br>
                TLS 1.3 encrypted connections
            </div>
        </div>
    </div>
    <div style="max-width:1280px; margin:32px auto 0; padding-top:24px; border-top:1px solid rgba(255,255,255,0.1); font-size:12px; color:var(--stone); display:flex; justify-content:space-between;">
        <span>© 2025 DrapeStore. Academic PCI DSS v4.0 Project.</span>
        <span>Built with Laravel + Stripe</span>
    </div>
</footer>

@stack('scripts')
</body>
</html>
