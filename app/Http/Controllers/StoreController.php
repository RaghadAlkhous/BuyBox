<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // عرض المتاجر لجميع المستخدمين
    public function index()
    {
        $stores = Store::all();

        return response()->json($stores, 200);
    }

    public function show($id)
    {
        $store = Store::with(['products.images', 'products.colors', 'products.sizes', 'products.types'])->find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }
        if ($store->store_image) {
            $store->store_image = asset('StoreImages/' . $store->store_image);
        }

        // تعديل بناء المنتجات بالشكل المطلوب
        $store->products->each(function ($product) {
            $product->image_urls = $product->images->map(function ($image) {
                return asset('ProductImages/' . $image->path);
            });
            $product->colors_array = $product->colors->mapWithKeys(function ($color, $index) {
                return ['Color ' . ($index + 1) => $color->color];
            });
            $product->sizes_array = $product->sizes->mapWithKeys(function ($size, $index) {
                return ['Size ' . ($index + 1) => $size->size];
            });
            $product->type_text = $product->types->pluck('type')->join(', ');
            unset($product->types, $product->images, $product->colors, $product->sizes, $product->product_image);
        });

        return response()->json($store, 200);
    }


    // للبحث في المتاجر
    public function storeSearch(Request $request)
    {
        $request->validate(['query' => 'required|string|max:255']);

        $query = $request->input('query');
        $stores = Store::where('name', 'LIKE', "%$query%")->get();

        foreach ($stores as $store) {
            if ($store->store_image && !str_starts_with($store->store_image, 'http')) {
                $store->store_image = asset('StoreImages/' . $store->store_image);
            }
        }
        return response()->json(['message' => 'Search result', 'data' => $stores], 200);
    }
}
