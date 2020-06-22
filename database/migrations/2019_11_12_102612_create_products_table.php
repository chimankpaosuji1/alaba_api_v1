<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_id');
            $table->string('product_slug');
            $table->string('product_name');
            $table->string('keyword')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('option')->nullable();
            $table->integer('moq')->nullable();
            $table->integer('status')->default(0)->nullable();
            $table->longText('product_details')->nullable();
            $table->longText('product_description')->nullable();
            $table->longText('package_content')->nullable();
            $table->longText('product_highlight')->nullable();
            $table->string('product_manual')->nullable();
            $table->string('youtubeid')->nullable();
            $table->string('measurement')->nullable();
            $table->string('dimension')->nullable();
            $table->string('product_warranty')->nullable();
            $table->string('warranty_type')->nullable();
            $table->longText('service_center_details')->nullable();
            $table->string('madein')->nullable();
            $table->string('selling_unit')->nullable();
            $table->string('selling_package_size')->nullable();
            $table->string('single_gross_weight')->nullable();
            $table->longText('package_type')->nullable();
            $table->string('package_quantity')->nullable();
            $table->unsignedBigInteger('seller_id');
            $table->string('main_image');
            $table->string('est_days')->nullable();
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('products');
    }
}
