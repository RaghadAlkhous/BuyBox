<?php
namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Display user's favorites
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()
            ->with('product.store', 'product.images', 'product.types')
            ->get();

        if ($favorites->isEmpty()) {
            return response()->json(['message' => 'No favorites found'], 400);
        }

        $formattedFavorites = $favorites->map(function ($favorite) {
            $product = $favorite->product;

            if ($product) {
                return [
                    'id' => $favorite->id,
                    'store_name' => $product->store->name ?? null,
                    'name' => $product->name,
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'store_id' => $product->store_id,
                    'type_text' => $product->types->pluck('type')->join(', '),
                    'created_at' => $favorite->created_at,
                    'updated_at' => $favorite->updated_at,
                    'image_urls' => $product->images->map(fn($image) => asset('ProductImages/' . $image->path)),
                ];
            }
            return null;
        })->filter(); // للتأكد من إزالة العناصر الفارغة

        return response()->json([
            'message' => 'User favorites retrieved successfully', 'favorites' => $formattedFavorites], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
    
        // التحقق إذا كان المنتج موجودًا بالفعل في المفضلة
        $existingFavorite = Favorite::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();
    
        if ($existingFavorite) {
            return response()->json([
                'message' => 'The product is already in favorites',
            ], 409); // 409 Conflict
        }
    
        // إضافة المنتج إلى المفضلة
        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);
    
        $product = Product::with(['store', 'images', 'types'])->find($request->product_id);
    
        // بناء المنتج بالشكل المطلوب
        $formattedProduct = null;
        if ($product) {
            $formattedProduct = [
                'store_name' => $product->store->name ?? null,
                'id' => $favorite->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'store_id' => $product->store_id,
                'type_text' => $product->types->pluck('type')->join(', '),
                'created_at' => $favorite->created_at,
                'updated_at' => $favorite->updated_at,
                'image_urls' => $product->images->map(fn($image) => asset('ProductImages/' . $image->path)),
            ];
        }
    
        return response()->json([
            'message' => 'Product added to favorites',
            'id' => $favorite->id,
            'favorite' => $formattedProduct,
        ], 201);
    }


    // Remove a product from favorites
    public function destroy($id)
    {
        $favorite = Favorite::where('user_id', Auth::id())->where('id', $id)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Product removed from favorites'], 200);
        }

        return response()->json(['message' => 'Favorite item not found'], 404);
    }
}
