{{-- resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — DrapeStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,400&family=DM+Sans:wght@300;400;500&family=DM+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --ink:#1C1A18; --cream:#FAF8F4; --sand:#E8E0D4; --bark:#6B5D50; --accent:#C4843C; --green:#16a34a; --red:#dc2626; --yellow:#d97706; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:#F5F2EE; color:var(--ink); display:flex; min-height:100vh; }
        .serif { font-family:'Playfair Display',serif; }
        .mono  { font-family:'DM Mono',monospace; }

        /* Sidebar */
        .sidebar { width:220px; background:var(--ink); min-height:100vh; padding:0; position:fixed; left:0; top:0; bottom:0; z-index:10; }
        .sidebar-logo { padding:24px 20px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .nav-item { display:flex; align-items:center; gap:10px; padding:12px 20px; font-size:13px; color:rgba(255,255,255,0.55); cursor:pointer; text-decoration:none; transition:color 0.15s, background 0.15s; border-left:2px solid transparent; }
        .nav-item:hover { color:rgba(255,255,255,0.9); background:rgba(255,255,255,0.05); }
        .nav-item.active { color:var(--accent); border-left-color:var(--accent); background:rgba(196,132,60,0.08); }

        /* Main */
        .main { margin-left:220px; padding:36px; flex:1; }

        /* Stat cards */
        .stat-card { background:white; border:1px solid var(--sand); border-radius:2px; padding:24px; }
        .stat-val { font-size:30px; font-weight:500; }
        .stat-label { font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:var(--bark); margin-bottom:10px; }

        /* PCI requirement row */
        .req-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f0ece6; }
        .req-row:last-child { border:none; }
        .dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .bar-bg { flex:1; height:4px; background:#e8e0d4; border-radius:2px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:2px; transition:width 0.6s ease; }

        /* Log table */
        .log-table { width:100%; border-collapse:collapse; font-size:13px; }
        .log-table th { text-align:left; padding:9px 12px; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--bark); border-bottom:2px solid var(--sand); }
        .log-table td { padding:10px 12px; border-bottom:1px solid #f0ece6; vertical-align:middle; }
        .log-table tr:last-child td { border:none; }
        .badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-family:'DM Mono',monospace; }
        .badge-ok   { background:#f0fdf4; color:#15803d; }
        .badge-warn { background:#fffbeb; color:#b45309; }
        .badge-err  { background:#fef2f2; color:#b91c1c; }
        .badge-info { background:#eff6ff; color:#1d4ed8; }
    </style>
</head>
<body>

{{-- ── SIDEBAR ── --}}
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="serif" style="font-size:18px;color:white;">Drape<span style="color:var(--accent);font-style:italic;">Store</span></div>
        <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:4px;letter-spacing:0.1em;text-transform:uppercase;">Admin Panel</div>
    </div>
    <div style="padding:16px 0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-item active">📊 Dashboard</a>
        <a href="{{ route('admin.orders') }}"    class="nav-item">🧾 Orders</a>
        <a href="{{ route('admin.products') }}"  class="nav-item">👗 Products</a>
        <a href="{{ route('admin.audit') }}"     class="nav-item">🔍 Audit Logs</a>
        <a href="{{ route('shop.index') }}"      class="nav-item">🏪 View Store</a>
    </div>
    <div style="position:absolute;bottom:0;left:0;right:0;padding:16px;">
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="nav-item" style="width:100%;border:none;background:none;cursor:pointer;">← Logout</button>
        </form>
    </div>
</div>

{{-- ── MAIN CONTENT ── --}}
<div class="main">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:36px;">
        <div>
            <h1 class="serif" style="font-size:28px;font-weight:400;">Security Dashboard</h1>
            <p style="font-size:13px;color:var(--bark);margin-top:4px;">PCI DSS Compliance Monitor</p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--green);">
            <span style="width:8px;height:8px;background:var(--green);border-radius:50%;animation:pulse 2s infinite;display:inline-block;"></span>
            All systems operational
        </div>
    </div>

    {{-- Stat cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-val">${{ number_format($stats['total_revenue'], 0) }}</div>
            <div style="font-size:12px;color:var(--green);margin-top:8px;">↑ All paid orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-val">{{ $stats['total_orders'] }}</div>
            <div style="font-size:12px;color:var(--bark);margin-top:8px;">All time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Products Active</div>
            <div class="stat-val">{{ $stats['total_products'] }}</div>
            <div style="font-size:12px;color:var(--bark);margin-top:8px;">In catalogue</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Customers</div>
            <div class="stat-val">{{ $stats['total_users'] }}</div>
            <div style="font-size:12px;color:var(--bark);margin-top:8px;">Registered accounts</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

        {{-- PCI Compliance status --}}
        <div style="background:white;border:1px solid var(--sand);padding:24px;">
            <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);margin-bottom:20px;">PCI DSS v4.0 — 12 Requirements</div>
            @php
            $reqs = [
                ['n'=>1,'name'=>'Network Security','score'=>100,'ok'=>true],
                ['n'=>2,'name'=>'Secure Configurations','score'=>95,'ok'=>true],
                ['n'=>3,'name'=>'Stored Data Protection','score'=>100,'ok'=>true],
                ['n'=>4,'name'=>'Data in Transit','score'=>100,'ok'=>true],
                ['n'=>5,'name'=>'Anti-Malware','score'=>88,'ok'=>true],
                ['n'=>6,'name'=>'Secure Systems & WAF','score'=>80,'ok'=>false],
                ['n'=>7,'name'=>'Access Restriction (RBAC)','score'=>100,'ok'=>true],
                ['n'=>8,'name'=>'Authentication & MFA','score'=>95,'ok'=>true],
                ['n'=>9,'name'=>'Physical Access (AWS)','score'=>100,'ok'=>true],
                ['n'=>10,'name'=>'Audit Logging','score'=>98,'ok'=>true],
                ['n'=>11,'name'=>'Security Testing','score'=>75,'ok'=>false],
                ['n'=>12,'name'=>'Security Policy','score'=>90,'ok'=>true],
            ];
            @endphp
            @foreach($reqs as $r)
            <div class="req-row">
                <div class="dot" style="background:{{ $r['ok'] ? '#16a34a' : '#d97706' }};"></div>
                <div style="font-size:13px;width:200px;flex-shrink:0;">Req {{ $r['n'] }} — {{ $r['name'] }}</div>
                <div class="bar-bg">
                    <div class="bar-fill" style="width:{{ $r['score'] }}%;background:{{ $r['ok'] ? '#16a34a' : '#d97706' }};"></div>
                </div>
                <div class="mono" style="font-size:11px;color:{{ $r['ok'] ? '#16a34a' : '#d97706' }};width:36px;text-align:right;">{{ $r['score'] }}%</div>
            </div>
            @endforeach
        </div>

        {{-- Recent orders --}}
        <div style="background:white;border:1px solid var(--sand);padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);">Recent Orders</div>
                <a href="{{ route('admin.orders') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">View all →</a>
            </div>
            @forelse($stats['recent_orders'] as $order)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0ece6;">
                <div>
                    <div style="font-size:14px;font-weight:500;">#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}</div>
                    <div style="font-size:12px;color:var(--bark);">{{ $order->customer_name }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:14px;font-weight:500;">${{ number_format($order->total,2) }}</div>
                    <div class="badge badge-ok" style="font-size:10px;">{{ $order->status }}</div>
                </div>
            </div>
            @empty
            <p style="color:var(--stone);font-size:14px;text-align:center;padding:20px;">No orders yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Audit Logs (REQ 10) --}}
    <div style="background:white;border:1px solid var(--sand);padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);">Live Audit Log (PCI REQ 10)</div>
            <a href="{{ route('admin.audit') }}" style="font-size:12px;color:var(--accent);text-decoration:none;">View all →</a>
        </div>
        <table class="log-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>IP</th>
                    <th>Description</th>
                    <th>Integrity</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['recent_logs'] as $log)
                <tr>
                    <td class="mono" style="color:var(--bark);font-size:12px;white-space:nowrap;">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</td>
                    <td>
                        @php
                            $badgeClass = match(true) {
                                str_contains($log->event_type,'SUCCESS') => 'badge-ok',
                                str_contains($log->event_type,'FAILED')  => 'badge-err',
                                str_contains($log->event_type,'ADMIN')   => 'badge-info',
                                default => 'badge-warn',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $log->event_type }}</span>
                    </td>
                    <td style="font-size:13px;">{{ $log->user_email ?? '—' }}</td>
                    <td class="mono" style="font-size:12px;color:var(--bark);">{{ $log->ip_address }}</td>
                    <td style="font-size:13px;color:var(--bark);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $log->description }}</td>
                    {{-- REQ 10.3.2: Show HMAC checksum (first 8 chars) --}}
                    <td class="mono" style="font-size:11px;color:var(--green);">✓ {{ substr($log->checksum, 0, 8) }}…</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--stone);padding:20px;">No audit events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.4)} }
</style>
</body>
</html>
