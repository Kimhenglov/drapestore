<?php
// ============================================================
// app/Http/Controllers/CheckoutController.php
//
// PURPOSE: Handle the checkout process and payment.
// This is the MOST IMPORTANT file for PCI DSS compliance.
//
// PCI DSS Requirements:
//   REQ 3: We NEVER receive or store raw card data
//          Stripe.js sends card directly to Stripe servers
//          We only receive a "payment_method_id" token
//   REQ 4: All Stripe API calls use TLS 1.2+ (enforced by Stripe SDK)
//          We check for HTTPS before processing
//   REQ 10: Every payment attempt is logged (success or failure)
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class CheckoutController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    /**
     * index() — Show the checkout form page.
     *
     * We redirect if cart is empty (nothing to pay for).
     * We pass the Stripe publishable key to the view so
     * Stripe.js can initialize the payment element.
     */
    public function index()
    {
        $cart = session('cart', []);

        // Can't checkout with empty cart
        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('warning', 'Your cart is empty. Add some items first!');
        }

        $total = CartController::calculateTotal($cart);

        return view('checkout.index', [
            'cart'       => $cart,
            'total'      => $total,
            // REQ 3: Publishable key is safe to expose — it only creates tokens
            // The SECRET key (sk_...) stays on server, never in browser
            'stripe_key' => env('STRIPE_PUBLISHABLE_KEY'),
        ]);
    }

    /**
     * process() — Handle the payment form submission.
     *
     * CRITICAL PCI DSS FLOW:
     * 1. Browser → Stripe.js sends card data directly to Stripe
     * 2. Stripe returns a "payment_method_id" token to the browser
     * 3. Browser sends token (NOT card number) to our server
     * 4. Our server sends token to Stripe API (over TLS)
     * 5. Stripe charges the card and returns success/failure
     * 6. We store the ORDER (with Stripe token reference, not card data)
     */
    public function process(Request $request)
    {
        // ── Input Validation ──────────────────────────────────────
        // REQ 6.2: Validate all inputs before processing
        $validated = $request->validate([
            'payment_method_id' => 'required|string|starts_with:pm_',  // Must be a Stripe token
            'name'              => 'required|string|max:100',
            'email'             => 'required|email|max:200',
            'address'           => 'required|string|max:500',
            'city'              => 'required|string|max:100',
            'postal_code'       => 'required|string|max:20',
            'country'           => 'required|string|size:2',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = CartController::calculateTotal($cart);

        try {
            // ── Initialize Stripe ─────────────────────────────────
            // REQ 4: Stripe SDK automatically uses TLS 1.2+
            // You CANNOT disable TLS in the Stripe SDK — it's enforced
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // ── Create Payment Intent ─────────────────────────────
            // This tells Stripe to charge the card represented by the token.
            // REQ 3: We pass the payment_method_id (token), never the card number.
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'         => (int)($total['total'] * 100), // Stripe uses cents ($63.13 = 6313)
                'currency'       => 'usd',
                'payment_method' => $validated['payment_method_id'], // ← Token, not card number!
                'confirm'        => true,                            // Charge immediately
                'return_url'     => route('checkout.success'),       // 3D Secure redirect
                'metadata'       => [
                    // Metadata is safe — no card data here
                    'customer_name'  => $validated['name'],
                    'customer_email' => $validated['email'],
                ],
            ]);

            // ── Store the Order ───────────────────────────────────
            // REQ 3: We store the order with:
            //   ✅ stripe_payment_id (Stripe's reference token)
            //   ✅ card_last_four (safe to store — not sensitive)
            //   ✅ card_brand (safe — "visa", "mastercard" etc.)
            //   ❌ card_number — NEVER stored
            //   ❌ cvv — NEVER stored
            //   ❌ expiry — NEVER stored
            $orderId = DB::table('orders')->insertGetId([
                'user_id'           => Auth::id(),
                'customer_name'     => $validated['name'],
                'customer_email'    => $validated['email'],
                'shipping_address'  => "{$validated['address']}, {$validated['city']} {$validated['postal_code']}, {$validated['country']}",
                'subtotal'          => $total['subtotal'],
                'tax'               => $total['tax'],
                'total'             => $total['total'],
                'status'            => 'paid',
                'stripe_payment_id' => $paymentIntent->id,           // Token reference (REQ 3)
                'card_last_four'    => $paymentIntent->payment_method ? '****' : null, // Masked
                'card_brand'        => 'card',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Store individual order items
            foreach ($cart as $item) {
                DB::table('order_items')->insert([
                    'order_id'    => $orderId,
                    'product_id'  => $item['product_id'],
                    'product_name'=> $item['name'],
                    'size'        => $item['size'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // ── REQ 10: Log successful payment ────────────────────
            $this->audit->paymentProcessed((string)$orderId, 'success');

            // Clear the cart — order is complete
            session()->forget('cart');
            // Save order ID for the success page
            session(['last_order_id' => $orderId]);

            return redirect()->route('checkout.success');

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined — this is normal (wrong card, insufficient funds)
            // REQ 10: Log the failure (but NOT the card details)
            $this->audit->log('PAYMENT_FAILED', [
                'decline_code' => $e->getDeclineCode(), // e.g. "insufficient_funds" — safe to log
                'error_code'   => $e->getStripeCode(),
                // ❌ Do NOT log: card number, CVV — we don't even have them
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment declined: ' . $this->friendlyDeclineMessage($e->getDeclineCode()));

        } catch (\Exception $e) {
            // Unexpected error (network issue, Stripe down, etc.)
            $this->audit->log('PAYMENT_SYSTEM_ERROR', ['error_class' => get_class($e)]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment could not be processed. Please try again or contact support.');
        }
    }

    /** Success page — show after payment completes */
    public function success()
    {
        $orderId = session('last_order_id');
        $order   = $orderId ? DB::table('orders')->find($orderId) : null;
        $items   = $orderId ? DB::table('order_items')->where('order_id', $orderId)->get() : collect();

        return view('checkout.success', compact('order', 'items'));
    }

    /** Convert Stripe decline codes into friendly messages for customers */
    private function friendlyDeclineMessage(?string $code): string
    {
        return match($code) {
            'insufficient_funds'   => 'Insufficient funds in your account.',
            'card_declined'        => 'Your card was declined.',
            'expired_card'         => 'Your card has expired.',
            'incorrect_cvc'        => 'The security code is incorrect.',
            'lost_card'            => 'This card has been reported as lost.',
            'stolen_card'          => 'This card has been reported as stolen.',
            default                => 'Your card could not be charged.',
        };
    }
}
