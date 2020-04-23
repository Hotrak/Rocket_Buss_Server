<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleCreateInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_create_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('schedule_date');
            $table->string('schedule_drivers');
            $table->string('schedule_holidays_drivers');
            $table->string('schedule_cars')->nullable();
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
        Schema::dropIfExists('schedule_create_infos');
    }
}
