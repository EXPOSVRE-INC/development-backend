<?php

namespace App\Models;

use Overtrue\LaravelLike\Traits\Likeable;
use Orkhanahmadov\LaravelCommentable\Commentable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory , Likeable, Favoriteable , Commentable;

    protected $table ='songs';
    protected $fillable = [
        'id',
        'title',
        'description',
        'song_length',
        'image_file',
        'likes_count',
        'views_count',
        'download_count',
        'status',
        'artist_id',
        'genre_id',
        'user_id',
        'mood_id',
        'full_song_file',
        'clip_15_sec',
        'viewsByLastDay'
      ];

      public function artist()
      {
          return $this->belongsTo(Artist::class);
      }

      public function genre()
      {
          return $this->belongsTo(Genre::class);
      }

      public function mood()
      {
          return $this->belongsTo(Mood::class);
      }
      public function posts()
      {
        return $this->hasMany(Post::class, 'song_id');
      }
     public function user()
        {
            return $this->hasOne(User::class, 'id', 'user_id');
        }

}
