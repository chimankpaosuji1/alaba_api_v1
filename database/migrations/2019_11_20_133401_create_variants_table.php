<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('material')->nullable();
            $table->string('variant_sku')->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('to_purchase')->default(0);
            $table->timestamps();
        });
        Schema::create('product_variant', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variants');
    }
}
