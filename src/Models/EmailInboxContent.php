<?php

namespace Amos\MailRobot\Models;

use Illuminate\Database\Eloquent\Model;

class EmailInboxContent extends Model
{
    protected $table = 'email_inbox_content';
    
    public $timestamps = false;
    
    public $primaryKey = 'id';

    protected $guarded = [];
}