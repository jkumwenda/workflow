<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCancelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cancelable_id')->index();
            $table->string('cancelable_type');
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('created_user_id');
            $table->timestamps();
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
        Schema::dropIfExists('cancels');
    }
}
