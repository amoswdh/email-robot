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
        $this->info("start".date("Y-m-d H:i:s"));
        //取出邮件账号
        $emailAccounts = $this->emailAccount->all();
        foreach($emailAccounts as $emailAccount) {
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
            }catch(\Exception $e){
                file_put_contents(config('api.log_path') . "email_pull_1.log", $emailAccount->account_id . "\n" .$e->getMessage() ."\n" .$e->getTraceAsString() , FILE_APPEND);
                continue;
            }catch(Exception $e){
                file_put_contents(config('api.log_path') . "email_pull_1.log", $emailAccount->account_id . "\n" .$e->getMessage() ."\n" .$e->getTraceAsString() , FILE_APPEND);
                continue;
            }
        }
        $this->info("end".date("Y-m-d H:i:s"));
    }
}