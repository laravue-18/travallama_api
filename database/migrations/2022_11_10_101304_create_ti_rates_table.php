<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ti_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ti_product_id');
            $table->integer('age_min');
            $table->integer('age_max');
            $table->integer('trip_cost_min');
            $table->integer('trip_cost_max');
            $table->float('rate');
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
        Schema::dropIfExists('ti_rates');
    }
};
