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
        //smx_user_id
        Schema::dropIfExists(config('smartx.database.user_id_table'));
        Schema::create(config('smartx.database.user_id_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 24)->unique();
            $table->timestamps();
        });

        //smx_common_user_group
        Schema::dropIfExists(config('smartx.database.user_group_table'));
        Schema::create(config('smartx.database.user_group_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 64)->unique();
            $table->string('icon_path', 255)->default('');
            $table->boolean('allow_browse')->unsigned()->default(0);
            $table->boolean('allow_posted')->unsigned()->default(0);
            $table->boolean('allow_comment')->unsigned()->default(0);
            $table->boolean('allow_delete')->unsigned()->default(0);
            $table->timestamps();
        });

        //smx_common_user
        Schema::dropIfExists(config('smartx.database.common_user_table'));
        Schema::create(config('smartx.database.common_user_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->default(0);
            $table->tinyInteger('group_id')
                ->unsigned()
                ->default(5);
            $table->tinyInteger('level')->unsigned()->default(0);
            $table->string('username', 190)->unique();
            $table->string('phone', 190)->unique();
            $table->string('password', 60);
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('score')->default(0);
            $table->tinyInteger('can_follow')->unsigned()->default(1);
            $table->integer('sale_id')->default(0);
            $table->string('remember_token', 200)->nullable();
            $table->integer('vip_id')->default(0);
            $table->string('vip_name', 45)->default('');
            $table->string('self_introduction', 200)->default('');
            $table->string('vip_introduction', 64)->default('');
            $table->timestamps();
        });
        //smx_wx_app
        Schema::dropIfExists(config('smartx.database.wx_app_table'));
        Schema::create(config('smartx.database.wx_app_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('appid', 190)->unique();
            $table->string('name', 64)->unique();
            $table->string('secret', 190)->unique();
            $table->string('token', 60);
            $table->string('aes_key', 190)->nullable();
            $table->string('msg_notify', 200)->nullable();
            $table->integer('mch_id')->default(0)->unsigned();
            $table->text('auth_reply')->nullable();
            $table->tinyInteger('type')->unsigned()->default(0)->comment('应用类型 0未知 1小程序 2APP 3公众号');
            $table->string('remark', 200)->nullable();
            $table->tinyInteger('is_default')->unsigned()->default(0);
            $table->timestamps();
        });
        //smx_wx_app_mch
        Schema::dropIfExists(config('smartx.database.wx_app_mch_table'));
        Schema::create(config('smartx.database.wx_app_mch_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('mch_id')->nullable();
            $table->string('mch_key', 190)->nullable();
            $table->string('pay_notify', 200)->nullable();
            $table->timestamps();
        });
        //smx_wx_user
        Schema::dropIfExists(config('smartx.database.wx_user_table'));
        Schema::create(config('smartx.database.wx_user_table'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->string('openid', 200)->unique();
            $table->string('unionid', 200)->nullable();
            $table->string('nickname', 100)->default('');
            $table->string('headimgurl', 255)->dafault('');
            $table->tinyInteger('sex')->unsigned()->default(0)->comment('1男2女0未知');
            $table->text('remark')->nullable();
            $table->string('label', 200)->nullable();
            $table->tinyInteger('is_black')->default(0);
            $table->integer('country_code')->nullable();
            $table->string('city', 45)->nullable();
            $table->string('province', 45)->nullable();
            $table->string('country', 45)->nullable();
            $table->tinyInteger('subscribe')->unsigned()->default(0);
            $table->datetime('subscribe_at')->nullable();
            $table->string('scene', 64)->nullable();
            $table->string('session_key', 64)->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists(config('smartx.database.verify_code_table'));
        Schema::create(config('smartx.database.verify_code_table'), function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('phone', 45);
            $table->string('code', 32);
            $table->string('action', 45)->default('login')->comment('场景');
            $table->integer('ttl')->unsigned()->default(300)->comment('过期时间s');
            $table->boolean('usable')->default(1)->comment('是否可用 0否 1是');
            $table->boolean('strategy')->default(0)->comment('验证策略 0，生成新的后旧的可使用 1，生成新的后旧的不可使用');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('smartx.database.user_id_table'));
        Schema::dropIfExists(config('smartx.database.wx_user_table'));
        Schema::dropIfExists(config('smartx.database.wx_app_table'));
        Schema::dropIfExists(config('smartx.database.wx_app_mch_table'));
        Schema::dropIfExists(config('smartx.database.common_user_table'));
        Schema::dropIfExists(config('smartx.database.verify_code_table'));
    }
}
