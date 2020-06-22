<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIslockedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('islockeds', function (Blueprint $table) {
            $table->Increments('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
            $table->string('reason');
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('islockeds');
    }
}
