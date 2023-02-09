<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('travellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('travel_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger("created_user_id");
            $table->timestamps();
            $table->unsignedBigInteger("current_user_id");
            $table->double('amount')->nullable();
            
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->dateTime('departure_date')->nullable();
            $table->dateTime('return_date')->nullable();
            $table->enum('accomodation_provided',['Yes','No']);

            //foreign keys
            
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users');//->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users');//->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('vouchers');//->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travellers', function (Blueprint $table) {
            $table->dropForeign(['travel_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['created_user_id',]);
            $table->dropForeign(['current_user_id']);
            $table->dropForeign(['voucher_id']);
        });
        
        Schema::dropIfExists('travellers');
    }
};
