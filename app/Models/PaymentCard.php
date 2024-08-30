<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'holder',
        'stripeToken',
        'last4',
        'isActive',
        'user_id'
    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
