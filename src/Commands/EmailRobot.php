<?php
namespace Amos\MailRobot\Commands;

use Amos\MailRobot\Models\EmailAccount;
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
    protected $description = 'Email auto pull script.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        echo 123;
        $emailAccount = EmailAccount::where("status","1")->orderBy('account_id', 'desc');

    }



}