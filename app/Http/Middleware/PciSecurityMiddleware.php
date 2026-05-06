<?php
// ============================================================
// app/Http/Middleware/PciSecurityMiddleware.php
//
// PURPOSE: Runs on EVERY single HTTP request to the app.
// Enforces PCI DSS security controls at the network/session level.
//
// PCI DSS Requirements covered:
//   REQ 1  — Blocks unauthorized HTTP methods
//   REQ 2  — Removes server version headers (hides technology stack)
//   REQ 4  — Adds HSTS header (forces HTTPS), blocks HTTP on payment pages
//   REQ 6  — Adds Content Security Policy (prevents XSS attacks)
//   REQ 8  — Enforces 15-minute session timeout
//   REQ 10 — Logs every request to the audit trail
// ============================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;
use Symfony\Component\HttpFoundation\Response;

class PciSecurityMiddleware
{
    // Inject the AuditLogger service via constructor
    public function __construct(private AuditLogger $audit) {}

    /**
     * The handle() method runs on EVERY request.
     * $request = incoming HTTP request from browser
     * $next    = function that passes request to the next handler
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ── STEP 1: REQ 1 — Block disallowed HTTP methods ────────
        // Only allow standard web methods. Block TRACE, CONNECT etc.
        // which can be used for Cross-Site Tracing (XST) attacks.
        $allowed = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        if (!in_array($request->method(), $allowed)) {
            return response('Method Not Allowed', 405);
        }

        // ── STEP 2: REQ 4 — Force HTTPS on checkout pages ────────
        // In production, if someone tries to access /checkout over HTTP,
        // we block them. Card data must NEVER travel over plain HTTP.
        if (app()->isProduction() && !$request->secure()) {
            if ($request->is('checkout*') || $request->is('payments*')) {
                $this->audit->log('INSECURE_PAYMENT_ATTEMPT', [
                    'url' => $request->url(),
                    'ip'  => $request->ip(),
                ]);
                // Redirect to HTTPS version
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        // ── STEP 3: REQ 8 — Session timeout (15 minutes idle) ────
        // PCI DSS requires that admin/CDE sessions expire after 15 min idle.
        // We check when the user last did something.
        if (Auth::check()) {
            $lastActivity = session('_last_activity');

            if ($lastActivity !== null) {
                $idleMinutes = (time() - $lastActivity) / 60;

                if ($idleMinutes > 15) {
                    // Session has been idle too long — log out for security
                    $userId = Auth::id();
                    Auth::logout();
                    session()->flush(); // Clear all session data
                    $request->session()->regenerate(); // New session ID

                    $this->audit->log('SESSION_TIMEOUT_LOGOUT', [
                        'user_id'      => $userId,
                        'idle_minutes' => round($idleMinutes, 1),
                    ]);

                    // Send them back to login with a warning message
                    return redirect()->route('auth.login')
                        ->with('warning', '⏱ Your session expired after 15 minutes of inactivity. Please log in again.');
                }
            }

            // Update the "last activity" timestamp to right now
            session(['_last_activity' => time()]);
        }

        // ── STEP 4: Process the request normally ─────────────────
        // Everything passed — let the request continue to the controller
        $response = $next($request);

        // ── STEP 5: Add security headers to EVERY response ───────
        // These headers tell the browser to apply extra security protections.
        // They are added AFTER the controller runs, on the way out.
        $this->addSecurityHeaders($response, $request);

        return $response;
    }

    /**
     * addSecurityHeaders() — Adds HTTP security headers to every response.
     *
     * These headers are invisible to users but tell the browser
     * to enforce important security policies.
     */
    private function addSecurityHeaders(Response $response, Request $request): void
    {
        // REQ 4 — HSTS: Tell browser "always use HTTPS for this site for 1 year"
        // Even if someone types "http://", the browser auto-switches to https://
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // REQ 6 — Clickjacking protection: Prevent our site being embedded in iframes
        // This stops "clickjacking" attacks where attackers overlay invisible frames
        $response->headers->set('X-Frame-Options', 'DENY');

        // REQ 6 — Prevent browser from guessing wrong content types
        // Stops MIME sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');



        

        // REQ 6 — Content Security Policy (CSP)
        // This tells the browser exactly which scripts/styles are allowed to load.
        // Prevents Cross-Site Scripting (XSS) attacks.
        // Breakdown of the policy:
        //   default-src 'self'          → Only load resources from OUR domain by default
        //   script-src 'self' stripe    → Only allow our scripts + Stripe.js (needed for payments)
        //   style-src 'self' 'unsafe-inline' → Allow our CSS + inline styles (needed for Tailwind)
        //   img-src 'self' data: https  → Images from our domain + external https + data URIs
        //   connect-src stripe          → Allow AJAX only to our domain + Stripe
        //   frame-src stripe            → Only Stripe can be in an iframe (card form)
        //   object-src 'none'           → Block Flash and other plugins (they're dangerous)

    

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://js.stripe.com https://cdn.tailwindcss.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; " .
            "font-src 'self' data: https://fonts.gstatic.com; " .
            "img-src 'self' data: blob: https:; " .
            "connect-src 'self' https://api.stripe.com https://m.stripe.network https://errors.stripe.com; " .
            "frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://m.stripe.network; " .
            "object-src 'none'; " .
            "base-uri 'self';"
        );

        // REQ 2 — Remove headers that reveal our technology stack
        // Attackers can use "X-Powered-By: PHP/8.2" to find known vulnerabilities
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Referrer Policy — Don't leak our internal URLs to external sites
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
