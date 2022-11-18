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
        Schema::create('img_medical_daily_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('img_product_id');
            $table->integer('age_min');
            $table->integer('age_max');
            $table->integer('deductible');
            $table->integer('policy_max');
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
        Schema::dropIfExists('img_medical_daily_rates');
    }
};
