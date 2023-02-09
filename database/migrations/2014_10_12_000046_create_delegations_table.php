<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDelegationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delegations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delegationable_id')->index();
            $table->string('delegationable_type');
            $table->enum('status', ['Pending', 'Checked']);
            $table->unsignedBigInteger('sender_user_id');
            $table->unsignedBigInteger('receiver_user_id');
            $table->timestamp('checked_at')->nullable();
            $table->unsignedBigInteger('requisition_status_id')->nullable();
            $table->text('sender_comment')->nullable();
            $table->text('receiver_comment')->nullable();
            $table->timestamps();
            $table->foreign('sender_user_id')->references('id')->on('users'); //->onDelete('cascade');
            $table->foreign('receiver_user_id')->references('id')->on('users'); //->onDelete('cascade');
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
        Schema::dropIfExists('delegations');
    }
}
