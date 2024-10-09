<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $table = 'chats';
    protected $fillable = ['conversation_id', 'from','to', 'message', 'received', 'removed'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class , 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

}
