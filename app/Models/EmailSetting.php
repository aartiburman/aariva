<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = [
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'status',
        'use_alternate_smtp',
        'alt_mail_driver',
        'alt_mail_host',
        'alt_mail_port',
        'alt_mail_username',
        'alt_mail_password',
        'alt_mail_encryption',
        'alt_mail_from_address',
        'alt_mail_from_name',
    ];
}
