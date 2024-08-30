<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Course;
use App\Models\Admin\Category;
use App\Models\Admin\State;
class BestDealFile extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'best_deal_id',
        'file',
        'images',
        'name',
        'deleted_at'
    ];

    // public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }
    // public function state()
    // {
    //     return $this->belongsTo(State::class);
    // }

}
