<?php

namespace Amos\MailRobot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpImap\Mailbox;

class EmailAccount extends Model
{
    //
    protected $table = "email_account";

    public $primaryKey = 'account_id';

    //禁止删除邮件的账号
    public static $notallow_delete = [
        16,
        55
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function site()
    {
        return $this->hasOne('Amos\MailRobot\Models\Site', "site_id", "site_id");
    }

    /**
     * @return string
     */
    public function imapPath()
    {
        $ssl = $this->in_is_ssl == 1 ? '/ssl' : '';
        $cert = $this->in_is_nocert == 1 ? '/novalidate-cert' : '';
        return '{' . $this->in_remote_system_name . ':' . $this->in_port . '/' . $this->in_flags . $ssl . $cert . '}';
    }

    /**
     * @return mixed
     */
    public function login()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function attachmentsDir()
    {
        return '/storage/Public/Email/attachment/account_' . $this->account_id . '/in';
    }

    /**
     * @return string
     */
    public function baseUrl()
    {
        return "http://" . $this->username . '/Public/Email/attachment/account_' . $this->account_id . '/in';
    }

    /**
     * @return Mailbox
     */
    public function mailbox()
    {
        return new Mailbox($this->imapPath(), $this->login(), $this->password(), $this->attachmentsDir(), "utf-8");
    }

    /**
     * 检查邮件
     * @param Mailbox $mailbox
     * @param $mail
     * @param $mailId
     * @param $p_message_id
     * @return bool
     */
    public function checkMail(Mailbox $mailbox, $mail, $mailId, $matches)
    {
        $result = true;
        //已存在邮件信息
        if(!empty($matches) && isset($matches[1])){
            $emailTicket = EmailTicketNew::where("p_message_id", $matches[1])->first();
            if (is_object($emailTicket)) {
                $result = false;
                $this->deleteMail($mailbox, $mailId);
            }
        }
        //邮件标题、内容为空
        if (empty($mail->subject) AND empty($mail->textPlain) AND empty($mail->textHtml)) {
            $result = false;
            $this->deleteMail($mailbox, $mailId);
        } elseif (empty($mail->textPlain) AND empty($mail->textHtml)) {
            $result = false;
            $this->deleteMail($mailbox, $mailId);
        }
        //校验主题为付款通知邮件，不理会跳出
        if (stripos($mail->subject, 'payment received') !== false) {
            $result = false;
            $this->deleteMail($mailbox, $mailId);
        }
        //校验发信地址 存在该发信地址邮件删除
        if ($mail->fromName == 'noreply@lululike.com.net' or
            $mail->fromAddress == 'noreply@lululike.com.net'
        ) {
            $result = false;
            $this->deleteMail($mailbox, $mailId);
        }
        return $result;
    }


    /**
     * 邮件入库
     * 1入库收信箱 2 生成新Ticket 3 分配客服 4 插入分配记录
     * @param Mailbox $mailbox
     * @param $mailId
     */
    public function createTicket(Mailbox $mailbox, $mailId)
    {
        //邮件头
        $mailInfo = $mailbox->getMailsInfo(array($mailId));
        $mailInfo = $mailInfo[0];
        //邮件详情
        $mail = $mailbox->getMail($mailId);

        EmailLog::create(
            [
                "header" => serialize($mailInfo),
                "body" => serialize($mail),
                "mail_id" => $mailId,
                "account_id" => $this->account_id
            ]
        );

        //匹配 Message Id
        preg_match('/<(.*)>/', $mail->messageId, $matches);

        //校验邮件有效性
        if (!$this->checkMail($mailbox, $mail, $mailId, $matches)) exit;

        //邮件标题中提取订单号信息
        $subject = $mail->subject;
        $tmp_order_sn = [];
        if (stripos($subject, '@') !== false) {
            $tmp_order_sn = explode('@', $subject);
            $subject = $tmp_order_sn[0];
        }

        //订单号
        $order_sn_ticket = isset($tmp_order_sn[1]) && !empty($tmp_order_sn[1]) ? $tmp_order_sn[1] : 0;

        $data = [];
        $data['message_id'] = empty($matches[1]) ? self::createMessageID($subject, strtotime($mailInfo->date)) : $matches[1];
        $data['email_sn'] = self::createEmailSN($data['message_id'], $subject, strtotime($mailInfo->date));
        $data['subject'] = $subject;
        $data['account_id'] = $this->account_id;
        $data['service_mail_id'] = isset($mail->id) && !empty($mail->id) ? $mail->id : $mailId;
        $data['date'] = strtotime($mail->date);
        $data['from_name'] = $mail->fromName;
        $data['from_email'] = $mail->fromAddress;

        // To
        foreach ($mail->to as $to_k => $to_v) {
            $data['receivers'][] = ["email" => $to_k, "name" => $to_v];
        }
        $data['receivers'] = isset($data['receivers']) ? serialize($data['receivers']) : serialize([]);

        // CC
        foreach ($mail->cc as $cc_k => $cc_v) {
            $data['cc'][] = ["email" => $cc_k, "name" => $cc_v];
        }
        $data['cc'] = isset($data['cc']) ? serialize($data['cc']) : serialize([]);

        //附件
        foreach ($mail->getAttachments() as $attachment) {
            $data['attachments'][] = ["name" => $attachment->name, "filePath" => $attachment->filePath];
        }
        $data['attachments'] = isset($data['attachments']) ? serialize($data['attachments']) : serialize([]);

        //解析IP
        $mail_header = imap_fetchheader($mailbox->getImapStream(), $mailId, FT_UID);
        preg_match_all('/(\d+\.\d+\.\d+\.\d+)/', $mail_header, $matches);
        $data['ip'] = $matches[1][count($matches[1]) - 1];

        $data['reply_to_email'] = array_keys($mail->replyTo)[0];
        $data['reply_to_name'] = $mail->replyTo[$data['reply_to_email']];
        $data['create_time'] = time();

        $data_content = [];
        $data_content['message_id'] = $data['message_id'];
        $data_content['text_plain'] = !empty($mail->textPlain) ? $mail->textPlain : '';
        $data_content['text_html'] = !empty($mail->replaceInternalLinks($this->baseUrl())) ? $mail->replaceInternalLinks($this->baseUrl()) : '';

        //新进邮件 - true 老邮件
        if (isset($mailInfo->references) && self::isOldEmail($mailInfo->references)) {
            $data['p_message_id'] = self::getReferencesFirst($mailInfo->references);
            $existEmail = (new EmailInbox())->getByPMessageId($data['p_message_id']);

            $in_reply_to = isset($mailInfo->in_reply_to) ? $mailInfo->in_reply_to : "";

            if (!is_object($existEmail)) {
                //保存主数据
                $this->saveMail($mailbox, array_merge($data, ['references_str' => $mailInfo->references, 'in_reply_to' => $in_reply_to]), $data_content, $order_sn_ticket, false, true);
            } else {
                $ticket = $this->getTicket($data);
                if (is_object($ticket)) {
                    $this->saveMail($mailbox, array_merge($data, ['references_str' => $mailInfo->references, 'in_reply_to' => $in_reply_to]), $data_content, $order_sn_ticket, false, false, $ticket);
                } else {
                    $this->saveMail($mailbox, array_merge($data, ['p_message_id' => 0]), $data_content, $order_sn_ticket);
                }
            }
        } else {
            $this->saveMail($mailbox, $data, $data_content, $order_sn_ticket);
        }
    }

    /**
     * @param Mailbox $mailbox
     * @param array $inboxData
     * @param array $inboxContentData
     * @param $order_sn_ticket
     * @param bool $add_ticket
     * @param bool $fixMailData
     * @param null $ticketOld
     * @return bool
     */
    public function saveMail(Mailbox $mailbox, array $inboxData, array $inboxContentData, $order_sn_ticket, $add_ticket = true, $fixMailData = false, $ticketOld = null)
    {
        //声明变量基础值
        $emailInbox = null;
        $emailInboxContent = null;
        $ticket = null;

        DB::beginTransaction();

        //修复缺失的主信息
        if ($fixMailData) {
            $this->saveMail($mailbox, array_merge($inboxData, [
                "message_id" => $inboxData['p_message_id'],
                "p_message_id" => 0,
                "date" => time() - 86400 * 2,
                "is_virtual" => 1 //修复标记
            ]), array_merge($inboxContentData, [
                "message_id" => $inboxData['p_message_id'],
                "text_plain" => "fix",
                "text_html" => "fix"
            ]), $order_sn_ticket);
        }

        //新增收信
        $emailInbox = (new EmailInbox())->create($inboxData);
        if (is_object($emailInbox)) {
            //新增收信内容
            $emailInboxContent = (new EmailInboxContent())->create(array_merge($inboxContentData, ["email_id" => $emailInbox->email_id]));
            //新增工单
            if ($add_ticket) {
                $this->addTicket($emailInbox, $order_sn_ticket);
            }
        }
        if (is_object($emailInbox) && is_object($emailInboxContent)) {
            DB::commit();

            //打开Ticket
            if (is_object($ticketOld)) {
                EmailTicketNew::where("ticket_id", $ticketOld->ticket_id)->update(['sys_flag' => 'opened', 'update_time' => time(), 'last_reply_time' => time()]);
            }

            //记录邮件收取成功
            EmailLog::where("mail_id", $inboxData["service_mail_id"])->where("account_id", $inboxData["account_id"])->update(["status" => 1]);
            //邮件拉取成功后 - 统一删除邮件信息
            $this->deleteMail($mailbox, $inboxData["service_mail_id"]);
        } else {
            DB::rollBack();
        }
    }


    /**
     * @param EmailInbox $emailInbox
     * @param $order_sn_ticket
     * @param int $email_cs_admin_id
     * @return static
     */
    public function addTicket(EmailInbox $emailInbox, $order_sn_ticket, $email_cs_admin_id = 11)
    {
//        $p_message_id = $emailInbox->is_virtual == 1 ? $emailInbox->p_message_id : $emailInbox->message_id;
        $p_message_id = $emailInbox->message_id;
        switch ($this->site->site_id) {
            case 1:
                $ticket_sn_tmp = 'ST';
                break;
            case 3:
                $ticket_sn_tmp = 'FM';
                break;
            case 6:
                $ticket_sn_tmp = 'WO';
                break;
            case 7:
                $ticket_sn_tmp = 'SP';
                break;
            case 8:
                $ticket_sn_tmp = 'TEL';
                $ticket_data['is_tel'] = 1;
                break;
            //binkish
            case 10:
                $ticket_sn_tmp = 'BIN';
                break;
            default:
                $ticket_sn_tmp = 'OT';
        }
        $ticket_data['ticket_sn'] = $ticket_sn_tmp . '-' . sprintf('%03d', rand(0, 999)) . '-' . strval(time() - strtotime('2015-01-01'));
        $ticket_data['account_id'] = $this->account_id;
        $ticket_data['email_id'] = $emailInbox->email_id;
        $ticket_data['p_message_id'] = $p_message_id;
        $ticket_data['distribution_email'] = $emailInbox->reply_to_email;
        $ticket_data['email_cs_admin_id'] = $email_cs_admin_id;

        $ticket_data['sys_flag'] = 'opened';
        // 通过 message_id 检测邮件优先级
        if (stripos($p_message_id, 'high') !== false) {
            $ticket_data['sys_priority'] = 'high';
        } elseif (stripos($p_message_id, 'urgent') !== false) {
            $ticket_data['sys_priority'] = 'urgent';
        } else {
            $ticket_data['sys_priority'] = 'normal';
        }
        $ticket_data['order_sn'] = isset($order_sn_ticket) && !empty($order_sn_ticket) ? $order_sn_ticket : 0;
        $ticket_data['add_time'] = time();
        $ticket_data['update_time'] = time();
        $ticket_data['last_reply_time'] = time();

        $is_fr_site = strpos($order_sn_ticket, "_fr_site");
        if ($is_fr_site) {
            $ticket_data['is_fr'] = 1;
        }
        // paypal 账户发来的邮件，默认分配给程静, 状态为in progress
        if (stripos($emailInbox->from_email, 'paypal.com') !== false && $this->account_id == 3) {
            $ticket_data['sys_flag'] = "in_progress";
            $ticket_data['is_system'] = 1;
        }

        return EmailTicketNew::create($ticket_data);
    }

    public function assignAgent()
    {

        //首封邮件使用以下代码段
//        if ($email_cs_admin_id_from_tel) {
//            echo "---TEL:" . $email_cs_admin_id_from_tel, PHP_EOL;
//            //如果存在电话工单客服创建标记,并且记录之前创建的客服
//            //$site['site_id'] = 8;
//            $email_cs_admin_id = $email_cs_admin_id_from_tel;
//            unset($email_cs_admin_id_from_tel);
//        } else {
//            // 回复次数1临时全部给Linda：11
//            $email_cs_admin_id = 11;
//            echo "-one-_-";
//        }
//
//
//        //老邮件分配规则
//        if ($is_fr_site) {
//            //TODO
//            //郭庆49   王菊艳47  袁梅清87
//            $all_admin_id = array(49, 47, 87);
//            $rand = rand(0, count($all_admin_id) - 1);
//            $email_cs_admin_id = $all_admin_id[$rand];
//            $tmp_fr = true;
//        } else {
//            //分配邮件方式配置 关闭：客服规则收取分配 开启：主题规则分配 默认：关闭
//            $emailSetup = EmailSetup::where("name", "distribution_email_style")->first();
//            if (is_object($emailSetup) && $emailSetup->value == 1) {
//                $email_cs_admin_id = self::themeDistribute($data['reply_to_email'], $this->site, $data['subject']);
//            } else {
//                $email_cs_admin_id = self::distributeEmail($data['reply_to_email'], $this->site);
//            }
//        }
//
//
//        //sys_flag  处理时opened状态需要处理分配，其他不需要
//
//        //已存在的ticket ，检查客服是否还在岗，否则分配其他客服
//        $is_ticket_email_cs_admin_id = self::reply_is_exist_kf($ticket->email_cs_admin_id, $this->site->site_id);
//        if ($is_ticket_email_cs_admin_id) {
//            $update_ticket = M('email_ticket_new')->where(array('ticket_id' => $ticket->ticket_id))->save(array('sys_flag' => 'opened', 'update_time' => time(), 'last_reply_time' => time()));
//        } else {
//            $new_email_cs_admin_id = rand_email_admin_new($this->site->site_id);
//            $update_ticket = M('email_ticket_new')->where(array('ticket_id' => $ticket->ticket_id))->save(array('email_cs_admin_id' => $new_email_cs_admin_id, 'sys_flag' => 'opened', 'update_time' => time(), 'last_reply_time' => time()));
//        }
    }


    public static function reply_is_exist_kf($ticket_email_cs_admin_id, $site_id)
    {
        //获取站点对应的邮件账户
//        $account_id = M('email_account')->where(array('site_id' => $site_id))->find();
//        echo "--reply_is_exist_kf---site_id" . $site_id;
//        echo "--reply_is_exist_kf---account_id" . $account_id['account_id'];
//        //$sql = "SELECT email_cs_admin_id FROM bt_email_admin WHERE role = 'normal' AND status = 1 AND skype != 'new' AND email_cs_admin_id != 48";
//        $sql = "SELECT bt_email_admin.email_cs_admin_id
//				  FROM `bt_email_admin`
//				  LEFT JOIN bt_account_get ON bt_email_admin.admin_id= bt_account_get.admin_id
//				 WHERE (bt_account_get.is_get_email= 1)
//				   AND (bt_email_admin.status= 1)
//				   AND (bt_email_admin.email_cs_admin_id= {$ticket_email_cs_admin_id})
//				   AND (`bt_account_get`.`account_id` = {$account_id['account_id']}
//				   )";
//        echo $sql;
//        $email_cs_admin_id_arr = M()->query($sql);
//        var_dump($email_cs_admin_id_arr);
//        $email_cs_admin_id = $email_cs_admin_id_arr[0]['email_cs_admin_id'];
//        return $email_cs_admin_id ? $email_cs_admin_id : 0;
    }

    /**
     * @param array $data
     * @return Model|null|static
     */
    public function getTicket(array $data)
    {
        $model = EmailTicketNew::where("email_ticket_new.distribution_email", $data['from_email'])
            ->where("email_ticket_new.p_message_id", $data['p_message_id'])
            ->join("email_inbox", "email_ticket_new.email_id", "=", "email_inbox.email_id")
            ->first();

        if (!is_object($model)) {
            // 如果这里为空的话，说明 from_email 和 distribution_email 不是一个，也
            // 就是说，这封邮件不是由客户端发过来的 ，而是从前台网站发来的，那么用reply_to_email获取
            $model = EmailTicketNew::where("email_ticket_new.distribution_email", $data['reply_to_email'])
                ->where("email_ticket_new.p_message_id", $data['p_message_id'])
                ->join("email_inbox", "email_ticket_new.email_id", "=", "email_inbox.email_id")
                ->first();
        }
        return $model;
    }

    /**
     * @param Mailbox $mailbox
     * @param $mailId
     */
    public function deleteMail(Mailbox $mailbox, $mailId)
    {
        if (in_array($this->account_id, self::$notallow_delete)) {
            $mailbox->moveMail($mailId, "TempHistory");
        } else {
            $mailbox->deleteMail($mailId);
        }
    }

    /**
     * @param $message_id
     * @param $subject
     * @param $date
     * @return string
     */
    public static function createEmailSN($message_id, $subject, $date)
    {
        return md5($message_id . $subject . $date);
    }

    /**
     * @param $subject
     * @param $date
     * @return string
     */
    public static function createMessageID($subject, $date)
    {
        $str = md5($subject . $date . gettimeofday(true) . lcg_value() . uniqid(rand(1, 100000), true));
        return $str . '@orderplus.com';
    }

    /**
     * @param $references
     * @return bool
     */
    public static function isOldEmail($references)
    {
        if (isset($references) && !empty(trim($references))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $references
     * @return mixed
     */
    public static function getReferencesFirst($references)
    {
        if (isset($references) && !empty($references)) {
            //$first_reference = explode(' ', $references)[0];
            preg_match('/<(.*?)>/', $references, $matches);
            return $matches[1];
        }
        return "";
    }

    //分配邮件给客服 email_distribution
    //参数：$email_data, array $site, string $subject
    //返回：$email_cs_admin_id 客服ID
    //根据主题与客服关联关系 分配
    public static function themeDistribute($reply_to_email, $site, $subject)
    {
//        //优先从大客户表 bt_email_ka_bind 查找销售专员和客户关联，如果关联关系存在，邮件发送关联至绑定的销售专员
//        $ka_bind_info = M('email_ka_bind')->where(array('user_email' => $reply_to_email))->find();
//        if ($ka_bind_info) {
//            echo 'member ' . $reply_to_email . ' has been bound to ' . $ka_bind_info['sales_nickname'] . '[' . $ka_bind_info['sales_name'] . ']';
//            return $ka_bind_info['sales_id'];
//        }
//        // 寻找该邮箱是否在系统中已有对应的客服
//        // 如果有回复过的，说明之前也是按照主题分类分配过
//        // 如果没有回复，说明是新邮件，再次根据主题分类去分配
//        $is_distributed = M('email_distribution')->where(array('_string' => 'email_cs_admin_id IS NOT NULL', 'email' => $reply_to_email, 'site_id' => $site['site_id']))->find()['email_cs_admin_id'];
//        if ($is_distributed) {
//            // 判断是否已离职
//            $is_exist_admin = M('email_admin')->where(array('email_cs_admin_id' => $is_distributed))->find();
//            if ($is_exist_admin) {
//                echo "this_email_is_distributed_admin:" . $is_distributed . "---";
//                return $is_distributed;
//            } else {
//                //已被删除、离职
//                //根据主题分类判断
//                // $where_type['subject'] = $subject;
//                $where_type['name'] = array('like', "%$subject%");
//                $where_type['pid'] = 0;
//                //查找二级主题的顶级分类 ，找到一级分类下的客服组
//                $email_question_type_admins = M('email_question_type')->where($where_type)->find()['admins'];
//                if (empty($email_question_type_admins)) {
//                    //查找二级主题的顶级分类
//                    $where_pid['name'] = array('like', "%$subject%");
//                    $pid = M('email_question_type')->where($where_pid)->find()['pid'];
//                    if ($pid) {
//                        $email_question_type_admins = M('email_question_type')->where(array('id' => $pid))->find()['admins'];
//                    }
//
//                }
//                //匹配上
//                if ($email_question_type_admins) {
//                    //存在客服组
//                    $arr_admins = explode("|", $email_question_type_admins);
//                    if (count($arr_admins) > 0) {
//                        $rand = rand(0, count($arr_admins) - 1);
//                        $email_cs_admin_id = $arr_admins[$rand];
//                        echo "this_email_is_distributed_admin:$email_question_type_admins---";
//                        return $email_cs_admin_id;
//                    }
//                } else {
//                    //不存在客服组，到other里进行分配
//                    unset($email_question_type_admins);
//                    $email_question_type_admins = M('email_question_type')->where(array('name' => 'Others', 'pid' => 0))->find()['admins'];
//                    //存在客服组
//                    $arr_admins = explode("|", $email_question_type_admins);
//                    if (count($arr_admins) > 0) {
//                        $rand = rand(0, count($arr_admins) - 1);
//                        $email_cs_admin_id = $arr_admins[$rand];
//                        echo "this_email_is_distributed_admin:other_admins:---" . $email_cs_admin_id;
//                        //将第一次分配到的客服 存入分配记录表
//                        $data['email_cs_admin_id'] = $email_cs_admin_id;
//                        $data['email'] = $reply_to_email;
//                        $data['site_id'] = $site['site_id'];
//                        $data['add_time'] = time();
//                        $data['update_time'] = time();
//                        M('email_distribution')->add($data);
//                        return $email_cs_admin_id;
//                    }
//                }
//
//
//            }
//        } else {
//            $where_type['name'] = array('like', "%$subject%");
//            $where_type['pid'] = 0;
//            //查找二级主题的顶级分类 ，找到一级分类下的客服组
//            $email_question_type_admins = M('email_question_type')->where($where_type)->find()['admins'];
//            echo "test:" . $email_question_type_admins;
//
//            if (empty($email_question_type_admins)) {
//                //查找二级主题的顶级分类
//                $where_pid['name'] = array('like', "%$subject%");
//                $pid = M('email_question_type')->where($where_pid)->find()['pid'];
//                echo M('email_question_type')->getLastSql();
//                echo "ppp_ss";
//                var_dump($pid);
//                echo "ppp_ee";
//                if ($pid) {
//                    echo "-pid:" . $pid;
//                    $email_question_type_admins = M('email_question_type')->where(array('id' => $pid))->find()['admins'];
//                    echo "-pid's admins:" . $email_question_type_admins;
//                }
//
//            }
//            //匹配上
//            if ($email_question_type_admins) {
//                //存在客服组
//                $arr_admins = explode("|", $email_question_type_admins);
//                if (count($arr_admins) > 0) {
//                    $rand = rand(0, count($arr_admins) - 1);
//                    $email_cs_admin_id = $arr_admins[$rand];
//                    echo "this_email_is_distributed_admin:$email_question_type_admins---";
//                    return $email_cs_admin_id;
//                }
//            } else {
//                //不存在客服组，到other里进行分配
//                unset($email_question_type_admins);
//                $email_question_type_admins = M('email_question_type')->where(array('name' => 'Others', 'pid' => 0))->find()['admins'];
//                //存在客服组
//                $arr_admins = explode("|", $email_question_type_admins);
//                if (count($arr_admins) > 0) {
//                    $rand = rand(0, count($arr_admins) - 1);
//                    $email_cs_admin_id = $arr_admins[$rand];
//                    echo "this_email_is_distributed_admin:other_admins:---" . $email_cs_admin_id;
//                    return $email_cs_admin_id;
//                }
//            }
//            //将第一次分配到的客服 存入分配记录表
//            $data['email_cs_admin_id'] = $email_cs_admin_id;
//            $data['email'] = $reply_to_email;
//            $data['site_id'] = $site['site_id'];
//            $data['add_time'] = time();
//            $data['update_time'] = time();
//            M('email_distribution')->add($data);
//        }
    }

    //分配邮件给客服 email_distribution
    //参数：$email_data, array $site,
    //返回：$email_cs_admin_id 客服ID
    public static function distributeEmail($reply_to_email, $site)
    {
//        echo "----------------distributeEmail:-----------";
//        //var_dump($site);
//        //优先从大客户表 bt_email_ka_bind 查找销售专员和客户关联，如果关联关系存在，邮件发送关联至绑定的销售专员
//        //$ka_bind_info = M('email_ka_bind')->where(array('user_email'=>$reply_to_email))->find();
//        //if ($ka_bind_info) {
//        //	echo 'member '.$reply_to_email.' has been bound to '.$ka_bind_info['sales_nickname'].'['.$ka_bind_info['sales_name'].']';
//        //	return $ka_bind_info['sales_id'];
//        //}
//
//        // 寻找该邮箱是否在系统中已有对应的客服
//        $is_distributed = M('email_distribution')->where(array('_string' => 'email_cs_admin_id IS NOT NULL', 'email' => $reply_to_email, 'site_id' => $site['site_id']))->find()['email_cs_admin_id'];
//        if ($is_distributed) {
//            // 判断是否已离职
////			$is_exist_admin = M('email_admin')->where(array('email_cs_admin_id'=>$is_distributed,'status'=>'1','skype'=>'new'))->find();
//            //status:1在职 0：离职 ；0716 off
//            //$is_exist_admin = M('email_admin')->where(array('email_cs_admin_id'=>$is_distributed,'_string'=>'status = 1 and skype<>new'))->find();
//            $is_exist_admin = M('email_admin')
//                ->join('bt_admin ON bt_email_admin.admin_id = bt_admin.adminid')
//                ->join('bt_account_get ON bt_admin.adminid = bt_account_get.admin_id')
//                ->where(array('bt_email_admin.email_cs_admin_id' => $is_distributed,
//                        '_string' => 'bt_email_admin.status = 1 and bt_account_get.is_get_email')
//                )
//                ->find();
//            if ($is_exist_admin) {
//                echo "this_email_is_distributed_admin:" . $is_distributed . "---";
//                return $is_distributed;
//            } else {
//                //已被删除、离职
//                //todo
//                //$sql_fenpei = "SELECT email_cs_admin_id FROM bt_email_admin WHERE status = 1 AND skype != 'new' AND email_cs_admin_id != 48";
//                $email_cs_admin_id = rand_email_admin_new($site['site_id']);
//                /* $email_cs_admin_id_arr = M()->query($sql_fenpei);
//                $rand = rand(0, count($email_cs_admin_id_arr) - 1);
//                $email_cs_admin_id = $email_cs_admin_id_arr[$rand]['email_cs_admin_id']; */
//                echo "this_email_is_distributed_admin:lizhi---";
//                return $email_cs_admin_id;
//
//            }
//        }
//
//        $email = $reply_to_email;
//
//        //获取当前邮件账号下所有授权用户、要考虑到以后取消权限，造成ticket分配人失效
//        //$sql = "SELECT email_cs_admin_id FROM bt_email_admin WHERE role = 'normal' AND status = 1 AND skype != 'new' AND email_cs_admin_id != 48";
//        //$email_cs_admin_id_arr = M()->query($sql);
//        //$rand = rand(0, count($email_cs_admin_id_arr) - 1);
//        //$email_cs_admin_id = $email_cs_admin_id_arr[$rand]['email_cs_admin_id'];
//        echo "site_id:" . $site['site_id'] . PHP_EOL;
//        //老权限规则
//        //$email_cs_admin_id = getAccountAdmins($site['site_id']);
//        //新权限规则
//        $email_cs_admin_id = getAccountAdminsNew($site['site_id']);
//
//        $data['email_cs_admin_id'] = $email_cs_admin_id;
//        $data['email'] = $email;
//        $data['site_id'] = $site['site_id'];
//        $data['add_time'] = time();
//        $data['update_time'] = time();
//
//        M('email_distribution')->add($data);
//
//        //返回相应的销售id
//        echo "this_email_isNOT_distributed_admin:" . $email_cs_admin_id . "---";
//        return $email_cs_admin_id;
    }
}
