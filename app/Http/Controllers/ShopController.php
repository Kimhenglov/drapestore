<?php
// ============================================================
// app/Http/Controllers/ShopController.php
// Shows the product listing and individual product pages
// ============================================================
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /** /shop — Product listing with filters */
    public function index(Request $request)
    {
        $query = DB::table('products')->where('is_active', true);

        // Filter by category (tops, bottoms, dresses, etc.)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        // Filter by price range
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products   = $query->orderBy('created_at', 'desc')->get();
        $categories = DB::table('products')->select('category')->distinct()->pluck('category');

        return view('shop.index', compact('products', 'categories'));
    }

    /** /shop/{id} — Single product detail page */
    public function show($id)
    {
        $product = DB::table('products')->where('id', $id)->where('is_active', true)->first();
        if (!$product) abort(404);

        // Related products (same category, different product)
        $related = DB::table('products')
            ->where('category', $product->category)
            ->where('id', '!=', $id)
            ->where('is_active', true)
            ->limit(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
