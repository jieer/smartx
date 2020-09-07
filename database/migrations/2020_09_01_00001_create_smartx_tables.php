<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmartXTables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return config('smartx.database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists(config('smartx.database.common_user_table'));
        Schema::create(config('smartx.database.common_user_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 190)->unique();
            $table->string('phone', 190)->unique();
            $table->string('password', 60);
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->string('remember_token', 200)->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists(config('smartx.database.wx_app_table'));
        Schema::create(config('smartx.database.wx_app_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('appid', 190)->unique();
            $table->string('name', 64)->unique();
            $table->string('secret', 190)->unique();
            $table->string('token', 60);
            $table->string('aes_key', 190)->nullable();
            $table->string('mch_id')->nullable();
            $table->string('notify', 200)->nullable();
            $table->tinyInteger('type')->unsigned()->default(0)->comment('应用类型 0未知 1小程序 2APP 3公众号');
            $table->string('remark', 200)->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists(config('smartx.database.wx_user_table'));
        Schema::create(config('smartx.database.wx_user_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id');
            $table->integer('user_id');
            $table->string('openid', 200)->unique();
            $table->string('unionid', 200)->nullable();
            $table->string('nickname', 100);
            $table->string('headimgurl', 255)->dafault('');
            $table->tinyInteger('sex')->unsigned()->default(0)->comment('1男2女0未知');
            $table->text('remark')->nullable();
            $table->string('label', 200)->nullable();
            $table->tinyInteger('is_black')->default(0);
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
        Schema::dropIfExists(config('smartx.database.wx_user_table'));
        Schema::dropIfExists(config('smartx.database.wx_app_table'));
        Schema::dropIfExists(config('smartx.database.common_user_table'));
    }
}
