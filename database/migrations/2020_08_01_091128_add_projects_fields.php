<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects',function (Blueprint $table){
            $table->string('description')->nullable();
            $table->string('favicon')->nullable();
            $table->integer('mainpage')->nullable();
            $table->integer('header')->nullable();
            $table->integer('footer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects',function (Blueprint $table){
           $table->dropColumn([
               'description',
               'favicon',
               'mainpage',
               'header',
               'footer'
           ]);
        });
    }
}
