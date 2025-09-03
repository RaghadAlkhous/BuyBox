<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{

    private $fcmService;

    public function __construct(){
        $this->fcmService = new FcmService();
    }
    // تسجيل حساب جديد
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:drivers,phone',
            'password' => 'required|string|min:6|confirmed',

        ]);

        // إنشاء حساب جديد
        $driver = Driver::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // إنشاء توكن
        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json([
            'message' => 'Driver registered successfully!',
            'token' => $token,
            'driver' => $driver,
        ], 201);
    }

    // تسجيل الدخول
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',

        ]);
        $driver = Driver::where('phone', $request->phone)->first();
        if (!$driver || !Hash::check($request->password, $driver->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'driver' => $driver,
        ], 200);
    }

    // تسجيل الخروج
    public function logout(Request $request)
    {
        // إبطال جميع التوكنات
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // واجهة السائق
    public function dashboard(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the driver dashboard',
            'driver' => $request->user(),
        ], 200);
    }
    public function index()
    {
        $orders = Order::with([
            'details.product.store',
            'details.product.images',
            'details.product.colors',
            'details.product.sizes',
            'details.product.types'
        ])->get();
    
        $formattedOrders = $orders->map(function ($order) {
            return [
                'user_id' => $order->user_id,
                'status' => $order->status,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'order_id' => $order->id,
                'quantity' => $order->details->sum('quantity'),
                'total_price' => $order->details->sum(fn($detail) => $detail->quantity * $detail->price),
                'delivery_price' => $order->details->sum(fn($detail) => ($detail->quantity * $detail->price) * 0.03),
                'total_price_with_delivery' => $order->details->sum(fn($detail) => $detail->quantity * $detail->price) +
                                               $order->details->sum(fn($detail) => ($detail->quantity * $detail->price) * 0.03),
                'product' => $order->details->map(function ($detail) {
                    $product = $detail->product;
                    return [
                        'product_id' => $product->id ?? null,
                        'name' => $product->name ?? null,
                        'price' => number_format($detail->price, 2),
                        'quantity' => $detail->quantity,
                        'image_urls' => $product->images->map(fn($image) => asset('ProductImages/' . $image->path))->toArray(),
                        'selected_color' => $product->colors->first()?->color ?? 'N/A',
                        'selected_size' => $product->sizes->first()?->size ?? 'N/A',
                        'type_text' => $product->types->first()?->type ?? 'N/A',
                        'store_name' => $product->store->name ?? 'Unknown',
                        'store_id' => $product->store_id,
                    ];
                })->toArray(),
            ];
        });
    
        return response()->json(['orders' => $formattedOrders], 200);
    }

    // تحديث حالة الطلب
    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,delivered', // الحالات المتاحة
        ]);
        $order = Order::findOrFail($orderId);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }
        // إذا كانت الحالة هي "delivered"، نقوم بحذف الطلب
        if ($request->status === 'delivered') {
            $order->delete();
            return response()->json([
                'message' => 'Order marked as delivered and deleted successfully.'
            ]);
        }
        $order->update(['status' => $request->status]);

        $this->fcmService->notifyUsers($order->status);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order
        ]);
    }
}