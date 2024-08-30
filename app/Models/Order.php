<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'seller_id',
        'buyer_id',
        'post_id',
        'price',
        'status',
        'payment_intent_id',
        'shipping_address_id',
        'shippingMethod',
        'trackingNumber'
    ];

    public function seller() {
        return $this->hasOne(User::class, 'id', 'seller_id');
    }

    public function buyer() {
        return $this->hasOne(User::class, 'id', 'buyer_id');
    }

    public function post() {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }

    public function shippingAddress() {
        return $this->hasOne(UserShippingAddress::class, 'id', 'shipping_address_id');
    }

    public function reject() {
        return $this->hasOne(OrderReject::class, 'order_id', 'id');
    }
}
