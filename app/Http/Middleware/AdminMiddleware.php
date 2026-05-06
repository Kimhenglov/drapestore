<?php
// ============================================================
// app/Http/Middleware/AdminMiddleware.php
// PCI REQ 7: Restrict access to admin pages by role
// ============================================================
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;

class AdminMiddleware {
    public function __construct(private AuditLogger $audit) {}

    public function handle(Request $request, Closure $next) {
        // Check: is the user logged in?
        if (!Auth::check()) {
            return redirect()->route('auth.login')->with('warning', 'Please log in to access the admin panel.');
        }
        // Check: does the user have the 'admin' role?
        // REQ 7.2: Access is denied by default — must be explicitly granted
        if (Auth::user()->role !== 'admin') {
            // REQ 10.2.4: Log unauthorized access attempt
            $this->audit->log('UNAUTHORIZED_ADMIN_ACCESS', [
                'user_id' => Auth::id(),
                'url'     => $request->url(),
            ]);
            abort(403, 'Access denied. Admin privileges required.');
        }
        return $next($request);
    }
}
