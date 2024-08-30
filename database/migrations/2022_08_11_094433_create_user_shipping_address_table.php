<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserShippingAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shipping_address', function (Blueprint $table) {
            $table->id();
            $table->string('country');
            $table->string('state')->nullable();
            $table->string('city');
            $table->string('zip');
            $table->string('address');
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();
            $table->integer('user_id');
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
        Schema::dropIfExists('user_shipping_address');
    }
}
