<?php

/**
 * Docexa Doctor Micro Service API
 *
 * OpenAPI spec version: 1.0.0
 * Contact: satish.soni@globalspace.in
 *
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen.git
 * Do not edit the class manually.
 */

namespace App\Http\Controllers;

use App\Certificate;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use \Mpdf\Mpdf;
use DB;
use Log;

class CertificateApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }
    /**
     * @OA\Get(
     * path="/establishments/users/{esteblishmentusermapID}/certificate",
     * tags={"Certificate"},
     *    
     * @OA\Parameter(
     *         name="esteblishmentusermapID",
     *         in="path",
     *         description="user map id",
     *         required=true,
     *         example="65887",
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Response(
     *         response="200",
     *         description="treatment detail master data",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="success")
     *        )
     *     ),
     * @OA\Response(
     *         response="400",
     *         description="Error: Bad request. required parameters is not supplied.",    
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="esteblishment User Map ID not found")
     *        )
     *     ),
     * )
     */
    public function list($esteblishmentusermapID)
    {

        if (!isset($esteblishmentusermapID)) {
            return response()->json(['status' => 'fail', 'msg' => 'esteblishment User Map ID not found'], 400);
        }
        $certificate = Certificate::where('user_map_id',$esteblishmentusermapID)->get();
        return response()->json(['status' => "success", 'certificate' => $certificate], 200);
    } 
    /**
     * @OA\Post(
     * path="/establishments/users/{esteblishmentusermapID}/certificate",
     * tags={"Certificate"},
     *    
     * @OA\Parameter(
     *         name="esteblishmentusermapID",
     *         in="path",
     *         description="user map id",
     *         required=true,
     *         example="65887",
     *         @OA\Schema(type="string")
     *     ),
    * @OA\RequestBody(
     *  required=true,
     *  description="certificate details",
     *  @OA\JsonContent(
     *      type="object",
     *     @OA\Property(property="type", type="string", example="sick leave"),
     *      @OA\Property(property="patient_name", type="string", example="satish soni"),
     *       @OA\Property(property="diagnosis", type="string", example=""),
     *       @OA\Property(property="form_date", type="string", example="2023-01-01"),
     *       @OA\Property(property="age", type="string", example="20"),
     *      @OA\Property(property="treatment_date", type="string", example="2023-01-01"),
     *      @OA\Property(property="to_date", type="string", example="2023-01-01"),
     *      @OA\Property(property="note", type="text", example="notes")
     *  ),
     * ),
     * @OA\Response(
     *         response="200",
     *         description="treatment detail master data",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="success")
     *        )
     *     ),
     * @OA\Response(
     *         response="400",
     *         description="Error: Bad request. required parameters is not supplied.",    
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="esteblishment User Map ID not found")
     *        )
     *     ),
     * )
     */
    public function generate($esteblishmentusermapID, Request $request)
    {
        $data = $request->all();
        if (!isset($esteblishmentusermapID)) {
            return response()->json(['status' => 'fail', 'msg' => 'esteblishment User Map ID not found'], 400);
        }
        $certi = new Certificate();
        $certi->type = $data['type'];
        $certi->patient_name = $data['patient_name'];
        $certi->diagnosis = $data['diagnosis'];
        $certi->form_date = $data['form_date'];
        $certi->age = $data['age'];
        $certi->treatment_date = $data['treatment_date'];
        $certi->to_date = $data['to_date'];
        $certi->note = $data['note']; 
        $certi->user_map_id = $esteblishmentusermapID;
        $certi->save();
        $certificate = Certificate::where('user_map_id',$esteblishmentusermapID)->get();
        return response()->json(['status' => "success", 'certificate' => $certificate], 200);
    }
     /**
     * @OA\Get(
     * path="/establishments/users/{esteblishmentusermapID}/certificate/{certificateID}",
     * tags={"Certificate"},
     *    
     * @OA\Parameter(
     *         name="esteblishmentusermapID",
     *         in="path",
     *         description="user map id",
     *         required=true,
     *         example="65887",
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Parameter(
     *         name="certificateID",
     *         in="path",
     *         description="certificateID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(type="string")
     *     ),
     * @OA\Response(
     *         response="200",
     *         description="treatment detail master data",
     *    @OA\JsonContent(
     *       @OA\Property(property="success", type="string", example="success")
     *        )
     *     ),
     * @OA\Response(
     *         response="400",
     *         description="Error: Bad request. required parameters is not supplied.",    
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="esteblishment User Map ID not found")
     *        )
     *     ),
     * )
     */
    public function details($esteblishmentusermapID,$certificateID,Request $request)
    {
        if (!isset($esteblishmentusermapID)) {
            return response()->json(['status' => 'fail', 'msg' => 'esteblishment User Map ID not found'], 400);
        }

        // $certificate = Certificate::find($certificateID);
        // return View('certificate.index', ['certificate' => $certificate]);
        //create PDF
        $mpdf = new Mpdf();
        // $header = trim($request->get('header', ''));
        // $footer = trim($request->get('footer', ''));
        $header = "";
        $footer = "";
        if (strlen($header)) {
            $mpdf->SetHTMLHeader($header);
        }

        if (strlen($footer)) {
            $mpdf->SetHTMLFooter($footer);
        }

        // if ($request->get('show_toc')) {
        //     $mpdf->h2toc = array(
        //         'H1' => 0,
        //         'H2' => 1,
        //         'H3' => 2,
        //         'H4' => 3,
        //         'H5' => 4,
        //         'H6' => 5
        //     );
        //     $mpdf->TOCpagebreak();
        // }
        $certificate = Certificate::find($certificateID);
        $user = new User();
        $doctor = $user->autologin($certificate->user_map_id);
        
        $view = View::make('certificate.index', ['certificate' => $certificate,'theme' => "theme1",'doctor'=>$doctor, 'link' => '/prescription/generate/pdf'])->render();
        $mpdf->SetTitle("Certificate - #00" . $certificate->id);
        $mpdf->WriteHTML($view);
        return $mpdf->output("Certificate - #00" . $certificate->id . "pdf", 'I');
    }
    
}
