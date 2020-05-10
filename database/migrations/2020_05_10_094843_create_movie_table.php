<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movie', function (Blueprint $table) {
            $table->increments('id')->primary();
            $table->string('name',20);
            $table->string('janre',80);
            $table->string('language',20);
            $table->string('product',40);
            $table->float('rate',2);
            $table->string('director',20);
            $table->string('actors',100);
            $table->string('summary',10000);
            $table->string('trailler',250);
            $table->string('image',250);
            $table->integer('p_year',4);
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
        Schema::dropIfExists('movie');
    }
}
