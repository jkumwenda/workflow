<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // *** Integrate with user_unit table ***

        // Schema::create('unit_user', function (Blueprint $table) {
        //     $table->unsignedBigInteger('user_id');
        //     $table->unsignedBigInteger('unit_id');
        //     $table->timestamps();
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('unit_user');
    }
}
