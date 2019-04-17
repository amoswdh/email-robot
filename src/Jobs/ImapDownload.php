<?php

namespace Amos\MailRobot\Jobs;

use Amos\MailRobot\Models\EmailAccount;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpImap\Mailbox;

class ImapDownload extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $emailAccount;
    private $mailbox;
    private $mailId;

    /**
     * ImapDownload constructor.
     * @param EmailAccount $emailAccount
     * @param Mailbox $mailbox
     * @param $mailId
     */
    public function __construct(EmailAccount $emailAccount, Mailbox $mailbox, $mailId)
    {
        //限制进程内存使用
        ini_set('memory_limit', '256M');
        //
        $this->emailAccount = $emailAccount;
        $this->mailbox = $mailbox;
        $this->mailId = $mailId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->emailAccount->createTicket($this->mailbox, $this->mailId);
        } catch (\Exception $e) {
            file_put_contents(config('api.log_path') . "email_pull_2.log", date("Y-m-d H:i:s"). "\n" .$this->emailAccount->account_id ."-".$this->mailId . "\n" .$e->getMessage() ."\n" .$e->getTraceAsString()."\n" , FILE_APPEND);
        } catch(\PDOException $e){
            file_put_contents(config('api.log_path') . "email_pull_2.log", date("Y-m-d H:i:s"). "\n" .$this->emailAccount->account_id ."-".$this->mailId . "\n" .$e->getMessage() ."\n" .$e->getTraceAsString()."\n" , FILE_APPEND);
        }
    }
}
