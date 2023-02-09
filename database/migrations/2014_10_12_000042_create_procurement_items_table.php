<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcurementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('procurement_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->enum('uom', ['EACH', 'PACK', 'BALE', 'CARTON', 'CASE', 'PALLET', 'REAM', 'BOTTLE', 'TUBE', 'VIALS', 'AMPULES', 'METERS', 'LITERS', 'BOX']);
            $table->text('description');
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->double('amount')->nullable();
            $table->enum('received', [0, 1]);
            $table->timestamps();
            $table->foreign('procurement_id')->references('id')->on('procurements'); //->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items'); //->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases'); //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('procurement_items');
    }
}
