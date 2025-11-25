<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\LaravelCommentable\Traits\Commentable;


class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'comment',
        'ip_address',
        'user_agent',
    ];

    /**
     * Comment constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('commentable.table_name'));
    }
}
