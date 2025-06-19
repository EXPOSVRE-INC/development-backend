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
        'followedHidden',
        'notify_phone_verification',
        'notify_new_message',
        'notify_new_comment',
        'notify_new_crowned_post',
        'notify_new_follow',
        'notify_new_sale',
        'notify_price_request'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
