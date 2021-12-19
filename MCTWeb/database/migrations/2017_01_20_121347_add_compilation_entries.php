<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompilationEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compilation_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('compilation_id')->unsigned();
            $table->foreign('compilation_id')->references('id')->on('compilations')->onDelete('cascade');
            $table->string('job_id');
            $table->string('node_id');
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
        Schema::dropIfExists('compilation_entries');
    }
}
