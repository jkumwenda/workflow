<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flow_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('flow_id');
            $table->integer('level');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('requisition_status_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('flow_id')->references('id')->on('flows'); //->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles'); //->onDelete('cascade');
            $table->foreign('requisition_status_id')->references('id')->on('requisition_statuses'); //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flow_details');
    }
}
