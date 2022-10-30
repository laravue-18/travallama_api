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
        Schema::create('img_products', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('code');
            $table->string('app_type');
            $table->json('states')->nullable();
            $table->boolean('states_flag')->nullable();
            $table->json('riders')->nullable();
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
        Schema::dropIfExists('img_products');
    }
};