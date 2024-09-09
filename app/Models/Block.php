<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;
    protected $table = 'blocks';
    protected $fillable = [
      'id',
      'user_id',
      'blocking_id',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
}

// The user who is blocked
public function blockingUser()
{
    return $this->belongsTo(User::class, 'blocking_id', 'id');
}
}
