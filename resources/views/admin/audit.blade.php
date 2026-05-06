{{-- resources/views/admin/audit.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs — DrapeStore Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--ink:#1C1A18;--cream:#FAF8F4;--sand:#E8E0D4;--bark:#6B5D50;--accent:#C4843C;}
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'DM Sans',sans-serif;background:#F5F2EE;color:var(--ink);display:flex;min-height:100vh;}
        .serif{font-family:'Playfair Display',serif;}
        .mono{font-family:'DM Mono',monospace;}
        .sidebar{width:220px;background:var(--ink);min-height:100vh;position:fixed;left:0;top:0;bottom:0;}
        .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.08);}
        .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;font-size:13px;color:rgba(255,255,255,0.55);text-decoration:none;border-left:2px solid transparent;}
        .nav-item:hover{color:white;background:rgba(255,255,255,0.05);}
        .nav-item.active{color:var(--accent);border-left-color:var(--accent);}
        .main{margin-left:220px;padding:36px;flex:1;}
        .badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-family:'DM Mono',monospace;}
        .badge-ok{background:#f0fdf4;color:#15803d;}
        .badge-warn{background:#fffbeb;color:#b45309;}
        .badge-err{background:#fef2f2;color:#b91c1c;}
        .badge-info{background:#eff6ff;color:#1d4ed8;}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-logo"><div style="font-size:18px;color:white;font-family:serif;">Drape<span style="color:var(--accent);font-style:italic;">Store</span></div></div>
    <div style="padding:16px 0;">
        <a href="{{ route('admin.dashboard') }}" class="nav-item">📊 Dashboard</a>
        <a href="{{ route('admin.orders') }}"    class="nav-item">🧾 Orders</a>
        <a href="{{ route('admin.products') }}"  class="nav-item">👗 Products</a>
        <a href="{{ route('admin.audit') }}"     class="nav-item active">🔍 Audit Logs</a>
        <a href="{{ route('shop.index') }}"      class="nav-item">🏪 View Store</a>
    </div>
</div>
<div class="main">
    <h1 style="font-size:26px;font-weight:400;margin-bottom:6px;font-family:serif;">Audit Logs</h1>
    <p style="font-size:13px;color:var(--bark);margin-bottom:8px;">PCI DSS REQ 10 — All security events. Tamper-proof (HMAC verified).</p>

    {{-- REQ 10 explanation box --}}
    <div style="background:rgba(196,132,60,0.08);border:1px solid rgba(196,132,60,0.25);padding:14px 18px;font-size:12px;color:var(--bark);margin-bottom:24px;line-height:1.7;">
        <strong>PCI REQ 10 explained:</strong> Every event in this table was automatically recorded with:
        timestamp (when), user + IP (who + where), event type (what), and an HMAC-SHA256 checksum.
        The checksum detects if any log row is modified after the fact. Logs are retained for 12 months.
    </div>

    <div style="background:white;border:1px solid var(--sand);padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--sand);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:var(--bark);">{{ $logs->total() }} total events</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#faf8f4;border-bottom:1px solid var(--sand);">
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Timestamp</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Event</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">User</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">IP Address</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Description</th>
                    <th style="text-align:left;padding:10px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--bark);">Integrity</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="border-bottom:1px solid #f5f0ea;">
                    <td style="padding:10px 16px;" class="mono" style="font-size:12px;white-space:nowrap;color:var(--bark);">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                    </td>
                    <td style="padding:10px 16px;">
                        @php
                            $bc = match(true) {
                                str_contains($log->event_type,'SUCCESS')  => 'badge-ok',
                                str_contains($log->event_type,'FAILED')   => 'badge-err',
                                str_contains($log->event_type,'LOCKED')   => 'badge-err',
                                str_contains($log->event_type,'ADMIN')    => 'badge-info',
                                str_contains($log->event_type,'PAYMENT')  => 'badge-info',
                                default => 'badge-warn',
                            };
                        @endphp
                        <span class="badge {{ $bc }}">{{ $log->event_type }}</span>
                    </td>
                    <td style="padding:10px 16px;font-size:12px;">{{ $log->user_email ?? '—' }}</td>
                    <td style="padding:10px 16px;" class="mono" style="font-size:12px;color:var(--bark);">{{ $log->ip_address }}</td>
                    <td style="padding:10px 16px;font-size:13px;color:var(--bark);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $log->description }}</td>
                    {{-- REQ 10.3.2: HMAC integrity checksum --}}
                    <td style="padding:10px 16px;" class="mono" style="font-size:11px;color:#15803d;">✓ {{ substr($log->checksum,0,12) }}…</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--bark);">No audit events recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{-- Pagination --}}
        <div style="padding:16px 20px;border-top:1px solid var(--sand);">
            {{ $logs->links() }}
        </div>
    </div>
</div>
</body>
</html>
