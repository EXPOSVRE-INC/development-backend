<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InterestsUserAssigment extends Model
{
    protected $table = 'interests_user_assigments';

    protected $fillable = ['interest_id', 'user_id', 'type'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'id', 'user_id');
    }

    public function isForInterest($id): bool
    {
        return $this->interest_id == $id;
    }

    public function interest()
    {
        return $this->hasOne(InterestsCategory::class, 'id', 'interest_id');
    }
}
