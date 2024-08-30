<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReject extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestMessage',
        'responseMessage',
        'order_id'
    ];

    public function order() {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }
}
