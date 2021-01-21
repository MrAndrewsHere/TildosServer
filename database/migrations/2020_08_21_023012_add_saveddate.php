<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaveddate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages',function (Blueprint $table){
            $table->longText('saveddate')->default('[]');
            $table->boolean('changed')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages',function (Blueprint $table){
            $table->removeColumn('saveddate');
            $table->removeColumn('changed');
        });
    }
}
