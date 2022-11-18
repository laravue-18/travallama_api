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
        Schema::create('geoblue_voyager_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geoblue_product_id');
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
        Schema::dropIfExists('geoblue_voyager_rates');
    }
};
