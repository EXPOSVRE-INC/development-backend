<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Orkhanahmadov\LaravelCommentable\Commentable;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Overtrue\LaravelLike\Traits\Liker;
use Spatie\Image\Manipulations;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Tags\HasTags;
use TimGavin\LaravelBlock\LaravelBlock;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements JWTSubject, HasMedia
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasTags, Liker, Favoriter, Commentable;
    use LaravelBlock;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'phoneIsActivated',
        'isConfirmed',
        'stripeCustomerId',
        'stripeAccountId',
        'pushToken',
        'is_admin',
        'status',
        'verify'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForApn()
    {
        return $this->pushToken;
    }

    protected function getDefaultGuardName(): string { return 'web'; }

    public function isAdmin(): bool
    {
        return $this->is_admin == true;
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Return the user's posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'owner_id', 'id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Add a mutator to ensure hashed passwords
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();

        $this
            ->addMediaConversion('small')
            ->extractVideoFrameAtSecond(1)
            ->fit(Manipulations::FIT_CROP, 48, 48)
            ->nonQueued();
        $this
            ->addMediaConversion('original')
            ->extractVideoFrameAtSecond(1)
            ->fit(Manipulations::FIT_CROP, 640, 640)
            ->nonQueued();
    }


    public function assignInterest($id): void
    {
        $assignments = $this->interestAssignments;
        foreach ($assignments as $assignment) {
            if ($assignment->isForInterest($id)) {
                return;
            }
        }
        $assignments[] = InterestsUserAssigment::create(['interest_id' => $id, 'user_id' => $this->id, 'type' => 'interest']);
        $this->interestAssignments = $assignments;
    }

    public function assignNotInterest($id): void
    {
        $assignments = $this->notInterestAssignments;
        foreach ($assignments as $assignment) {
            if ($assignment->isForInterest($id)) {
                return;
            }
        }
        $assignments[] = InterestsUserAssigment::create(['interest_id' => $id, 'user_id' => $this->id, 'type' => 'not-interest']);
        $this->notInterestAssignments = $assignments;
    }

    public function revokeInterest($id): void
    {
        $assignments = $this->interestAssignments;
        $assignment = InterestsUserAssigment::where(['interest_id' => $id, 'user_id' => auth()->user()->id, 'type' => 'interest'])->first();
        if ($assignment) {
            $assignment->delete();
        }
//        foreach ($assignments as $i => $assignment) {
//            if ($assignment->isForInterest($id)) {
//                unset($assignments[$i]);
//                isset($assignments[$i]) ?? $assignments[$i]->delete();
//                $this->interestAssignments = $assignments;
//                return;
//            }
//        }
//        throw new \DomainException('Interest is not found.');
    }

    public function revokeNotInterest($id): void
    {
        $assignments = $this->notInterestAssignments;
        $assignment = InterestsUserAssigment::where(['interest_id' => $id, 'user_id' => auth()->user()->id, 'type' => 'not-interest'])->first();
        if ($assignment) {
            $assignment->delete();
        }
//        foreach ($assignments as $i => $assignment) {
//            if ($assignment->isForInterest($id)) {
//                unset($assignments[$i]);
//                $this->notInterestAssignments = $assignments;
//                return;
//            }
//        }
//        throw new \DomainException('Interest is not found.');
    }

    public function revokeInterests(): void
    {
        $this->interestAssignments = [];
        $interests = InterestsUserAssigment::where(['user_id' => auth()->user()->id, 'type' => 'interest'])->get();
        foreach ($interests as $interest) {
            $interest->delete();
        }

    }

    public function revokeNotInterests(): void
    {
        $this->notInterestAssignments = [];
        $interests = InterestsUserAssigment::where(['user_id' => auth()->user()->id, 'type' => 'not-interest'])->get();
        foreach ($interests as $interest) {
            $interest->delete();
        }
    }

    public function interestAssignments()
    {
        return $this->hasMany(InterestsUserAssigment::class, 'user_id', 'id')->where(['type' => 'interest']);
    }

    public function notInterestAssignments()
    {
        return $this->hasMany(InterestsUserAssigment::class, 'user_id', 'id')->where(['type' => 'not-interest']);
    }

    public function interests()
    {
        return $this->hasManyThrough(InterestsCategory::class, InterestsUserAssigment::class, 'user_id', 'id', 'id', 'interest_id')
            ->where(['type' => 'interest']);
    }

    public function notInterests()
    {
        return $this->hasManyThrough(InterestsCategory::class, InterestsUserAssigment::class, 'user_id', 'id', 'id', 'interest_id')
            ->where(['type' => 'not-interest']);
    }

    public function tags()
    {
        return $this
            ->morphToMany(Tag::getTagClassName(), 'taggable', 'taggables', null, 'tag_id');
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'user_to_user_subscribers', 'subscribe_to_user_id', 'subscribe_from_user_id');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(User::class, 'user_to_user_subscribers', 'subscribe_from_user_id', 'subscribe_to_user_id');
    }

    public function subscribe(User $user)
    {
        if (!$this->subscriptions->contains($user->id)) {
            $this->subscriptions()->attach($user->id);
        }
    }

    public function unsubscribe(User $user)
    {
        $this->subscriptions()->detach($user->id);
    }

    public function isSubscriber($userId)
    {
        return (bool) $this->subscribers()->find($userId);
    }

    public function isSubscription($userId)
    {
        return (bool) $this->subscriptions()->find($userId);
    }

    public function address()
    {
        return $this->hasOne(UserShippingAddress::class);
    }

    public function paymentCards()
    {
        return $this->hasMany(PaymentCard::class, 'user_id', 'id');
    }

    public function paymentAccounts()
    {
        return $this->hasMany(PaymentAccount::class, 'user_id', 'id');
    }

    public function ordersForBuyer()
    {
        return $this->hasMany(Order::class, 'buyer_id', 'id');
    }

    public function ordersForSeller()
    {
        return $this->hasMany(Order::class, 'seller_id', 'id');
    }

    public function collections()
    {
        return $this->hasMany(PostCollection::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function setting()
    {
        return $this->hasOne(UserSettings::class, 'user_id', 'id');
    }

    public function requests()
    {
        return $this->hasMany(PriceRequest::class, 'user_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'model_id', 'id')->where(['model' => 'user']);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class, 'user_id', 'id');
    }

    // Users that have blocked this user
    public function blockedBy()
    {
        return $this->hasMany(Block::class, 'blocking_id' , 'id');
    }
}
