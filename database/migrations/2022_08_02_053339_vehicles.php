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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number');
            $table->integer('mileage');
            $table->integer('capacity');
            $table->string('colour');
            $table->foreignID('vehicle_type_id')->constrained('vehicle_types')->onDelete('cascade');
            $table->foreignID('make_id')->constrained('makes')->onDelete('cascade');
            $table->foreignID('campus_id')->constrained('campuses')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
