<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTicketNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_ticket_new', function (Blueprint $table) {
            $table->increments('ticket_id');
            $table->string('ticket_sn');
            $table->integer('account_id');
            $table->integer('email_id');
            $table->string('p_message_id');
            $table->string('distribution_email');
            $table->integer('email_cs_admin_id');
            $table->enum('sys_flag', ["closed","resolved","in_progress","opened"]);
            $table->enum('sys_priority', ["normal","high","urgent"]);
            $table->string('order_sn');
            $table->integer('add_time');
            $table->integer('update_time');
            $table->integer('last_reply_time');
            $table->tinyInteger('overall_satisfaction');
            $table->tinyInteger('written_communications');
            $table->tinyInteger('written_explanations');
            $table->tinyInteger('response_speeds');
            $table->text('customer_comments');
            $table->text('note');
            $table->tinyInteger('trash');
            $table->tinyInteger('is_in_progress_reminded');
            $table->tinyInteger('is_closed_reminded');
            $table->char('is_paypal_close', 4);
            $table->tinyInteger('is_merged');
            $table->tinyInteger('is_track');
            $table->integer('track_time');
            $table->string('modified_reply_to_email');
            $table->tinyInteger('is_system');
            $table->string('comments_coupon');
            $table->tinyInteger('is_tel');
            $table->tinyInteger('is_fr');
            $table->tinyInteger('tag_ok');
            $table->integer('tag_time');
            $table->integer('tag_admin');
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
        Schema::drop('email_ticket_new');
    }
}
