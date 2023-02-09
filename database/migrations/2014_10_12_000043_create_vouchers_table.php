<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_id')->index();
            $table->string('expenditure_code')->nullable();
            $table->double('excepted_tax')->nullable();
            $table->string('withholding_tax_code')->nullable();
            $table->unsignedInteger('tax_applied')->nullable();
            $table->double('total_amount')->nullable();
            // $table->unsignedBigInteger('trail_id');
            $table->unsignedBigInteger('requisition_status_id');
            $table->unsignedBigInteger('assigned_accountant_user_id')->nullable();
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->unsignedBigInteger('created_user_id')->nullable();
            $table->timestamps();
            // $table->foreign('trail_id')->references('id')->on('trails'); //->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases'); //->onDelete('cascade');
            $table->foreign('requisition_status_id')->references('id')->on('requisition_statuses'); //->onDelete('cascade');
            $table->foreign('assigned_accountant_user_id')->references('id')->on('users'); //->onDelete('cascade');
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
        Schema::dropIfExists('vouchers');
    }
}
