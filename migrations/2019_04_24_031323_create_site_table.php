<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site', function (Blueprint $table) {
            $table->increments('site_id');
            $table->string('code');
            $table->string('site_name');
            $table->integer('group');
            $table->string('site_url');
            $table->string('site_db_server');
            $table->string('site_db_name');
            $table->string('site_db_user');
            $table->string('site_db_password');
            $table->text('comment');
            $table->tinyInteger('status');
            $table->string('welcome_msg');
            $table->string('category_default_title');
            $table->string('search_default_title');
            $table->string('logo_src');
            $table->string('logo_alt');
            $table->string('default_title');
            $table->string('default_keywords');
            $table->string('default_description');
            $table->string('send_email_address');
            $table->string('send_mail_host');
            $table->string('send_email_from_name');
            $table->string('service_email_address');
            $table->string('site_image_host');
            $table->string('site_telphone');
            $table->string('site_fax');
            $table->string('pp_client_id');
            $table->string('pp_secret');
            $table->string('pp_webhook_url');
            $table->tinyInteger('pp_dispute_status');
            $table->string('pp_merchant_id');
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
        Schema::drop('site');
    }
}
