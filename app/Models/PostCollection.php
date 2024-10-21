<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\LaravelCommentable\Commentable;
use Overtrue\LaravelLike\Traits\Likeable;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostCollection extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Commentable, Likeable;

    protected $table = 'posts_collection';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'allowToComment',
        'allowToCrown',
        'user_id',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'collection_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('small')
            ->extractVideoFrameAtSecond(1)
            ->fit(Manipulations::FIT_CROP, 48, 48)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('original')
            ->extractVideoFrameAtSecond(1)
            ->fit(Manipulations::FIT_CROP, 640, 640)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();
    }
}
