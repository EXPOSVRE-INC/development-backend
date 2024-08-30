<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('collection_post');
            $table->boolean('post_for_sale');
            $table->integer('collection_id');
            $table->boolean('unlimited_edition');
            $table->integer('limited_addition_number');
            $table->boolean('physical_item');
            $table->datetime('time_sale_from_date')->nullable();
            $table->datetime('time_sale_to_date')->nullable();
            $table->integer('fixed_price');
            $table->integer('royalties_percentage');
            $table->boolean('allow_to_comment');
            $table->boolean('allow_views');
            $table->boolean('exclusive_content');
            $table->integer('owner_id');
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
        Schema::dropIfExists('posts');
    }
}
