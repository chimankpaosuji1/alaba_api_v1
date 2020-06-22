<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldToSellerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            //
            $table->string('loc_of_reg')->nullable();
            $table->string('year_of_com_reg')->nullable();
            $table->longText('com_adv')->nullable();
            $table->string('main_product')->nullable();
            $table->string('factory_location')->nullable();
            $table->string('factory_size')->nullable();
            $table->string('quality_staff')->nullable();
            $table->string('prod_line')->nullable();
            $table->string('factory_address')->nullable();
            $table->string('main_market')->nullable();
            $table->longText('com_logo')->nullable();
            $table->string('com_intro')->nullable();
            $table->string('com_brochure')->nullable();
            $table->string('cert_name')->nullable();
            $table->string('cert_ref_no')->nullable();
            $table->string('cert_issued_by')->nullable();
            $table->string('cert_issued_date')->nullable();
            $table->string('cert_image')->nullable();
            $table->longText('cert_desc')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('trade_ref_no')->nullable();
            $table->string('trade_issued_by')->nullable();
            $table->string('trade_issued_date')->nullable();
            $table->string('trade_image')->nullable();
            $table->longText('trade_desc')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            //
        });
    }
}
