<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('travellers', function (Blueprint $table) {
            $table->unsignedBigInteger("subsistence_id")->nullable()->after('amount');
            $table->foreign('subsistence_id')->references('id')->on('subsistences')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travellers', function (Blueprint $table) {
            $table->dropForeign(['subsistence_id']);
            $table->dropColumn('subsistence_id');
        });
    }
};
