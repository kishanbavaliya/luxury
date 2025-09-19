<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCarMakesCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('car_makes', function (Blueprint $table) {
            $table->integer('no_of_people')->nullable();
            $table->integer('no_of_bags')->nullable();
            $table->integer('no_of_doors')->nullable();
            $table->string('transmission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
