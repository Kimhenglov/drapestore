{{-- resources/views/admin/products.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — DrapeStore Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--ink:#1C1A18;--cream:#FAF8F4;--sand:#E8E0D4;--bark:#6B5D50;--accent:#C4843C;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'DM Sans',sans-serif;background:#F5F2EE;color:var(--ink);display:flex;min-height:100vh;}
        .serif{font-family:'Playfair Display',serif;}
        .sidebar{width:220px;background:var(--ink);min-height:100vh;padding:0;position:fixed;left:0;top:0;bottom:0;}
        .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.08);}
        .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;font-size:13px;color:rgba(255,255,255,0.55);cursor:pointer;text-decoration:none;border-left:2px solid transparent;}
        .nav-item:hover{color:white;background:rgba(255,255,255,0.05);}
        .nav-item.active{color:var(--accent);border-left-color:var(--accent);background:rgba(196,132,60,0.08);}
        .main{margin-left:220px;padding:36px;flex:1;}
        .form-input{width:100%;border:1px solid var(--sand);padding:10px 12px;font-size:13px;background:white;outline:none;}
        .form-input:focus{border-color:var(--bark);}
        .form-label{font-size:11px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);margin-bottom:6px;display:block;}
        .btn{padding:9px 20px;font-size:12px;letter-spacing:0.1em;text-transform:uppercase;border:none;cursor:pointer;}
        .btn-dark{background:var(--ink);color:white;}
        .btn-accent{background:var(--accent);color:white;}
        .btn-red{background:#dc2626;color:white;}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo"><div class="serif" style="font-size:18px;color:white;">Drape<span style="color:var(--accent);font-style:italic;">Store</span></div></div>
    <div style="padding:16px 0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-item">📊 Dashboard</a>
        <a href="{{ route('admin.orders') }}"    class="nav-item">🧾 Orders</a>
        <a href="{{ route('admin.products') }}"  class="nav-item active">👗 Products</a>
        <a href="{{ route('admin.audit') }}"     class="nav-item">🔍 Audit Logs</a>
        <a href="{{ route('shop.index') }}"      class="nav-item">🏪 View Store</a>
    </div>
</div>
<div class="main">
    <h1 class="serif" style="font-size:28px;font-weight:400;margin-bottom:8px;">Product Management</h1>
    <p style="font-size:13px;color:var(--bark);margin-bottom:32px;">PCI REQ 7: Admin-only access. All changes are logged.</p>

    @if(session('success'))<div style="background:#f0fdf4;border-left:3px solid #16a34a;padding:12px 16px;margin-bottom:20px;font-size:14px;color:#15803d;">✓ {{ session('success') }}</div>@endif

    {{-- Add Product Form --}}
    <div style="background:white;border:1px solid var(--sand);padding:28px;margin-bottom:32px;">
        <div style="font-size:13px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);margin-bottom:20px;">Add New Product</div>
        <form method="POST" action="{{ route('admin.products.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div><label class="form-label">Product Name *</label><input name="name" class="form-input" required placeholder="Silk Wrap Dress"></div>
                <div><label class="form-label">Price ($) *</label><input name="price" type="number" step="0.01" class="form-input" required placeholder="89.00"></div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-input" required>
                        <option value="">Select...</option>
                        @foreach(['tops','bottoms','dresses','outerwear','accessories'] as $c)
                            <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div><label class="form-label">Image URL *</label><input name="image_url" class="form-input" required placeholder="https://images.unsplash.com/..."></div>
                <div><label class="form-label">Stock *</label><input name="stock" type="number" class="form-input" required value="50"></div>
                <div><label class="form-label">Sizes (comma-separated) *</label><input name="size_options" class="form-input" required value="XS,S,M,L,XL"></div>
            </div>
            <div style="margin-bottom:16px;"><label class="form-label">Description *</label><textarea name="description" class="form-input" rows="2" required placeholder="Product description..."></textarea></div>
            <button type="submit" class="btn btn-accent">+ Add Product</button>
        </form>
    </div>

    {{-- Products Table --}}
    <div style="background:white;border:1px solid var(--sand);padding:24px;">
        <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);margin-bottom:16px;">All Products ({{ count($products) }})</div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:2px solid var(--sand);">
                    <th style="text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Product</th>
                    <th style="text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Category</th>
                    <th style="text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Price</th>
                    <th style="text-align:left;padding:8px 12px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Stock</th>
                    <th style="padding:8px 12px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr style="border-bottom:1px solid #f0ece6;">
                    <td style="padding:12px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="{{ $p->image_url }}" style="width:44px;height:56px;object-fit:cover;background:var(--sand);">
                            <span style="font-weight:400;">{{ $p->name }}</span>
                        </div>
                    </td>
                    <td style="padding:12px;color:var(--bark);text-transform:capitalize;">{{ $p->category }}</td>
                    <td style="padding:12px;font-weight:500;">${{ number_format($p->price,2) }}</td>
                    <td style="padding:12px;">{{ $p->stock }}</td>
                    <td style="padding:12px;">
                        <form method="POST" action="{{ route('admin.products.delete', $p->id) }}" onsubmit="return confirm('Remove this product?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-red" style="padding:6px 14px;font-size:11px;">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
