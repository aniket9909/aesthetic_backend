<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsentForm;
use App\Models\DoctorSuggestedTreatment;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Patientmaster;
use Carbon\Carbon;



class ConsentFormController extends Controller
{
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'consent_id' => 'nullable|integer',
            // 'doctor_id' => 'required',
            // 'patient_id' => 'required',
            'form_type' => 'required|in:consent,lhr',
            'name' => 'required|string',
            'address' => 'required|string',
            'contact_no' => 'required|string',
            'email' => 'nullable|email',
            'date' => 'required|date',
            'exp_date' => 'nullable|date',
            'medical_history' => 'nullable|string',
            'medications' => 'nullable|string',
            'procedures' => 'required|string',
            'doctor_sign' => 'nullable|string',
            'patient_sign' => 'nullable|string',
            'treatment_history' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            DB::beginTransaction();
            $data = $validator->validated();

            // Check if consent_id is present and not null, update if exists, else create new
            if (!empty($data['consent_id'])) {
                $form = ConsentForm::find($data['consent_id']);
                if ($form) {
                    $form->fill($data);
                } else {
                    $form = new ConsentForm($data);
                }
            } else {
                $form = new ConsentForm($data);
            }

            // Flags
            $form->is_submitted_by_doctor = (
                $request->has('doctor_sign') &&
                !empty($request->input('doctor_sign')) &&
                $request->input('doctor_sign') !== null
            ) ? true : false;

            $form->is_submitted_by_patient = (
                $request->has('patient_sign') &&
                !empty($request->input('patient_sign')) &&
                $request->input('patient_sign') !== null
            ) ? true : false;
            $form->is_complete = $form->is_submitted_by_doctor && $form->is_submitted_by_patient;
            Log::alert("message", ['is_submitted_by_doctor' => $form->is_submitted_by_doctor]);

            $form->save();

            // Store treatment history if present
            if (!empty($data['treatment_history'])) {
                // If updating, delete old treatment histories first
                if (!empty($data['consent_id']) && $form->exists) {
                    $form->treatmentHistories()->delete();
                }
                foreach ($data['treatment_history'] as $treatment) {
                    $form->treatmentHistories()->create([
                        'treatment' => $treatment['treatment'],
                        'service_id' => $treatment['service_id'],
                        'mode' => $treatment['mode'],
                        'paid' => $treatment['paid'],
                        'total_amount' => $treatment['total_amount'],
                        'date' => $treatment['date'],
                    ]);
                }
            }

            if ($form->is_submitted_by_doctor == true && $form->is_complete != true) {
                // WhatsappController::sendTextToWhatsApp($form->doctor->waba_number, "Consent form submitted by doctor: {$form->name}");
                $whatsappController = new WhatsappController();
                $whatsappController->sendTextToWhatsApp(new Request([
                    // 'request' => $request,
                    'from' => "919321962947",
                    'to' => "7058107992",
                    'message' => "Please fill this in below link:\nhttps://dev.aestheticai.globalspace.in/consult-form?patient_id={$form->patient_id}&type={$form->form_type}&doctor={$form->doctor_id}&flag=0&consent_id={$form->id}",
                ]));
                // $whatsappController->sendTemplateToWhatsApp(new Request([
                //     // 'request' => $request,
                //     // 'from' => "919321962947",
                //     'to' => "7058107992",
                //     'value1' => "https://dev.aestheticai.globalspace.in/consult-form?patient_id={$form->patient_id}&type={$form->form_type}&doctor={$form->doctor_id}&flag=0&consent_id={$form->id}",
                // ]));
            }
            DB::commit();
            return response()->json(['message' => 'Consent form stored successfully', 'form_id' => $form->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing consent form: {$e->getMessage()}", ['exception' => $e]);
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function createEmptyForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'patient_id' => 'required',
            'form_type' => 'required|in:consent,lhr',
            'staff_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $patientInfo = Patientmaster::where('patient_id', $request->patient_id)
            ->select('patient_name', 'address', 'mobile_no', 'email_id')
            ->first();

        try {
            $data = $validator->validated();

            $form = new ConsentForm();
            $form->doctor_id = $data['doctor_id'];
            $form->patient_id = $data['patient_id'];
            $form->form_type = $data['form_type'];
            $form->name =  $patientInfo->patient_name ?? '';
            $form->address =  $patientInfo->address ?? '';
            $form->contact_no =  $patientInfo->mobile_no ?? '';
            $form->email =  $patientInfo->email_id ?? '';
            $form->procedures =  $data['staff_name'] ?? '';
            $form->date = Carbon::now()->format('Y-m-d');
            $form->is_submitted_by_doctor = false;
            $form->is_submitted_by_patient = false;
            $form->is_complete = false;
            $form->save();

            return response()->json([
                'status' => true,
                'message' => 'Empty consent form created successfully',
                'form_id' => $form->id,
                'data' => $form
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating empty consent form: {$e->getMessage()}", ['exception' => $e]);
            return response()->json([
                "status" => false,
                'error' => 'An error occurred while creating the empty form.'
            ], 500);
        }
    }

    public function show($id)
    {


        $form = ConsentForm::where('id', $id)
            ->with('treatmentHistories')
            ->first();
        if (!$form) {
            return response()->json(["status" => false, 'data' => [], 'message' => 'Consent form not found'], 404);
        }
        return response()->json([
            "status" => true,
            "data" => $form
        ]);
    }

    public function index()
    {

        return response()->json([
            "status" => true,
            "data" => ConsentForm::with('treatmentHistories')->get()
        ]);
    }

    public function getByPatientId($patientId)
    {
        $forms = ConsentForm::with('treatmentHistories')
            ->where('patient_id', $patientId)
            ->get();

        return response()->json([
            "status" => true,
            "data" => $forms
        ]);
    }
    public function getByPatientIdForApp($patientId)
    {
        $forms = ConsentForm::where('patient_id', $patientId)
            ->select('id', 'doctor_id', 'patient_id', 'form_type', 'name', 'address', 'contact_no', 'email', 'date', 'exp_date', 'medical_history', 'medications', 'procedures', 'doctor_sign', 'patient_sign', 'is_submitted_by_doctor', 'is_submitted_by_patient', 'is_complete')
            ->get();

        return response()->json([
            "status" => true,
            "data" => $forms
        ]);
    }

    public function sendConsentFormLinkToPatient($consentId)
    {
        $form = ConsentForm::find($consentId);

        if (!$form) {
            return response()->json(['status' => false, 'message' => 'Consent form not found'], 404);
        }

        // Get patient's WhatsApp number from Patientmaster
        $patient = Patientmaster::where('patient_id', $form->patient_id)->first();
        $patientWhatsappNumber = $patient ? $patient->mobile_no : null;

        if (!$patientWhatsappNumber) {
            return response()->json(['status' => false, 'message' => 'Patient WhatsApp number not found'], 404);
        }

        $whatsappController = new WhatsappController();
        $whatsappController->sendTextToWhatsApp(new Request([
            'from' => "919321962947",
            // 'to' => $patientWhatsappNumber,
            'to' => '7058107992',
            'message' => "Please fill this in below link:\nhttps://dev.aestheticai.globalspace.in/consult-form?patient_id={$form->patient_id}&type={$form->form_type}&doctor={$form->doctor_id}&flag=0&consent_id={$form->id}",
        ]));

        return response()->json(['status' => true, 'message' => 'Consent form link sent to patient']);
    }
}
