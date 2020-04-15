<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_user', function (Blueprint $table) {
            // $table->bigIncrements('id');
            $table->unsignedbigInteger('group_id');
            $table->unsignedbigInteger('user_id');
            $table->string('nickname')->nullable;
            $table->primary(['group_id','user_id']);
            // $table->primary(['group_id','user_id']);//primarykeyは、このテーブル内にしかない。

            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_user');
    }
}
