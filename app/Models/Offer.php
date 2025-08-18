<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'post_id',
        'message',
        'offered_price',
        'status',
        'made_by',
        'source'
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
