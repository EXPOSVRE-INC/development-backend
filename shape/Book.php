<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Bulk;
use App\Models\Admin\Category;
use App\Models\Admin\State;
class BestDeal extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'state_id',
        'best_deal_id',
        'bulk_id',
        'type',
        'package_name',
        'image',
        'price',
        'status',
        'description',
        'deleted_at',
        'universal_status'
    ];

    // public function course()
    // {
    //     return $this->belongsTo(Course::class);
    // }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function bulk()
    {
        return $this->belongsTo(Bulk::class);
    }

}
