<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('stripeId')->nullable();
            $table->string('accountNumber')->nullable();
            $table->string('nameOfBank')->nullable();
            $table->string('addressOfBank')->nullable();
            $table->string('city')->nullable();
            $table->string('routingNumber')->nullable();
            $table->string('state')->nullable();
            $table->integer('zipCode')->nullable();
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
        Schema::dropIfExists('payment_accounts');
    }
}
