<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Http\Controllers\Controller;
use App\Models\ProductColor;

class AdminProductController extends Controller
{
    public function create($storeId)
    {
        $store = Store::findOrFail($storeId);
        return response()->json(['store' => $store], 200);
    }

    public function store(Request $request, $storeId)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'colors' => 'nullable|array',
                'colors.*' => 'string',
                'types' => 'nullable|array',
                'types.*' => 'string',
                'sizes' => 'nullable|array',
                'sizes.*' => 'string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        $store = Store::findOrFail($storeId);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $product = $store->products()->create($validated);

        $imageUrls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . uniqid() . '.' . $image->extension();
                $image->move(public_path('ProductImages'), $imageName);
                $product->images()->create(['path' => $imageName]);
                $imageUrls[] = asset('ProductImages/' . $imageName);
            }
        }

        // إضافة الألوان للمنتج
        $colorsArray = [];
        if ($request->has('colors')) {
            foreach ($request->colors as $color) {
                $createdColor = ProductColor::create([
                    'product_id' => $product->id,
                    'color' => $color,
                ]);
                $colorsArray[] = $createdColor->color;
            }
        }

        // إضافة الأنواع للمنتج
        $typesArray = [];
        if ($request->has('types')) {
            foreach ($request->types as $type) {
                $createdType = $product->types()->create(['type' => $type]);
                $typesArray[] = $createdType->type;
            }
        }

        // إضافة القياسات للمنتج
        $sizesArray = [];
        if ($request->has('sizes')) {
            foreach ($request->sizes as $size) {
                $createdSize = $product->sizes()->create(['size' => $size]);
                $sizesArray[] = $createdSize->size;
            }
        }

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product,
            'image_urls' => $imageUrls,
            'colors' => $colorsArray,
            'types' => $typesArray,
            'sizes' => $sizesArray
        ], 201);
    }

    public function update(Request $request, $storeId, $productId)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'colors' => 'nullable|array', // قبول الألوان كـ مصفوفة
            'colors.*' => 'string', // التحقق من أن كل لون نصي
            'types' => 'nullable|array', // قبول الأنواع كـ مصفوفة
            'types.*' => 'string', // التحقق من أن كل نوع نصي
            'sizes' => 'nullable|array', // قبول القياسات كـ مصفوفة
            'sizes.*' => 'string', // التحقق من أن كل قياس نصي
        ]);

        $store = Store::findOrFail($storeId);
        $product = $store->products()->findOrFail($productId);

        $product->update($validated);


        $imageUrls = [];
        // معالجة الصور
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . uniqid() . '.' . $image->extension();
                $imagePath = 'ProductImages/' . $imageName;

                // حساب hash الصورة
                $imageHash = md5_file($image->getRealPath());

                // تحقق إذا كانت الصورة بنفس الـ hash
                $existingImage = $product->images->firstWhere('hash', $imageHash);

                // إذا لم تكن الصورة موجودة، أضفها
                if (!$existingImage) {
                    $image->move(public_path('ProductImages'), $imageName);
                    $product->images()->create([
                        'path' => $imageName,
                        'hash' => $imageHash, // حفظ الـ hash في قاعدة البيانات
                    ]);
                }
                $imageUrls[] = asset('ProductImages/' . $imageName);
            }
        }

        // تحديث الألوان
        $colorsArray = [];
        if ($request->has('colors')) {
            $product->colors()->delete(); // حذف الألوان القديمة
            foreach ($request->colors as $color) {
                $createdColor = ProductColor::create([
                    'product_id' => $product->id,
                    'color' => $color,
                ]);
                $colorsArray[] = $createdColor->color;
            }
        }

        // تحديث الأنواع
        $typesArray = [];
        if ($request->has('types')) {
            $product->types()->delete(); // حذف الأنواع القديمة
            foreach ($request->types as $type) {
                $createdType = $product->types()->create(['type' => $type]);
                $typesArray[] = $createdType->type;
            }
        }

        // تحديث القياسات
        $sizesArray = [];
        if ($request->has('sizes')) {
            $product->sizes()->delete(); // حذف القياسات القديمة
            foreach ($request->sizes as $size) {
                $createdSize = $product->sizes()->create(['size' => $size]);
                $sizesArray[] = $createdSize->size;
            }
        }

        // تحديث مسارات الصور
        foreach ($product->images as $image) {
            $image->path = asset('ProductImages/' . $image->path);
        }

        unset($product->images, $product->product_image);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
            'image_urls' => $imageUrls,
            'colors' => $colorsArray,
            'types' => $typesArray,
            'sizes' => $sizesArray
        ], 200);
    }

    public function destroy($storeId, $productId)
    {
        $store = Store::findOrFail($storeId);
        $product = $store->products()->findOrFail($productId);

        foreach ($product->images as $image) {
            if (file_exists(public_path('ProductImages/' . $image->path))) {
                unlink(public_path('ProductImages/' . $image->path));
            }
            $image->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
