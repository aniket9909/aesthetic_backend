<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
class VaccinationBrandNameUsermapidModel
 extends Model
 {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'vaccination_brand_name_user_map_id';
    protected $primaryKey = 'id';

    public function getPrecriptionChartOf(){
    $data=[];

       $results = DocexaVaccinationsChartModel :: all();
        if($results){
            $brand = brandModel::all();
            foreach($results as $result){
               
               $brandresult= brandModel :: Where('vaccination_name',$result->types)->get();
               Log::info(['brandresult'=> $brandresult]);
                $branddata =[];
               foreach($brandresult as $brand){
                  $branddata [] =$brand;
               }

               $data [] =[
                'id' => $result->id,
                'time' => $result->time,
                'category' => $result->category,
                'types' =>  $result -> types,
                'brand' => $branddata,
               ];
            }

        return $data;
       }else{
        return false;
       }
    }


}