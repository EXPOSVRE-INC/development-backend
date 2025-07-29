<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'post_id',
        'status',
        'offered_price'
    ];

    public function requestor()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function post()
    {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
}
