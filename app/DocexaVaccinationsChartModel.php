<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
use DB;
class DocexaVaccinationsChartModel extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_vaccinations_chart';
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

            $vaccineBrandGroups = DB::table('vaccine_brand_groups')->get();
           
         
            
        return $data;
       }else{
        return false;
       }
    }

    public function getPrecriptionChartOfWrtoUserMapId($usermapid){
      $data=[];
  
         $results = DocexaVaccinationsChartModel :: all();

         $resultWrtoUserMapId = docexaVaccinationsChartUsermapidModel :: where('user_map_id', $usermapid)->get();

         $vaccineBrandGroups = DB::table('vaccine_brand_groups')->get();
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

              if(count($resultWrtoUserMapId)>0){
               $brand = VaccinationBrandNameUsermapidModel :: where('user_map_id', $usermapid)->get();
               foreach($resultWrtoUserMapId as $result){
                  $brandresult = VaccinationBrandNameUsermapidModel :: where('vaccination_name',$result-> types)->where('user_map_id',$usermapid)->get();
                  Log:: info (['brandresult' => $brandresult ]);
                  $branddata =[];
                  foreach($brandresult as $brand){
                     $branddata [] = $brand;
                  }
               
                
               $dataWrtUserMapId [] =[
                  'id' => $result->id,
                  'time' => $result->time,
                  'category' => $result->category,
                  'types' =>  $result -> types,
                  'brand' => $branddata,
               ];
            }
              }
  
          return [
            'data' => $data,
            'dataWrtoUsermapid' => $dataWrtUserMapId,
            'vaccineBrandGroups' =>$vaccineBrandGroups
          ];
         }else{
          return false;
         }
      }
}