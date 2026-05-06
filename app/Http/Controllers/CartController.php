<?php
// ============================================================
// app/Http/Controllers/CartController.php
// Manages the shopping cart stored in the PHP session.
//
// The cart lives in the user's session — not the database.
// This is secure and PCI-friendly because no payment data
// is stored server-side during the shopping phase.
// ============================================================
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /** View the cart page */
    public function index()
    {
        $cart  = session('cart', []);       // Get cart from session (empty array if none)
        $total = $this->calculateTotal($cart);
        return view('cart.index', compact('cart', 'total'));
    }

    /** Add a product to the cart */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'size'       => 'required|string|max:10',
            'quantity'   => 'required|integer|min:1|max:10',
        ]);

        $product = DB::table('products')->find($request->product_id);

        // Cart key = product_id + size (so same shirt in different sizes = different cart items)
        $cartKey = $request->product_id . '_' . $request->size;
        $cart    = session('cart', []);

        if (isset($cart[$cartKey])) {
            // Already in cart — increase quantity
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            // New cart item
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'size'       => $request->size,
                'quantity'   => $request->quantity,
                'image_url'  => $product->image_url,
            ];
        }

        session(['cart' => $cart]);
        return redirect()->back()->with('success', "'{$product->name}' added to your cart!");
    }

    /** Update quantity of a cart item */
    public function update(Request $request)
    {
        $request->validate(['cart_key' => 'required|string', 'quantity' => 'required|integer|min:0|max:10']);
        $cart = session('cart', []);

        if ($request->quantity == 0) {
            unset($cart[$request->cart_key]); // Remove if qty = 0
        } else {
            if (isset($cart[$request->cart_key])) {
                $cart[$request->cart_key]['quantity'] = $request->quantity;
            }
        }
        session(['cart' => $cart]);
        return redirect()->route('cart.index');
    }

    /** Remove one item from cart */
    public function remove(Request $request)
    {
        $cart = session('cart', []);
        unset($cart[$request->cart_key]);
        session(['cart' => $cart]);
        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /** Clear the entire cart */
    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }

    /** Calculate cart totals (subtotal, tax, total) */
    public static function calculateTotal(array $cart): array
    {
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $tax      = round($subtotal * 0.07, 2);  // 7% tax
        return [
            'subtotal'   => round($subtotal, 2),
            'tax'        => $tax,
            'total'      => round($subtotal + $tax, 2),
            'item_count' => collect($cart)->sum('quantity'),
        ];
    }
}
