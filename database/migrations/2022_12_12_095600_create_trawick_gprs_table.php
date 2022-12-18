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
        Schema::create('trawick_gprs', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('age');
            $table->integer('days');
            $table->string('destination');
            $table->string('table');
            $table->float('percent');
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
        Schema::dropIfExists('trawick_gprs');
    }
};
