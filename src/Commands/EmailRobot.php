<?php
namespace Amos\MailRobot\Commands;

use Amos\MailRobot\Services\EmailAccountService;
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
        //限制进程内存使用
        ini_set('memory_limit','256M');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try{
            //取出邮件账号
            $emailAccounts = $this->emailAccount->all();

            //配置删除邮件黑名单 - 集合中设置的账号ID不做删除操作
            //优化 - 数据库新增字段控制
            $email_account_ids_notallow_delete = array(16,55);

            //邮件移动临时目录
            $move_to = "TempHistory";

            foreach($emailAccounts as $emailAccount){

                //站点信息
                $site = $emailAccount->site;

                //创建邮件附件存储目录
                if(!file_exists($emailAccount->attachmentsDir())){mkdir($emailAccount->attachmentsDir(), 0777, true);}
                chmod($emailAccount->attachmentsDir(), 0777);

var_dump($site->site_id);exit;
                $site = M('site')->where(array('site_id'=> $emailAccount->site_id))->find();
            }
        }catch(\Exception $e){
            $this->info($e->getLine());
        }
    }
}