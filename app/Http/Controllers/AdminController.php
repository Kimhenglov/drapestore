<?php
// ============================================================
// app/Http/Controllers/AdminController.php
// PCI REQ 7: Admin-only management pages
// PCI REQ 10: View audit logs
// ============================================================
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class AdminController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    /** Admin dashboard — overview stats */
    public function dashboard()
    {
        $stats = [
            'total_orders'    => DB::table('orders')->count(),
            'total_revenue'   => DB::table('orders')->where('status', 'paid')->sum('total'),
            'total_products'  => DB::table('products')->where('is_active', true)->count(),
            'total_users'     => DB::table('users')->where('role', 'customer')->count(),
            'recent_orders'   => DB::table('orders')->orderByDesc('created_at')->limit(5)->get(),
            'recent_logs'     => DB::table('audit_logs')->orderByDesc('created_at')->limit(8)->get(),
        ];
        $this->audit->adminAction('VIEW_DASHBOARD');
        return view('admin.dashboard', compact('stats'));
    }

    /** View all orders */
    public function orders()
    {
        $orders = DB::table('orders')->orderByDesc('created_at')->paginate(20);
        $this->audit->adminAction('VIEW_ORDERS');
        return view('admin.orders', compact('orders'));
    }

    /** View and manage products */
    public function products()
    {
        $products = DB::table('products')->orderByDesc('created_at')->get();
        $this->audit->adminAction('VIEW_PRODUCTS');
        return view('admin.products', compact('products'));
    }

    /** Add a new product */
    public function storeProduct(Request $request)
    {
        $v = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0.01',
            'category'    => 'required|in:tops,bottoms,dresses,accessories,outerwear',
            'image_url'   => 'required|url',
            'stock'       => 'required|integer|min:0',
            'size_options'=> 'required|string',
        ]);

        DB::table('products')->insert(array_merge($v, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        $this->audit->adminAction('PRODUCT_CREATED', ['name' => $v['name']]);
        return redirect()->route('admin.products')->with('success', 'Product added successfully.');
    }

    /** Delete a product */
    public function deleteProduct($id)
    {
        DB::table('products')->where('id', $id)->update(['is_active' => false]);
        $this->audit->adminAction('PRODUCT_DELETED', ['product_id' => $id]);
        return redirect()->route('admin.products')->with('success', 'Product removed.');
    }

    /** View audit logs — REQ 10 */
    public function auditLogs()
    {
        $logs = DB::table('audit_logs')->orderByDesc('created_at')->paginate(30);
        $this->audit->adminAction('VIEW_AUDIT_LOGS');
        return view('admin.audit', compact('logs'));
    }
}
