<?php

namespace Amos\MailRobot\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTicketNew extends Model
{
    protected $table = 'email_ticket_new';

    public $timestamps = false;

    public $primaryKey = 'ticket_id';

    protected $guarded = [];
}
