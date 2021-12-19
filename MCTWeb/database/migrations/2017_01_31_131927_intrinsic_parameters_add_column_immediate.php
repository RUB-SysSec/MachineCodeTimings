<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\ParameterTypeInit;

class IntrinsicParametersAddColumnImmediate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intrinsic_parameters', function (Blueprint $table) {
            $table->boolean('immediate')->default('0')->after('name');
        });
        
        $inits = ParameterTypeInit::all();
        foreach($inits as $init){
            $init->code = rtrim($init->code, ";");
            $init->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intrinsic_parameters', function (Blueprint $table) {
                $table->dropColumn('immediate');
        });
        
        $inits = ParameterTypeInit::all();
        foreach($inits as $init){
            $init->code .= ";";
            $init->save();
        }
    }
}
