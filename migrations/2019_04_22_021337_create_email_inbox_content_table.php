<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailInboxContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_inbox_content', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('email_id');
            $table->string('message_id');
            $table->text('text_plain');
            $table->text('text_html');
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
        Schema::drop('email_inbox_content');
    }
}
