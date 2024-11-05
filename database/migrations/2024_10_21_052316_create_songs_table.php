<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('image_file');
            $table->integer('likes_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->string('status')->default('active');
            $table->integer('artist_id');
            $table->integer('genre_id');
            $table->integer('mood_id');
            $table->string('full_song_file');
            $table->string('clip_15_sec');
            $table->time('song_length');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('songs');
    }
}
