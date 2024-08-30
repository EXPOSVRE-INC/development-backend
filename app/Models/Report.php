<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
      'id',
      'reason',
      'status',
      'reporter_id',
      'model',
      'model_id',
    ];
}
