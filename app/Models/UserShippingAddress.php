<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShippingAddress extends Model
{
    use HasFactory;

    protected $table = 'user_shipping_address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country',
        'state',
        'city',
        'zip',
        'address',
        'lat',
        'lon',
        'user_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
