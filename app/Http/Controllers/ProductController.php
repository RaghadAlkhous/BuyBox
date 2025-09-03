<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // عرض جميع المنتجات
    public function index()
    {
        $products = Product::with(['store', 'images', 'colors', 'sizes', 'types'])->get();

        // تعديل بناء المنتج بالشكل المطلوب
        $products->each(function ($product) {
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
            if ($product->store && $product->store->store_image) {
                $product->store->store_image = asset('StoreImages/' . $product->store->store_image);
            }
            unset($product->types, $product->images, $product->colors, $product->sizes, $product->product_image);
        });

        return response()->json($products, 200);
    }


    // عرض منتج معين

    public function show($id)
    {
        $product = Product::with(['store', 'images', 'colors', 'sizes', 'types'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
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
        if ($product->store && $product->store->store_image) {
            $product->store->store_image = asset('StoreImages/' . $product->store->store_image);
        }
        unset($product->types, $product->images, $product->colors, $product->sizes, $product->product_image);

        return response()->json([ 
            'available_quantity' => $product->quantity, 
            'product' => $product, ], 200);
    }

    public function productSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'store_id' => 'required|exists:stores,id', // إضافة التحقق من وجود المتجر
        ]);

        $query = $request->input('query');
        $storeId = $request->input('store_id'); // الحصول على معرف المتجر من الطلب

        // البحث عن المنتجات التي تحتوي على النص الموجود في `query` وتنتمي إلى المتجر المحدد
        $products = Product::with(['store', 'images', 'colors', 'sizes', 'types'])
            ->where('name', 'LIKE', "%$query%")
            ->where('store_id', $storeId) // البحث ضمن المتجر المحدد
            ->get();

        // تعديل بناء المنتج بالشكل المطلوب
        $products->each(function ($product) {
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

            if ($product->store && $product->store->store_image) {
                if (!str_starts_with($product->store->store_image, 'http')) {
                    $product->store->store_image = asset('StoreImages/' . $product->store->store_image);
                }
            }

            unset($product->types, $product->images, $product->colors, $product->sizes, $product->product_image);
        });

        return response()->json(['message' => 'Search Result', 'data' => $products], 200);
    }


}
