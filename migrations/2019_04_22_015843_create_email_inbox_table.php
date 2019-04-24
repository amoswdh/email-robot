<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailInboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_inbox', function (Blueprint $table) {
            $table->increments('email_id');
            $table->string('email_sn');
            $table->integer('account_id');
            $table->integer('service_mail_id');
            $table->string('message_id');
            $table->string('p_message_id');
            $table->string('references_str',1500);
            $table->string('in_reply_to');
            $table->integer('date');
            $table->text('subject'); //避免超长文本存储丢失
            $table->text('from_name');
            $table->string('from_email');
            $table->text('receivers');
            $table->text('cc');
            $table->text('bcc');
            $table->string('reply_to_email');
            $table->text('reply_to_name');
            $table->text('attachments');
            $table->string('ip');
            $table->integer('country_id');
            $table->tinyInteger('is_virtual');
            $table->integer('create_time');
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
        Schema::drop('email_inbox');
    }
}
