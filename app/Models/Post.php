<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Orkhanahmadov\LaravelCommentable\Commentable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Overtrue\LaravelLike\Traits\Likeable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTags, Commentable, Likeable, Favoriteable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'title',
        'description',
        'subtitle',
        'collection_post',
        'post_for_sale',
        'collection_id',
        'unlimited_edition',
        'limited_addition_number',
        'physical_item',
        'time_sale_from_date',
        'time_sale_to_date',
        'fixed_price',
        'royalties_percentage',
        'allow_to_comment',
        'allow_views',
        'exclusive_content',
        'owner_id',
        'views_count',
        'views_by_last_day',
        'likes_count',
        'order_priority',
        'parent_id',
        'is_archived',
        'nudity',
        'type',
        'currency',
        'typeOfPrice',
        'isFree',
        'shippingIncluded',
        'shippingPrice',
        'ad',
        'status',
        'publish_date',
        'link',
        'isArticle',
        'author'
    ];

    public function owner()
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }

    public function collection()
    {
        return $this->belongsTo(PostCollection::class);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->extractVideoFrameAtSecond(1)
//            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->width(300)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();
        $this
            ->addMediaConversion('small')
            ->extractVideoFrameAtSecond(1)
//            ->fit(Manipulations::FIT_CROP, 48, 48)
            ->width(48)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();

        $this
            ->addMediaConversion('original')
            ->extractVideoFrameAtSecond(1)
//            ->fit(Manipulations::FIT_CROP, 640, 640)
            ->width(640)
            ->quality(100)
            ->sharpen(10)
            ->nonQueued();
    }

    public function tags()
    {
        return $this
            ->morphToMany(Tag::getTagClassName(), 'taggable', 'taggables', null, 'tag_id');
    }

    public function assignInterest($id): void
    {
        $assignments = $this->interestAssignments;
        foreach ($assignments as $assignment) {
            if ($assignment->isForInterest($id)) {
                return;
            }
        }
        $assignments[] = InterestsPostAssigment::create(['interest_id' => $id, 'post_id' => $this->id]);
//        $this->interestAssignments = $assignments;
    }

    public function revokeInterest($id): void
    {
        $assignments = $this->interestAssignments;
        foreach ($assignments as $i => $assignment) {
            if ($assignment->isForInterest($id)) {
                unset($assignments[$i]);
                $this->interestAssignments = $assignments;
                return;
            }
        }
        throw new \DomainException('Interest is not found.');
    }

    public function revokeInterests(): void
    {
        $this->interestAssignments = [];
    }

    public function interestAssignments()
    {
        return $this->hasMany(InterestsPostAssigment::class, 'post_id', 'id');
    }

    public function interests()
    {
        return $this->hasManyThrough(InterestsCategory::class, InterestsPostAssigment::class, 'post_id', 'id', 'id', 'interest_id');
    }

    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function intervals() {
        return $this->hasMany(LiveExpirience::class, 'post_id', 'id');
    }

    public function orders() {
        return $this->hasMany(Order::class, 'post_id', 'id');
    }

    public function requests() {
        return $this->hasMany(PriceRequest::class, 'post_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'model_id', 'id')->where(['model' => 'post']);
    }

    public function likersByLastDay(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            config('like.likes_table'),
            'likeable_id',
            config('like.user_foreign_key')
        )
            ->where('likeable_type', $this->getMorphClass())
            ->whereDate('likes.created_at', '>=', Carbon::today()->subDays(1));
    }
}
