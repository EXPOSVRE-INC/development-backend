<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table ='songs';
    protected $fillable = [
        'id',
        'title',
        'description',
        'song_length',
        'image_file',
        'likes_count',
        'listens_count',
        'status',
        'artist_id',
        'genre_id',
        'mood_id',
        'full_song_file',
        'clip_15_sec',
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
}
