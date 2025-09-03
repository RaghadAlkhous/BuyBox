<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // عرض محتويات السلة
    public function index(Request $request)
    {
        $cartItems = $request->user()->cart()->with(['product.store', 'product.images'])->get();
    
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
    
        $totalPrice = 0;
        $totalDeliveryCharge = 0;
        $totalItems = 0;
    
        $cartDetails = $cartItems->map(function ($cartItem) use (&$totalPrice, &$totalDeliveryCharge, &$totalItems) {
            $product = $cartItem->product;
    
            if ($product) {
                // إعداد الصور
                $product->image_urls = $product->images->map(function ($image) {
                    return asset('ProductImages/' . $image->path);
                });
    
                // إعداد صورة المتجر
                if ($product->store && $product->store->store_image) {
                    $product->store->store_image = asset('StoreImages/' . $product->store->store_image);
                }
    
                // القيم المختارة
                $selectedColor = $cartItem->color;
                $selectedSize = $cartItem->size;
                $selectedType = $cartItem->type;
    
                // حساب الإجماليات
                $productPrice = (float) $product->price;
                $totalItemPrice = $cartItem->quantity * $productPrice;
                $deliveryCharge = $totalItemPrice * 0.03; // حساب التوصيل كنسبة 3% من الإجمالي
    
                $totalPrice += $totalItemPrice;
                $totalDeliveryCharge += $deliveryCharge;
                $totalItems += $cartItem->quantity;
    
                return [
                    'id' => $cartItem->id,
                    'user_id' => $cartItem->user_id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'name' => $product->name,
                    'description' => $product->description,
                    'selected_color' => $selectedColor,
                    'selected_size' => $selectedSize,
                    'selected_type' => $selectedType,
                    'store_name' => $product->store->name ?? null,
                    'price' => $product->price,
                    'store_id' => $product->store_id,
                    'image_urls' => $product->image_urls,
                ];
            }
    
            return null;
        })->filter();
    
        $response = [
            'total_price' => $totalPrice,
            'total_delivery_charge' => $totalDeliveryCharge,
            'final_total' => $totalPrice + $totalDeliveryCharge,
            'total_items' => $totalItems,
            'products' => $cartDetails->values(),
        ];
    
        return response()->json($response, 200);
    }
    
    
    
    // إضافة منتج إلى السلة
 
    public function addToCart(Request $request)
    {
        
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
            // 'type' => 'nullable|string',
        ]);
        
        $product = Product::find($validated['product_id']);
        
        if (!$product || $product->quantity < $validated['quantity']) {
            return response()->json([
                'message' => 'Requested quantity not available',
                'available_quantity' => $product->quantity ?? 0
            ], 400);
        }
        
        $cart = Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            [
                'quantity' => $validated['quantity'],
                'color' => $validated['color'],
                'size' => $validated['size'],
             //   'type' => $validated['type'],
            ]
        );
    
   
        return response()->json($cart, 201);
    }
    

    // حذف منتج من السلة
    public function removeFromCart(Request $request, $id)
    {
        $cartItems = $request->user()->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
        $cartItem = $request->user()->cart()->findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Product removed from cart']);
    }

    // تأكيد الطلب
    // تأكيد الطلب
    public function confirmOrder(Request $request)
    {
        $cartItems = $request->user()->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // حساب السعر الإجمالي مع التوصيل لكل العناصر
        $totalPrice = 0;
        $totalDeliveryPrice = 0;

        $cartItems->each(function ($item) use (&$totalPrice, &$totalDeliveryPrice) {
            $productPrice = (float) $item->product->price;
            $itemTotalPrice = $item->quantity * $productPrice;
            $itemDeliveryPrice = $itemTotalPrice * 0.03;

            $totalPrice += $itemTotalPrice;
            $totalDeliveryPrice += $itemDeliveryPrice;

            // إضافة الحقول لحفظها لاحقًا
            $item->total_price = $itemTotalPrice;
            $item->delivery_price = $itemDeliveryPrice;
            $item->total_price_with_delivery = $itemTotalPrice + $itemDeliveryPrice;
        });

        // إنشاء الطلب
        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'confirmed',
            'total_price' => $totalPrice,
            'total_delivery_price' => $totalDeliveryPrice,
            'total_price_with_delivery' => $totalPrice + $totalDeliveryPrice,
        ]);

        foreach ($cartItems as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'total_price' => $item->total_price,
                'delivery_price' => $item->delivery_price,
                'total_price_with_delivery' => $item->total_price_with_delivery,
            ]);

            // تقليل الكمية المتاحة من المنتج
            $item->product->decrement('quantity', $item->quantity);
        }

        // مسح السلة بعد تأكيد الطلب
        $request->user()->cart()->delete();

        return response()->json([
            'message' => 'Order confirmed',
            'order' => $order,
        ]);
    }
}