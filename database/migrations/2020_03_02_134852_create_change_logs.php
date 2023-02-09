<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('changeable_id')->index();
            $table->string('changeable_type');
            $table->enum('crud', ['Create', 'Update', 'Delete']);
            $table->json('information');
            $table->json('changes')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
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
        Schema::dropIfExists('change_logs');
    }
}
