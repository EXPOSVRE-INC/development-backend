<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InterestsPostAssigment extends Model
{
    protected $table = 'interests_post_assignments';

    protected $fillable = ['interest_id', 'post_id'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'id', 'post_id');
    }

    public function interests()
    {
        return $this->belongsToMany(Post::class, 'id', 'post_id');
    }

    public function isForInterest($id): bool
    {
        return $this->interest_id == $id;
    }

}
