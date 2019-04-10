<?php

namespace Amos\MailRobot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmailInbox extends Model
{
    protected $table = 'email_inbox';

    public $timestamps = false;

    public $primaryKey = 'email_id';

    protected $guarded = [];

    /**
     * @param $p_message_id
     * @return Model|null|static
     */
    public function getByPMessageId($p_message_id)
    {
        return $this->where("message_id", $p_message_id)->first();
    }
}
