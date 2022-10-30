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
        Schema::create('trawick_tripcost_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trawick_product_id');
            $table->integer('cost_min');
            $table->integer('cost_max');
            $table->integer('age_min');
            $table->integer('age_max');
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
        Schema::dropIfExists('trawick_tripcost_rates');
    }
};
