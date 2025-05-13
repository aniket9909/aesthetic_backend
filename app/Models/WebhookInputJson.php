<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookInputJson extends Model
{
    PROTECTED $table = 'webhook_input_jsons';
    protected $fillable = [
        'whatsapp_business_account',
        'json_identification_id',
        'images_url',
        'long_json',
        'data',
    ];
    protected $casts = [
        'long_json' => 'array',
    ];

    public function storeLongJson($json)
    {
        $this->long_json = $json;
        $this->save();
    }

    public function getLongJsonAsArray()
    {
        return $this->long_json ?? [];
    }
}
