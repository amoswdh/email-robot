<?php
namespace Amos\MailRobot\Commands;

use Illuminate\Console\Command;


class EmailRobot extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'email:robot';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '邮件自动拉取服务.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        echo 123;
    }



}