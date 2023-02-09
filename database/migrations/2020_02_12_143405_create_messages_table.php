<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('messageable_id')->index();
            $table->string('messageable_type');
            $table->unsignedBigInteger('questioner_user_id');
            $table->unsignedBigInteger('receiver_user_id');
            $table->unsignedBigInteger('answerer_user_id')->nullable();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamps();
            $table->foreign('questioner_user_id')->references('id')->on('users'); //->onDelete('cascade');
            $table->foreign('receiver_user_id')->references('id')->on('users'); //->onDelete('cascade');
            $table->foreign('answerer_user_id')->references('id')->on('users'); //->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
