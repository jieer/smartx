<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMchTables extends Migration
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
        //smx_wx_app
        Schema::table(config('smartx.database.wx_app_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mch_id')->default(0)->unsigned()->after('type');
            $table->timestamps();
        });
        //smx_wx_app_mch
        Schema::dropIfExists(config('smartx.database.wx_app_mch_table'));
        Schema::create(config('smartx.database.wx_app_mch_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('mch_key', 190)->nullable();
            $table->string('mch_id')->nullable();
            $table->string('pay_notify', 200)->nullable();
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
    }
}
