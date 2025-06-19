<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationPreferencesToUserSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('notify_phone_verification')->default(true);
            $table->boolean('notify_new_message')->default(true);
            $table->boolean('notify_new_comment')->default(true);
            $table->boolean('notify_new_crowned_post')->default(true);
            $table->boolean('notify_new_follow')->default(true);
            $table->boolean('notify_new_sale')->default(true);
            $table->boolean('notify_price_request')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            //
        });
    }
}
