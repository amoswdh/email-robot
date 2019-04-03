<?php

namespace Amos\MailRobot\Contracts;

interface EmailAccountContract
{
    /**
     * 邮件账号列表
     * @return mixed
     */
    public function all(  );
}