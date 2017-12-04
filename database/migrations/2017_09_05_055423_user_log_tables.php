<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UserLogTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up() {
        // Create table for storing roles
        Schema::create('userlog', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('token');
            $table->longText('description');
            $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down() {
        Schema::dropIfExists('userlog');
    }

}
