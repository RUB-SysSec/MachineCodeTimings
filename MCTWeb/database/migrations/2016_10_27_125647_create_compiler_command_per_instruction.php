<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompilerCommandPerInstruction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intrinsics', function (Blueprint $table) {
            //
            $table->string('compiler_command');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intrinsics', function (Blueprint $table) {
            //
            $table->dropColumn('compiler_command');
        });
    }
}
