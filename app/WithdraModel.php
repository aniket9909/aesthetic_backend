<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class WithdraModel extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_withdraw_transcation';
    protected $primaryKey = 'id';  

    public function getwithdrawDetails($usermapId)
    {
            $data = WithdraModel::where('user_map_id', $usermapId)->get();
                if ($data) {
                    return $data;
                }
    }
}
