<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — DrapeStore Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--ink:#1C1A18;--cream:#FAF8F4;--sand:#E8E0D4;--bark:#6B5D50;--accent:#C4843C;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'DM Sans',sans-serif;background:#F5F2EE;color:var(--ink);display:flex;min-height:100vh;}
        .serif{font-family:'Playfair Display',serif;}
        .sidebar{width:220px;background:var(--ink);min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
        .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.08);}
        .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;font-size:13px;color:rgba(255,255,255,0.55);text-decoration:none;border-left:2px solid transparent;}
        .nav-item:hover{color:white;background:rgba(255,255,255,0.05);}
        .nav-item.active{color:var(--accent);border-left-color:var(--accent);}
        .main{margin-left:220px;padding:36px;flex:1;}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;}
        .badge-paid{background:#f0fdf4;color:#15803d;}
        .badge-pending{background:#fffbeb;color:#b45309;}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo"><div class="serif" style="font-size:18px;color:white;">Drape<span style="color:var(--accent);font-style:italic;">Store</span></div></div>
    <div style="padding:16px 0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-item">📊 Dashboard</a>
        <a href="{{ route('admin.orders') }}"    class="nav-item active">🧾 Orders</a>
        <a href="{{ route('admin.products') }}"  class="nav-item">👗 Products</a>
        <a href="{{ route('admin.audit') }}"     class="nav-item">🔍 Audit Logs</a>
        <a href="{{ route('shop.index') }}"      class="nav-item">🏪 View Store</a>
    </div>
</div>
<div class="main">
    <h1 class="serif" style="font-size:28px;font-weight:400;margin-bottom:8px;">Orders</h1>
    <p style="font-size:13px;color:var(--bark);margin-bottom:32px;">All customer orders. Card data is never stored — only Stripe payment tokens.</p>

    <div style="background:white;border:1px solid var(--sand);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#faf8f4;border-bottom:1px solid var(--sand);">
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Order #</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Customer</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Date</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Total</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Status</th>
                    <th style="text-align:left;padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Payment Token</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr style="border-bottom:1px solid #f5f0ea;">
                    <td style="padding:14px 16px;font-weight:500;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td style="padding:14px 16px;">
                        <div style="font-weight:500;">{{ $order->customer_name }}</div>
                        <div style="font-size:12px;color:var(--bark);">{{ $order->customer_email }}</div>
                    </td>
                    <td style="padding:14px 16px;color:var(--bark);">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i') }}</td>
                    <td style="padding:14px 16px;font-weight:500;">${{ number_format($order->total, 2) }}</td>
                    <td style="padding:14px 16px;"><span class="badge badge-{{ $order->status === 'paid' ? 'paid' : 'pending' }}">{{ ucfirst($order->status) }}</span></td>
                    {{-- REQ 3: Show Stripe token only - never card number --}}
                    <td style="padding:14px 16px;font-family:monospace;font-size:11px;color:var(--bark);">{{ substr($order->stripe_payment_id ?? '—', 0, 18) }}…</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--bark);">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="padding:16px;">{{ $orders->links() }}</div>
    </div>
</div>
</body>
</html>
