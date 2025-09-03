<?php

namespace App\Http\Controllers\Admin;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class AdminStoreController extends Controller
{
    public function index()
    {
        // جلب جميع المتاجر بدون تحميل المنتجات
        $stores = Store::all();

        // إرجاع قائمة المتاجر
        return response()->json(['stores' => $stores], 200);
    }

    public function create()
    {
        return response()->json(['message' => 'Provide store details to create a new store.'], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'store_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('store_image')) {
            $imageName = time() . '.' . $request->store_image->extension();
            $request->store_image->move(public_path('StoreImages'), $imageName);
            $validated['store_image'] = $imageName;
        }

        $store = Store::create($validated);

        // إضافة الرابط الكامل لصورة المتجر
        if ($store->store_image) {
            $store->store_image = asset('StoreImages/' . $store->store_image);
        }

        return response()->json(['message' => 'Store added successfully', 'store' => $store], 201);
    }


    public function edit($id)
    {
        $store = Store::findOrFail($id);
        return response()->json(['store' => $store], 200);
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'store_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $store = Store::findOrFail($id);

        if ($request->hasFile('store_image')) {
            $imageName = time() . '.' . $request->store_image->extension();
            $request->store_image->move(public_path('StoreImages'), $imageName);
            $validated['store_image'] = $imageName;
        }

        $store->update($validated);


        if ($store->store_image) {
            $store->store_image = asset('StoreImages/' . $store->store_image);
        }

        return response()->json([
            'message' => 'Store updated successfully',
            'store' => $store,
        ], 200);
    }


    public function destroy($storeId)
    {
        $store = Store::findOrFail($storeId);
        if ($store->store_image && file_exists(public_path('StoreImages/' . $store->store_image))) {
            unlink(public_path('StoreImages/' . $store->store_image));
        }
        $store->delete();

        return response()->json(['message' => 'Store deleted successfully'], 200);
    }

    public function show($id)
    {
        $store = Store::with([
            'products.images',  // الصور المرتبطة بالمنتجات
            'products.colors',  // الألوان المرتبطة بالمنتجات
            'products.types',   // الأنواع المرتبطة بالمنتجات
            'products.sizes',   // القياسات المرتبطة بالمنتجات
        ])->findOrFail($id);

        // تحويل مسار صورة المتجر إلى رابط كامل
        if ($store->store_image) {
            $store->store_image = asset('StoreImages/' . $store->store_image);
        }

        // معالجة المنتجات
        $store->products->each(function ($product) {
            // تحويل الصور إلى روابط كاملة
            $product->image_urls = $product->images->map(function ($image) {
                return asset('ProductImages/' . $image->path);
            });
            $product->colors_array = $product->colors->mapWithKeys(function ($color, $index) {
                return ['Color ' . ($index + 1) => $color->color];
            });
            $product->sizes_array = $product->sizes->mapWithKeys(function ($size, $index) {
                return ['Size ' . ($index + 1) => $size->size];
            });

            // استخراج الأنواع كنصوص
            $product->type_text = $product->types->pluck('type')->join(', ');

            // إزالة الحقول الزائدة لتقليل البيانات
            unset($product->types, $product->images, $product->colors, $product->sizes ,$product->product_image);
        });

        return response()->json(['store' => $store], 200);
    }



}
