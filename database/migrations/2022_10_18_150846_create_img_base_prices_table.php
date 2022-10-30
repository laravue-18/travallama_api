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
        Schema::create('img_base_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('img_product_id');
            $table->integer('trip_cost_min');
            $table->integer('trip_cost_max');
            $table->float('price');
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
        Schema::dropIfExists('img_base_prices');
    }
};