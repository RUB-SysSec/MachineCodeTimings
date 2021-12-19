<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompilationEntriesComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compilation_entries', function (Blueprint $table) {
            $table->string('comment');
            $table->integer('intrinsic_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compilation_entries', function (Blueprint $table) {
            $table->dropColumn('intrinsic_id');
            $table->dropColumn('comment');
        });
    }
}
