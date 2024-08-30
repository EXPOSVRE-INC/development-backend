<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToPostsCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts_collection', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->boolean('allowToComment')->default(false);
            $table->boolean('allowToCrown')->default(false);
            $table->integer('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts_collection', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('allowToComment');
            $table->dropColumn('allowToCrown');
            $table->dropColumn('user_id');
        });
    }
}
