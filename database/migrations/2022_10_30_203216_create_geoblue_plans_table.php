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
        Schema::create('geoblue_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geoblue_product_id');
            $table->string('name');
            $table->string('product_type_id');
            $table->string('plan_id');
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
        Schema::dropIfExists('geoblue_plans');
    }
};
