<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelLike\Traits\Likeable;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserProfile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Likeable;

    protected $table = 'user_profile';

    protected $fillable = [
        'firstName',
        'lastName',
        'birthDate',
        'phone',
        'user_id',
        'jobTitle',
        'jobDescription',
        'website',
        'instagram',
        'twitter'
    ];

    protected $casts = [
        'birthDate' => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }
}
