<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('buyer_id');
            $table->string('sale_no');
            $table->string('status')->default('Pending');
            $table->string('buyer_status')->default('Pending');
            $table->string('seller_status')->default('Pending');
            $table->integer('total_weight');
            $table->integer('total_qty');
            $table->decimal('total_amount_paid',10,2);
            $table->decimal('total_amount',10,2);
            $table->decimal('total_shipping_amount',10,2);
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();
            $table->longText('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_email')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->longText('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_country')->nullable();
            $table->timestamps();
        });

        Schema::create('buyer_product', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('buyer_id');
        });

        Schema::create('buyer_sale', function (Blueprint $table) {
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('sale_id');
        });

        Schema::create('sale_seller', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('sale_id');

        });

        Schema::create('product_sale', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('sale_id');

        });

        Schema::create('order_sale', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('sale_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buyer_product');
        Schema::dropIfExists('sale_seller');
        Schema::dropIfExists('product_sale');
        Schema::dropIfExists('order_sale');
        Schema::dropIfExists('sales');
    }
}
