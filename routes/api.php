<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/', function (Request $request) {
    return "API";
});

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/send-code',[\App\Http\Controllers\Auth\PasswordResetController::class,'sendResetCode']);
Route::post('/verify-code',[\App\Http\Controllers\Auth\PasswordResetController::class,'verifyResetCode']);
Route::post('/reset-password',[\App\Http\Controllers\Auth\PasswordResetController::class,'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    //Routes للمستخدم
    Route::get('logout', [\App\Http\Controllers\AuthController::class, 'logout']);



    Route::post('profile', [\App\Http\Controllers\UserController::class, 'getProfile']); //عرض الملف الشخصي
    Route::post('profile/update', [\App\Http\Controllers\UserController::class, 'updateProfile']); //تحديث الملف الشخصي
    Route::get('/profile/{id}', [\App\Http\Controllers\UserController::class, 'getProfileById']);
    //Routes للسلة
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index']); //عرض محتويات السلة
    Route::post('/cart', [\App\Http\Controllers\CartController::class, 'addToCart']); //إضافة منتج إلى السلة
    Route::delete('/cart/{id}', [\App\Http\Controllers\CartController::class, 'removeFromCart']); // حذف منتج من السلة
    Route::post('/cart/confirm', [\App\Http\Controllers\CartController::class, 'confirmOrder']); // تأكيد الطلب
    Route::get('/order', [\App\Http\Controllers\OrderController::class, 'index']); //عرض الطلب

    // Routes للمفضلة

    Route::get('/favorites', [\App\Http\Controllers\FavoriteController::class, 'index']); //عرض المفضلة
    Route::post('/favorites', [\App\Http\Controllers\FavoriteController::class, 'store']); //اضافة للمفضلة
    Route::delete('/favorites/{id}', [\App\Http\Controllers\FavoriteController::class, 'destroy']); //حذف من المفضلة

    // Routes للمتاجر
    Route::get('/stores', [\App\Http\Controllers\StoreController::class, 'index']); // عرض جميع المتاجر
    Route::get('/stores/{id}', [\App\Http\Controllers\StoreController::class, 'show']); // عرض متجر معين
    Route::get('store/search', [\App\Http\Controllers\StoreController::class, 'storeSearch']); //للبحث عن المتاجر

    // Routes للمنتجات
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']); // عرض جميع المنتجات
    Route::get('/products/{id}', [\App\Http\Controllers\ProductController::class, 'show']); // عرض منتج معين
    Route::get('product/search', [\App\Http\Controllers\ProductController::class, 'productSearch']); //للبحث عن المنتجات
    Route::post('change-password',[\App\Http\Controllers\UserController::class,'changePassword']);
    Route::post('forgot-password',[\App\Http\Controllers\UserController::class,'forgotPassword']);
    Route::post('reset-user-password',[\App\Http\Controllers\UserController::class,'resetPassword']);
});



use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminStoreController;

Route::prefix('admin')->group(function () {
    // تسجيل الدخول والتسجيل
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout'])->middleware('auth:sanctum');

    // المسارات المحمية (تتطلب مصادقة و صلاحيات مدير)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // إدارة المتاجر
        Route::prefix('stores')->group(function () {
            Route::get('/', [AdminStoreController::class, 'index']);
            Route::post('/', [AdminStoreController::class, 'store']);
            Route::get('/{id}', [AdminStoreController::class, 'show']);
            Route::post('/{id}', [AdminStoreController::class, 'update']);
            Route::delete('/{id}', [AdminStoreController::class, 'destroy']);
        });

        // إدارة المنتجات
        Route::prefix('stores/{storeId}/products')->group(function () {
            Route::post('/', [AdminProductController::class, 'store']);
            Route::post('/{productId}', [AdminProductController::class, 'update']);
            Route::delete('/{productId}', [AdminProductController::class, 'destroy']);
        });
    });
});
use App\Http\Controllers\DriverController;
Route::post('/drivers/register', [DriverController::class, 'register']);
Route::post('/drivers/login', [DriverController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {      
      Route::get('drivers/logout', [DriverController::class, 'logout']);    // إدارة الطلبات  
        Route::get('drivers/orders', [DriverController::class, 'index']);    
        Route::patch('drivers/orders/{orderId}/status', [DriverController::class, 'updateStatus']);});

