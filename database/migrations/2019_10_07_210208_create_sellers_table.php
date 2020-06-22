<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('business_name', 350);
            $table->integer('is_basic')->default(0);
            $table->integer('is_premium')->default(0);
            $table->integer('trade_assurance')->default(0);
            $table->string('business_type', 350);
            $table->string('product_category', 350);
            $table->string('address', 350);
            $table->string('country', 350);
            $table->string('city', 350);
            $table->string('business_reg_no', 350)->nullable();
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
        Schema::dropIfExists('sellers');
    }
}
