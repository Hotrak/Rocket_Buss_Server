<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTownConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('town_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('town1_id');
            $table->integer('town2_id');
            $table->integer('town_x');
            $table->integer('town_y');
            $table->integer('conn_group');
            $table->integer('index_pos');
            $table->integer('time_drive')->nullable();
            $table->double('price',8,2)->nullable();
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
        Schema::dropIfExists('town_connections');
    }
}
