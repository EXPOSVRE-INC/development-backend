<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveExpirience extends Model
{
    use HasFactory;

    protected $table = 'posts_live_expirience';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'post_id',
        'startUnixTime',
        'finalUnixTime'
        ];

    public function post() {
        return $this->belongsTo(Post::class);
    }
}
