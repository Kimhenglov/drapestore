<?php
// ============================================================
// app/Services/AuditLogger.php
//
// PCI DSS REQ 10: Log every security event with tamper-proof checksum.
// ============================================================

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Log a security event to the audit_logs table.
     */
    public function log(string $eventType, array $metadata = [], string $description = ''): void
    {
        // Auto-generate description if not provided
        if (!$description) {
            $description = $this->describeEvent($eventType, $metadata);
        }

        // REQ 3.3: Sanitize - remove any sensitive fields
        $metadata = $this->sanitize($metadata);

        try {
            DB::table('audit_logs')->insert([
                'event_type'  => $eventType,
                'user_id'     => Auth::id(),
                'user_email'  => Auth::user()?->email,
                'ip_address'  => request()->ip() ?? '0.0.0.0',
                'user_agent'  => substr(request()->userAgent() ?? '', 0, 500),
                'url'         => request()->url(),
                'description' => $description,
                'metadata'    => json_encode($metadata),
                // REQ 10.3.2: HMAC checksum for tamper detection
                'checksum'    => $this->createChecksum($eventType, $metadata),
                'created_at'  => now(),
            ]);
        } catch (\Exception $e) {
            // If audit log fails, write to Laravel log as backup
            Log::critical('AUDIT_LOG_WRITE_FAILED', [
                'error' => $e->getMessage(),
                'event' => $eventType,
            ]);
        }
    }

    // Convenience methods
    public function loginFailed(string $email, string $reason = ''): void
    {
        $this->log('AUTH_LOGIN_FAILED', ['email' => $email, 'reason' => $reason]);
    }

    public function loginSuccess(int $userId, string $email): void
    {
        $this->log('AUTH_LOGIN_SUCCESS', ['user_id' => $userId, 'email' => $email]);
    }

    public function paymentProcessed(string $orderId, string $status): void
    {
        $this->log('PAYMENT_PROCESSED', ['order_id' => $orderId, 'status' => $status]);
    }

    public function adminAction(string $action, array $details = []): void
    {
        $this->log('ADMIN_ACTION', array_merge(['action' => $action], $details));
    }

    private function createChecksum(string $eventType, array $metadata): string
    {
        $key = env('LOG_HMAC_KEY', 'fallback-change-in-production');
        $payload = $eventType . json_encode($metadata) . now()->toIso8601String();
        return hash_hmac('sha256', $payload, $key);
    }

    private function sanitize(array $data): array
    {
        $forbidden = ['password', 'cvv', 'cvc', 'card_number', 'pan',
                      'full_pan', 'track_data', 'pin', 'cc_number'];
        foreach ($forbidden as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED — PCI REQ 3]';
            }
        }
        return $data;
    }

    private function describeEvent(string $type, array $meta): string
    {
        return match($type) {
            'AUTH_LOGIN_SUCCESS'        => "User logged in: " . ($meta['email'] ?? '?'),
            'AUTH_LOGIN_FAILED'         => "Failed login: " . ($meta['email'] ?? '?'),
            'AUTH_ACCOUNT_LOCKED'       => "Account locked: " . ($meta['email'] ?? '?'),
            'AUTH_LOGOUT'               => "User logged out",
            'AUTH_REGISTER'             => "New user registered",
            'PAYMENT_PROCESSED'         => "Payment for order #" . ($meta['order_id'] ?? '?'),
            'PAYMENT_FAILED'            => "Payment failed for order #" . ($meta['order_id'] ?? '?'),
            'UNAUTHORIZED_ADMIN_ACCESS' => "Unauthorized admin access from " . request()->ip(),
            'SESSION_TIMEOUT_LOGOUT'    => "Session expired after inactivity",
            'ADMIN_ACTION'              => "Admin: " . ($meta['action'] ?? '?'),
            default                     => "Event: {$type}",
        };
    }
}
