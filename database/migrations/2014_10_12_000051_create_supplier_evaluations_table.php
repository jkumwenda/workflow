<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('purchase_id');
            $table->integer('score');
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('created_user_id');
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('suppliers'); //->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases'); //->onDelete('cascade');
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
        Schema::dropIfExists('supplier_evaluations');
    }
}
