<?php

namespace App;
use App\PermissionTable;

use Illuminate\Database\Eloquent\Model;
class Permission extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'permission_relationships';
    public $timestamps = true;


    public function permission()
    {
        return $this->belongsTo(PermissionTable::class); // links this->course_id to courses.id
    }
}
