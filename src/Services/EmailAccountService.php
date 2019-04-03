<?php

namespace Amos\MailRobot\Services;

use Amos\MailRobot\Contracts\EmailAccountContract;
use App\Models\EmailAccount;

class EmailAccountService implements EmailAccountContract
{

    /**
     * EmailAccountService constructor.
     * @param EmailAccount $emailAccount
     */
    public function __construct( EmailAccount $emailAccount )
    {
        $this->emailAccount = $emailAccount;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all()
    {
        // TODO: Implement all() method.
        return $this->emailAccount->where("status","1")->orderBy('account_id', 'desc')->get();
    }
}