<?php
// ============================================================
// app/Http/Controllers/AuthController.php
// PCI REQ 8: User authentication (login, register, logout)
// ============================================================
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class AuthController extends Controller
{
    // REQ 8.3.4: Max failed attempts before account lockout
    private const MAX_ATTEMPTS = 5;
    // Lock account for 30 minutes after too many failures
    private const LOCKOUT_MINUTES = 30;

    public function __construct(private AuditLogger $audit) {}

    public function showLogin()  { return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }

    /**
     * login() — Process login attempt.
     * REQ 8.2: Verify unique ID (email) and password
     * REQ 8.3.4: Lock account after 5 failed attempts
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user in DB
        $user = DB::table('users')->where('email', $request->email)->first();

        // REQ 8.3.4: Check if account is currently locked out
        if ($user && $user->locked_until && now()->lt($user->locked_until)) {
            $minutesLeft = now()->diffInMinutes($user->locked_until);
            $this->audit->loginFailed($request->email, 'account_locked');
            return back()->withErrors(['email' =>
                "Account temporarily locked. Try again in {$minutesLeft} minute(s)."
            ]);
        }

        // Check credentials: does user exist AND does password match?
        // Hash::check() compares the plain password to the bcrypt hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Wrong password — increment the failure counter
            if ($user) {
                $attempts = $user->login_attempts + 1;
                $lockUntil = $attempts >= self::MAX_ATTEMPTS ? now()->addMinutes(self::LOCKOUT_MINUTES) : null;

                DB::table('users')->where('id', $user->id)->update([
                    'login_attempts' => $attempts,
                    'locked_until'   => $lockUntil,
                ]);

                if ($lockUntil) {
                    $this->audit->log('AUTH_ACCOUNT_LOCKED', ['email' => $request->email]);
                }
            }
            // REQ 10.2.4: Log the failed attempt
            $this->audit->loginFailed($request->email, 'invalid_credentials');
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        // ✅ Credentials are correct — log the user in
        // Reset login attempt counter
        DB::table('users')->where('id', $user->id)->update([
            'login_attempts' => 0,
            'locked_until'   => null,
            'last_login_at'  => now(),
        ]);

        // Create the session — store user ID
        // REQ 8.2.8: Session will be managed with timeout by middleware
        Auth::loginUsingId($user->id);
        $request->session()->regenerate(); // New session ID (prevents session fixation)

        // REQ 10.2.1: Log successful login
        $this->audit->loginSuccess($user->id, $user->email);

        // Redirect admin → admin dashboard, customers → shop
        return redirect()->intended(
            $user->role === 'admin' ? route('admin.dashboard') : route('shop.index')
        );
    }

    /**
     * register() — Create a new customer account.
     * REQ 8.3.6: Enforce minimum 12-char password with complexity
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            // REQ 8.3.6: Min 12 chars, must have uppercase, lowercase, number, special char
            'password' => ['required', 'min:12', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'],
        ], [
            'password.regex' => 'Password must have uppercase, lowercase, a number, and a special character.',
            'password.min'   => 'Password must be at least 12 characters.',
        ]);

        $userId = DB::table('users')->insertGetId([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password), // REQ 8.3.1: Always hash passwords
            'role'       => 'customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auth::loginUsingId($userId);
        $this->audit->log('AUTH_REGISTER', ['user_id' => $userId, 'email' => $request->email]);

        return redirect()->route('shop.index')->with('success', 'Account created! Welcome to DrapeStore.');
    }

    /** logout() — Clear session and redirect */
    public function logout(Request $request)
    {
        $this->audit->log('AUTH_LOGOUT', ['user_id' => Auth::id()]);
        Auth::logout();
        $request->session()->invalidate();     // Destroy the session
        $request->session()->regenerateToken(); // New CSRF token
        return redirect()->route('auth.login')->with('success', 'You have been logged out.');
    }
}
