<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('supplier_id');
            $table->enum('route', ['Cheque', 'LPO']);
            // $table->unsignedBigInteger('trail_id');
            $table->unsignedBigInteger('requisition_status_id');
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->unsignedBigInteger('created_user_id');
            $table->timestamps();
            $table->foreign('procurement_id')->references('id')->on('procurements'); //->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers'); //->onDelete('cascade');
            // $table->foreign('trail_id')->references('id')->on('trails'); //->onDelete('cascade');
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
        Schema::dropIfExists('purchases');
    }
}
