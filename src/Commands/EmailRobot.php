<?php
namespace Amos\MailRobot\Commands;

use Amos\MailRobot\Jobs\ImapDownload;
use Amos\MailRobot\Services\EmailAccountService;
use Illuminate\Console\Command;
use PhpImap\Exception;


class EmailRobot extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'email:robot';

    /**
     * @var EmailAccountService|null
     */
    private $emailAccount = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email auto pull script.';

    /**
     * EmailRobot constructor.
     * @param EmailAccountService $emailAccount
     */
    public function __construct( EmailAccountService $emailAccount )
    {
        $this->emailAccount = $emailAccount;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //取出邮件账号
        $emailAccounts = $this->emailAccount->all();

        foreach($emailAccounts as $emailAccount) {

            if(in_array($emailAccount->account_id,[363,362,361,360,359,358,357])){
                continue;
            }

            try{
                //创建邮件附件存储目录
                if(!file_exists($emailAccount->attachmentsDir())){mkdir($emailAccount->attachmentsDir(), 0777, true);}
                chmod($emailAccount->attachmentsDir(), 0777);

                $mailbox = $emailAccount->mailbox();

                $criteria = 'ALL';

                $mailIds = $mailbox->searchMailBox($criteria);

                foreach($mailIds as $mailId) {
                    dispatch((new ImapDownload($emailAccount, $mailbox , $mailId ))->onQueue("imap"));
                }
                exit;
            }catch(\Exception $e){
                $this->info($e->getMessage());
            }catch(Exception $e){
                $this->info($e->getMessage());
                continue;
            }
        }
    }
}