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
        Schema::create('travels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('flow_id');
            $table->unsignedBigInteger('requisition_status_id');
            $table->integer('level')->nullable();
            $table->enum('travel_type', ['book', 'hire'])->nullable();
            $table->string('purpose');
            $table->integer('vehicle_type_id')->nullable();
            $table->dateTime('datetime_out')->nullable();
            $table->dateTime('datetime_in')->nullable();
            $table->unsignedBigInteger('origin');
            $table->unsignedBigInteger('destination');
            $table->tinyInteger('personal_car_use')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger("created_user_id");
            $table->unsignedBigInteger("current_user_id")->nullable();
            $table->unsignedBigInteger("driver_id")->nullable();
            $table->unsignedBigInteger("vehicle_id")->nullable();
            $table->integer("mileage_out")->nullable();
            $table->integer("mileage_in")->nullable();

            //Foreign keys
            $table->foreign('procurement_id')->references('id')->on('procurements')->onDelete('cascade');
            $table->foreign('requisition_status_id')->references('id')->on('requisition_statuses');//->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users'); //->onDelete('cascade');
            $table->foreign('current_user_id')->references('id')->on('users');// ->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users');//->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');//->onDelete('cascade');
            $table->foreign('origin')->references('id')->on('campuses');//->onDelete('cascade');
            $table->foreign('destination')->references('id')->on('districts');//->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travels', function (Blueprint $table) {
            $table->dropForeign(['procurement_id']);
            $table->dropForeign(['requisition_status_id']);
            $table->dropForeign(['created_user_id',]);
            $table->dropForeign(['current_user_id']);
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['origin']);
            $table->dropForeign(['destination']);
        });

        Schema::dropIfExists('travels');
    }
};
