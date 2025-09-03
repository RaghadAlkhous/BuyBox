<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price', 
        'color_id', 
        'mobile_type_id', 
        'size_id'
    ];

    public function color()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function mobileType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function size()
    {
        return $this->belongsTo(ProductSize::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

        // حساب السعر الإجمالي
        public function getTotalPriceAttribute()
        {
            return $this->quantity * $this->price;
        }
}
