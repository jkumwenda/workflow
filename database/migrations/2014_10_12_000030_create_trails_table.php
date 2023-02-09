<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('trailable_id')->index();
            $table->string('trailable_type');
            $table->unsignedBigInteger('flow_id');
            $table->unsignedBigInteger('flow_detail_id')->nullable();
            // $table->integer('level');
            // $table->unsignedBigInteger('requisition_status_id');
            $table->unsignedBigInteger('user_id')->nullable(); //本当はnullableにしたくない→autoがあるため必要
            $table->enum('status', ['CHECKING', 'DELEGATING', 'RETURNED', 'NORMAL', 'TRANSFERRED', 'STALLED']);
            $table->text('comment')->nullable();
            $table->timestamp('transaction_at')->nullable();
            $table->timestamps();
            $table->foreign('flow_id')->references('id')->on('flows'); //->onDelete('cascade');
            $table->foreign('flow_detail_id')->references('id')->on('flow_details'); //->onDelete('cascade');
            // $table->foreign('requisition_status_id')->references('id')->on('requisition_statuses'); //->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users'); //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trails');
    }
}
