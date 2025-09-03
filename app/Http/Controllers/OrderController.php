<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with([
            'details.product.store',
            'details.product.images',
            'details.product.colors',
            'details.product.sizes',
            'details.product.types'
        ])->get();

        // Formatting orders and product details
        $orders = $orders->map(function ($order) {
            $formattedOrder = [
                'user_id' => $order->user_id,
                'status' => $order->status,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'order_id' => $order->id,
                'quantity' => $order->details->sum('quantity'),
                'total_price' => $order->details->sum(fn($detail) => $detail->quantity * $detail->price),
                'delivery_price' => $order->details->sum(fn($detail) => ($detail->quantity * $detail->price) * 0.03),
                'total_price_with_delivery' => $order->details->sum(fn($detail) => $detail->quantity * $detail->price) + $order->details->sum(fn($detail) => ($detail->quantity * $detail->price) * 0.03),
                'product' => $order->details->map(function ($detail) {
                    $product = $detail->product;

                    return [
                        'product_id' => $product->id ?? null,
                        'name' => $product->name ?? null,
                        'price' => $detail->price,
                        'quantity' => $detail->quantity,
                        'image_urls' => $product->images->map(fn($image) => asset('ProductImages/' . $image->path))->toArray(),
                        'selected_color' => $product->colors->first()->color ?? null,
                        'selected_size' => $product->sizes->first()->size ?? '.',
                        'type_text' => $product->types->first()->type ?? '.',
                        'store_name' => $product->store->name ?? null,
                        'store_id' => $product->store_id,
                    ];
                })->toArray(),
            ];

            return $formattedOrder;
        });

        return response()->json(['orders' => $orders]);
    }

    public function store(Request $request) {}
}