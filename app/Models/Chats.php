<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chats extends Model
{
    protected $table = 'chats';
    
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message_type',
        'message_text',
        'analysis',
        'output',
        'media_url',
        'media_mime_type',
        'media_sha256',
        'media_id',
        'whatsapp_message_id',
        'after_image',
        'date',
    ];

  
}
