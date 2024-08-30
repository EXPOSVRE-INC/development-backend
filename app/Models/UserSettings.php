<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;

    protected $table = 'user_settings';

    protected $fillable = [
        'id',
        'user_id',
        'followersHidden',
        'followedHidden'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
