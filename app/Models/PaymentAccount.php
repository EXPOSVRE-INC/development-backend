<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripeId',
        'accountNumber',
        'nameOfBank',
        'addressOfBank',
        'city',
        'routingNumber',
        'state',
        'zipCode',
        'isActive',
        'user_id'
    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
