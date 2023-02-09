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
        Schema::create('subsistences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('travel_id');
            $table->unsignedBigInteger('requisition_status_id');
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->unsignedBigInteger('created_user_id');
            $table->timestamps();

            //foreign keys
            $table->foreign('travel_id')->references('id')->on('travels')->onDelete('cascade');
            $table->foreign('requisition_status_id')->references('id')->on('requisition_statuses'); //->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users'); //->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users'); //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subsistences');
    }
};
