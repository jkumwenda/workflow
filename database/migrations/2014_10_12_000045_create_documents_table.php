<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('documentable_id')->index();
            $table->string('documentable_type');
            $table->enum('document_type', ['Quotation', 'Invoice', 'Misc']);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_extension');
            $table->enum('checked', [0, 1]);    //tbl_document -> 0 tbl_quotation -> 1
            $table->unsignedBigInteger('created_user_id')->nullable();
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
        Schema::dropIfExists('documents');
    }
}
