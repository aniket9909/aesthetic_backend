<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FeedbackIdentifier extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'feedback_identifier';
    protected $primaryKey = 'id';
}