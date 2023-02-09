<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_id');
            $table->string('po_number');
            // $table->unsignedBigInteger('trail_id');
            $table->unsignedBigInteger('requisition_status_id');
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
            // $table->foreign('trail_id')->references('id')->on('trails'); //->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases'); //->onDelete('cascade');
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
        Schema::dropIfExists('orders');
    }
}
