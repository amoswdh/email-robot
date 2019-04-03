<?php

namespace Amos\MailRobot\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    //
    protected $table = "email_account";

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
    public function login(){
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function password(){
        return $this->password;
    }

    /**
     * @return string
     */
    public function attachmentsDir(){
        return '/storage/Public/Email/attachment/account_'.$this->account_id .'/in';
    }
}
