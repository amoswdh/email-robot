<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_account', function (Blueprint $table) {
            $table->increments('account_id');//自增ID
            $table->string('nickname'); //昵称
            $table->string('username'); //邮件账号主机
            $table->string('password'); //登陆密码
            $table->string('in_remote_system_name');
            $table->integer('in_port');
            $table->string('in_flags',32);
            $table->tinyInteger('in_is_ssl');
            $table->tinyInteger('in_is_nocert');
            $table->string('out_remote_system_name');
            $table->tinyInteger('out_port');
            $table->tinyInteger('out_is_smtpauth');
            $table->tinyInteger('out_is_ssl');
            $table->integer('email_count');
            $table->integer('check_time');
            $table->integer('status');
            $table->integer('site_id');
            $table->integer('add_time');
            $table->string('email_qiye');
            $table->tinyInteger('department_status');
            $table->enum('type', ['synchronous', 'asynchronous']);
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
        Schema::dropIfExists('email_account');
    }
}
