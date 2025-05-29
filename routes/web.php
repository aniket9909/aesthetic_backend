<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SkinAnalysisController;
use App\Patientmaster;
use Illuminate\Http\Request;


$router->get('/images/{filename}', function ($filename) {
    $path = base_path('skin_images/' . $filename); // Make sure the path is correct
    if (!file_exists($path)) {
        $path = base_path('skin_images/after_' . $filename); // Check in the after_images directory
    }
    if (!file_exists($path)) {
        abort(404, 'Image not found');
    }

    // Get the file contents
    $file = file_get_contents($path);

    // Get the MIME type of the file
    $mimeType = mime_content_type($path);

    // Return a file response with proper headers
    return response()->make($file, 200, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
    ]);
});



$router->get('admin/log', [
    'middleware' => 'log'
]);
$router->get('testjob', 'JobApi@index');
$router->group(['prefix' => 'v3'], function () use ($router) {
    $router->get('googlesheet', 'SpreadsheetController@masterdata');
    $router->get('oauth2callback', 'SpreadsheetController@oauth2callback');
    $router->get('appointment/{appointment_encrypted_id}', 'DoctorsApi@appointmentv3');
    //$router->get('googlesheet', 'SpreadsheetController');
});
$router->group(['prefix' => 'api/v4'], function () use ($router) {
    // public routes
    $router->post('/register', 'Auth\ApiAuthController@register');
    // ...

});
$router->group(['prefix' => 'api/v4', 'middleware' => ['auth:api']], function () use ($router) {

    //$router->get('/user', 'Auth\ApiAuthController@logout');
    $router->get('doctors/search/vaccine', 'DoctorsApi@searchdoctorforvaccine');
});

$router->group(['prefix' => 'pfizer'], function () use ($router) {
    $router->get('googlesheet', 'SpreadsheetController@masterdata');
    $router->get('updatesheet', 'SpreadsheetController@updatedata');
    $router->get('oauth2callback', 'SpreadsheetController@oauth2callback');
    $router->get('appointment/{appointment_encrypted_id}', 'DoctorsApi@appointmentv3');
    $router->post('appointment/create', 'DoctorsApi@createAppointmentv2');
    $router->get('payment/{appointment_encrypted_id}/charge', 'DoctorsApi@createpaymentv2');
    $router->get('payment/{appointment_encrypted_id}/pay', 'DoctorsApi@payv2');
    $router->post('payment/{appointment_encrypted_id}/fail', 'DoctorsApi@payfailv2');
    $router->post('payment/{appointment_encrypted_id}/success', 'DoctorsApi@paysuccessv2');
    //$router->get('googlesheet', 'SpreadsheetController');
});


$router->group(['prefix' => 'api/v1/feedback'], function () use ($router) {
    $router->get('', 'FeedbackApi@get'); // Get all feedback
    $router->post('', 'FeedbackApi@post'); // Create new feedback
});

$router->group(['prefix' => 'api/goroga/v1'], function () use ($router) {
    //doctor list
    //https://www.practo.com/search/doctors?results_type=doctor&q=%5B%7B%22word%22%3A%22Dentist%22%2C%22autocompleted%22%3Atrue%2C%22category%22%3A%22subspeciality%22%7D%5D&city=Ahmedabad
    $router->get('search/doctors', 'GorogaApi@doctor_search');

    //book appointment
    //get slot on userMap_id
    //time slot date usermap id

});

$router->group(['prefix' => 'api/v3/patient'], function () use ($router) {
    $router->post('auth/public/confirmOTP', 'PatientApi@confirmOTP');
    $router->post('auth/public/confirmOtp', 'PatientApi@confirmOTPv2');
    $router->get('auth/public/generateOTP/{mobile}', 'PatientApi@generateOTP');
});

$router->group(['prefix' => 'api/v4'], function () use ($router) {
    $router->post('establishments/users/{esteblishmentusermapID}/prescription', 'PrescriptionApi@savePrescriptionV4');
    $router->get('vaccination/{patientId}', 'PrescriptionApi@getvaccinationDetails');
    $router->post('establishments/users/prescritpion/save/{usermapId}', 'DocexaGenieApis@saveDocexaGeniePrescriptionImage');
});

$router->group(['prefix' => 'api/v3'], function () use ($router): void {
    $router->post('establishments/users/prescritpion/save/{usermapId}', 'DocexaGenieApis@saveDocexaGeniePrescriptionImage');

    // $router->get('slotdetails/{id}','ClinicApi@getslotdetails');

    $router->get('masterdata', 'DoctorsApi@masterdata');
    $router->post('fileupload', 'DoctorsApi@fileUpload');
    $router->get('signedurl', 'DoctorsApi@signedurl');
    $router->get('doctors/location', 'MasterApi@getlocation');
    $router->get('doctors/specilization', 'MasterApi@getspecilization');
    $router->get('doctors/search', 'DoctorsApi@searchdoctor');
    $router->get('doctors/search/vaccine', 'DoctorsApi@searchdoctorforvaccine');

    $router->get('doctors/{handle}', 'DoctorsApi@addPet');

    $router->post('doctors/registerprecheck', 'DoctorsApi@registerprecheck');
    $router->post('doctors/register', 'DoctorsApi@register');
    $router->post('doctors/update', 'DoctorsApi@doctorupdate');
    $router->post('doctors/register/vaccine', 'DoctorsApi@pfizerdoctorregister');
    $router->post('doctors/updatehandle', 'DoctorsApi@updatehandle');
    $router->get('doctors/slotdetails/{esteblishmentUserMapID}', 'DoctorsApi@slotdetails');
    $router->get('doctors/skudetails/{esteblishmentUserMapID}', 'DoctorsApi@skudetails');
    $router->post('doctors/getappointment', 'DoctorsApi@getappointment');
    $router->post('doctors/scheduleappointment', 'DoctorsApi@scheduleAppointment');
    $router->post('doctors/createappointment', 'DoctorsApi@createAppointment');

    $router->get('auth/otp/{mobileno}', 'DoctorsApi@sendOtp');
    $router->post('auth/login', 'DoctorsApi@login');
    $router->get('auth/otp/{mobileno}/login', 'DoctorsApi@loginotp');

    // forgot password
    $router->post('forgotPassword', 'DoctorsApi@forgotPassword');
    $router->post('changePassword', 'DoctorsApi@changePassword');

    // medical certificate
    $router->post('medicalCertificate/{doctorId}', 'DoctorsApi@medicalCertificate');
    $router->get('medicalCertificate/{doctorId}/{patientId}', 'DoctorsApi@getmedicalCertificate');
    $router->get('medicalCertificate/{doctorId}/bypatientId/{patientId}', 'DoctorsApi@getmedicalCertificate');
    $router->get('medicalCertificates/{doctorId}', 'DoctorsApi@getmedicalCertificatesListUnderDoctor');
    $router->get('medicalCertificate/data', 'DoctorsApi@getMedicalCertificateData');

    $router->post('medicalCertificatenew/{doctorId}/{clinicId}/{patientId}', 'PrescriptionApi@addMedicalCertificate');
    $router->get('medicalCertificatenew/{doctorId}/{clinicId}/{page}/{limit}', 'PrescriptionApi@getmedicalcertificate');
    $router->get('medicalCertificatenew/{doctorId}/{patientId}', 'PrescriptionApi@getmedicalcertificatebypatientId');
    $router->get('certificate/{id}', 'PrescriptionApi@getmedicalcertificatebyId');




    $router->post('transcationHistory', 'TranscationApi@getTranscationHistoryOfDoctor');
    $router->put('transcation/update', 'TranscationApi@updateTranscationDetails');

    $router->post('prescription/data/{id}', 'DoctorsApi@signUploadMicrosoftAzure');
    $router->get('prescription/data/{id}', 'DoctorsApi@getUploadedSign');

    $router->post('record/upload/{id}/{patientId}', 'DoctorsApi@UploadRecordMicroSoftAzure');
    $router->get('record/upload/{id}/{patientId}', 'DoctorsApi@getrecord');

    $router->post('save/prescription/{id}/{patientId}', 'DoctorsApi@savePrescriptionTos3');



    $router->post('receipe', 'DoctorsApi@uploadReceipe');

    $router->post('upload/precription/{userMapId}', 'DocexaGenieApis@uploadPrescription');
    $router->get('genieprescription/{userMapId}/{pageId}', 'DocexaGenieApis@getuploadPrescription');
    $router->get('genieprescription/{userMapId}', 'DocexaGenieApis@getTotalPrescription');


    $router->post('upload/offlineprecription/{userMapId}', 'DocexaGenieApis@uploadOfflinePrescription');

    $router->post('upload/offlineprecriptionv5/{userMapId}', 'DocexaGenieApis@uploadOfflinePrescriptionv5');

    $router->put('delete/offlinePrescription/{id}', 'DocexaGenieApis@deleteOfflinePrescription');
    $router->get('deleted/offlinePrescription/{user_map_id}', 'DocexaGenieApis@deletedOfflinePrescription');
    $router->post('updateOfflineSaved/flag/{userMapId}', 'DocexaGenieApis@updateFlag');



    $router->get('prescription/view/{id}/{patientId}/{booking_id}', 'DoctorsApi@PrescriptionViewv2');
    $router->get('prescription/add/view/{id}/{patientId}/{booking_id}', 'DoctorsApi@PrescriptionViewv3');

    $router->post('doctor/appointments', 'DoctorsApi@getTodaysApppointmentsv2');
    $router->post('doctor/seenPrescription', 'DoctorsApi@getSeenPrescription');
    $router->post('doctor/cancelledApt', 'DoctorsApi@getCancelledApt');
    $router->post('doctor/appointments/v2', 'DoctorsApi@getTodaysApppointmentsv2');


    $router->post('doctor/todaysAppointment', 'DoctorsApi@getTodaysApppointmentsV1');
    $router->post('doctor/todaysAppointment/search', 'DoctorsApi@searchInTodaysApppointmentsV1');

    $router->post('doctor/todaysAppointment/patient', 'DoctorsApi@getTodaysApppointmentsForParticularPatient');




    $router->get('v1/prescription/view/{id}/{patientId}/{booking_id}', 'DoctorsApi@PrescriptionViewv1');
    $router->get('vaccination/chart', 'PrescriptionApi@getPrecriptionChart');
    $router->post('add/vaccination', 'PrescriptionApi@addVaccination');
    $router->get('vaccination/chart/{usermapid}', 'PrescriptionApi@getVaccinationChartByUserMapId');
    $router->get('vaccination/dueList/{usermapid}/{patientId}', 'PrescriptionApi@getVaccinationDueListByUserMap');
    $router->post('add/brandName', 'PrescriptionApi@addBrandName');

    $router->get('getvaccinationgiven/{usermapid}/{patientId}', 'PrescriptionApi@getVaccinationGiven');


    $router->post('precription/layout/{id}', 'PrescriptionApi@prescriptionLayoutSave');
    $router->get('precription/layout/{id}', 'PrescriptionApi@getPrescriptionLayoutData');

    $router->post('vaccination/due', 'PrescriptionApi@saveVaccinationDetails');
    $router->post('vaccination/due/new', 'PrescriptionApi@saveVaccinationDetailsv1w');
    $router->post('vaccination/given', 'PrescriptionApi@saveGivenVaccinationDetailsV11');
    $router->post('vaccination/given/v1', 'PrescriptionApi@saveGivenVaccinationDetailsV1');
    $router->put('givenVaccination/update', 'PrescriptionApi@UpdateGivenVaccinationDetails');
    $router->put('dueVaccination/update', 'PrescriptionApi@UpdateDueVaccinationDetails');

    $router->put('vaccination/prescription/update', 'PrescriptionApi@VaccinationPrescriptionUpdate');
    $router->put('vaccination/brand/update', 'PrescriptionApi@VaccinationBrandUpdate');

    $router->post('vaccination/due/neww', 'PrescriptionApi@saveVaccinationDetailsv1w');

    $router->delete('vaccination/delete/{id}', 'PrescriptionApi@deleteVaccination');
    $router->post('vaccination/givenv11', 'PrescriptionApi@saveGivenVaccinationDetailsV11');








    $router->post('todaysPrecription/patientDetails', 'PrescriptionApi@getPatientDetailsOfThetodaysPrecription');

    $router->post('prescriptionNotes/save/{usermapId}/{patientId}', 'PrescriptionApi@prescriptionNotesSave');
    $router->get('getprescriptionNotes/{usermapId}/{patientId}', 'PrescriptionApi@getprescriptionNotesSave');


    $router->put('setFlag/patient', 'PrescriptionApi@setFlagForPatient');
    $router->get('getFlag/patient/{usermapId}/{patientId}', 'PrescriptionApi@getflagStatusOfPatient');

    $router->get('getVaccination/list/{usermapid}/{patientId}', 'PrescriptionApi@getVaccinationDueList');


    // $router->post('appointment', 'DoctorsApi@listappointment');
    $router->post('appointment', 'DoctorsApi@listappointmentupdated');
    $router->post('todaysappointment/{page}/{limit}', 'DoctorsApi@getTodaysAptByPagination');
    $router->post('pastappointment/{page}/{limit}', 'DoctorsApi@getpastappointmentByPagination');
    $router->post('upcomingAppointment/{page}/{limit}', 'DoctorsApi@getupcomingappointmentByPagination');

    $router->post('getPatients/WithoutScheduledApt/{page}/{limit}', 'DoctorsApi@getPatientsWithoutScheduledApt');

    $router->post('appointment/patient', 'DoctorsApi@listappointmentupdatedForParticularPatient');


    $router->get('appointment/{appointment_encrypted_id}', 'DoctorsApi@appointment');
    $router->get('hospital/{hospitalID}/appointment/{appointment_encrypted_id}', 'DoctorsApi@hospitalappointment');
    $router->put('appointment/{appointment_encrypted_id}/prescription', 'DoctorsApi@prescription');


    $router->put('appointment/{appointment_encrypted_id}', 'DoctorsApi@updateappointment');
    $router->post('appointment/update', 'DoctorsApi@scheduleAppointment');
    $router->post('appointment/create', 'DoctorsApi@createAppointment');
    $router->post('appointment/updatee', 'DoctorsApi@scheduleappointmentv9');

    $router->post('appointment/createnew', 'DoctorsApi@createAppointmentV4');
    $router->post('appointment/v1', 'DoctorsApi@listappointmentupdated');
    $router->post('appointment/createForWalkIn', 'DoctorsApi@createAppointmentwalkinV5');






    $router->get('payment/{appointment_encrypted_id}/charge', 'DoctorsApi@createpayment');
    $router->get('payment/{appointment_encrypted_id}/pay', 'DoctorsApi@pay');
    $router->get('hospital/payment/{appointment_encrypted_id}/pay', 'DoctorsApi@hospitalpay');
    $router->post('payment/{appointment_encrypted_id}/fail', 'DoctorsApi@payfail');
    $router->post('payment/{appointment_encrypted_id}/success', 'DoctorsApi@paysuccess');
    $router->post('hospital/payment/{appointment_encrypted_id}/fail', 'DoctorsApi@payhospitalfail');
    $router->post('hospital/payment/{appointment_encrypted_id}/success', 'DoctorsApi@payhospitalsuccess');
    $router->post('payment/paywebhook', 'DoctorsApi@paywebhook');

    $router->get('establishments/users/{esteblishmentUserMapID}/skus', 'SkuApi@skudetails');
    $router->get('establishments/hospital/{hospitalID}/users/{esteblishmentUserMapID}/skus', 'SkuApi@hospitalskudetails');
    $router->put('establishments/users/{esteblishmentUserMapID}/skus', 'SkuApi@addsku');
    $router->patch('establishments/users/{esteblishmentUserMapID}/skus/{id}', 'SkuApi@editsku');
    $router->delete('establishments/users/{esteblishmentUserMapID}/skus/{id}', 'SkuApi@deletesku');
    $router->patch('establishments/users/{esteblishmentUserMapID}/skus/{id}/default', 'SkuApi@setdefaultskudetails');
    $router->patch('establishments/users/{esteblishmentUserMapID}/skus/{id}/enable', 'SkuApi@updatestatusenable');
    $router->patch('establishments/users/{esteblishmentUserMapID}/skus/{id}/disable', 'SkuApi@updatestatusdisable');



    $router->put('establishments/users/{esteblishmentUserMapID}/skus/{id}', 'SkuApi@editsku');
    $router->put('establishments/users/{esteblishmentUserMapID}/skus/{id}/default', 'SkuApi@setdefaultskudetails');
    $router->put('establishments/users/{esteblishmentUserMapID}/skus/{id}/enable', 'SkuApi@updatestatusenable');
    $router->put('establishments/users/{esteblishmentUserMapID}/skus/{id}/disable', 'SkuApi@updatestatusdisable');
    $router->get('establishments/users/{esteblishmentUserMapID}/skus/{id}/disable', 'SkuApi@updatestatusdisable');



    $router->get('establishments/users/{esteblishmentUserMapID}/slots', 'DoctorsApi@slotdetails');
    $router->get('establishments/users/{esteblishmentUserMapID}/slot', 'DoctorsApi@slotdetailsV3');


    $router->get('establishments/hospital/{hospitalID}/users/{esteblishmentUserMapID}/slots', 'DoctorsApi@hospitalslotdetails');

    $router->get('establishments/users/{esteblishmentUserMapID}/dashboard', 'DoctorsApi@dashboard');
    $router->put('establishments/users/{esteblishmentusermapID}/token', 'DoctorsApi@updatetoken');
    $router->put('establishments/users/{esteblishmentusermapID}/update', 'DoctorsApi@updateprofile');
    $router->get('establishments/users/{esteblishmentusermapID}', 'DoctorsApi@getprofile');

    $router->get('establishments/users/{esteblishmentusermapID}/account', 'DoctorsApi@getaccount');
    $router->delete('establishments/users/{esteblishmentusermapID}/account', 'DoctorsApi@deleteaccount');
    $router->put('establishments/users/{esteblishmentusermapID}/account', 'DoctorsApi@updateaccount');

    $router->delete('establishments/users/{esteblishmentusermapID}', 'DoctorsApi@destroy');

    $router->get('videocall/{appointment_encrypted_id}/session/create', 'VideoCallApi@videocallsessioncreate');
    $router->get('videocall/{appointment_encrypted_id}/session/disconnect', 'VideoCallApi@videocallsessiondisconnect');
    $router->get('videocall/{appointment_encrypted_id}/signal', 'VideoCallApi@videocallsingal');
    $router->get('videocall/{appointment_encrypted_id}/test', 'VideoCallApi@videocalltest');
    $router->post('videocall/callbackUrl', 'VideoCallApi@callbackUrl');

    $router->get('patients/search/{key}:{value}', 'PatientApi@search');
    $router->get('patient/search/{esteblishmentusermapID}/{key}:{value}/{page}/{limit}', 'PatientApi@searchv2');

    $router->get('patientsoverall/search/{esteblishmentusermapID}/{key}:{value}', 'PatientApi@searchv3');


    $router->post('establishments/users/{esteblishmentusermapID}/patient', 'PatientApi@create');
    $router->get('establishments/users/{esteblishmentusermapID}/patient', 'PatientApi@patientlist');

    $router->get('establishments/users/{esteblishmentusermapID}/patients/{page}/{limit}', 'PatientApi@patientlistv2');

    $router->put('establishments/users/{esteblishmentusermapID}/updatepatient/{patientId}', 'PatientApi@updatepatientDetails');


    $router->post('establishments/users/{esteblishmentusermapID}/appointment/create', 'DoctorsApi@createAppointment');
    $router->post('establishments/hospital/{hospitalID}/users/{esteblishmentusermapID}/appointment/create', 'DoctorsApi@createhospitalappointment');
    $router->post('establishments/hospital/{hospitalID}/users/{esteblishmentusermapID}/oncall', 'AppointmentApi@createoncallappointment');
    $router->post('establishments/hospital/{hospitalID}/prescription/upload', 'AppointmentApi@prescriptionupload');
    $router->post('establishments/hospital/{hospitalID}/ambulance', 'AppointmentApi@createambulancebooking');
    $router->post('establishments/hospital/{hospitalID}/pathlab', 'AppointmentApi@createpathlabbooking');
    $router->post('establishments/hospital/{hospitalID}/nursing', 'AppointmentApi@createnursingbooking');

    $router->get('establishments/hospital/{hospitalID}/appointment', 'HospitalApi@gethospitalappointment');
    $router->get('establishments/hospital/{hospitalID}/nursing', 'HospitalApi@gethospitalnursingappointment');
    $router->get('establishments/hospital/{hospitalID}/pathlab', 'HospitalApi@gethospitalpathlabappointment');
    $router->get('establishments/hospital/{hospitalID}/prescription/upload', 'HospitalApi@gethospitalprescriptionupload');
    $router->get('establishments/hospital/{hospitalID}/ambulance', 'HospitalApi@gethospitalambulanceappointment');
    $router->get('establishments/hospital/{hospitalID}/doctors', 'HospitalApi@gethospitaldoctors');
    $router->get('establishments/hospital/{hospitalID}/patients', 'HospitalApi@gethospitalpatients');
    $router->get('establishments/hospital/{hospitalID}/oncall', 'HospitalApi@gethospitaloncallappointment');

    $router->get('account/ifsc/{ifsccode}', 'DoctorsApi@getbankdetails');
    $router->get('sync/drug', 'DrugApi@getdruglist');
    $router->post('establishments/users/{esteblishmentusermapID}/drug/search', 'DrugApi@drugfilter');



    $router->post('establishments/users/{esteblishmentusermapID}/drugs/search', 'DrugApi@drugfilterv2');
    $router->post('establishments/users/{esteblishmentusermapID}/update/drugsFlag', 'DrugApi@updateDrugsFlag');
    $router->post('establishments/users/{esteblishmentusermapID}/symptoms/search', 'PrescriptionApi@getSymptomsSearch');
    $router->post('establishments/users/{esteblishmentusermapID}/diagnosis/search', 'PrescriptionApi@getdiagnosisSearch');
    $router->post('establishments/users/{esteblishmentusermapID}/testRequested/search', 'PrescriptionApi@gettestrequestedsearch');
    $router->post('establishments/users/{esteblishmentusermapID}/advice/search', 'PrescriptionApi@getadviceserch');
    $router->post('establishments/users/{esteblishmentusermapID}/medicalhistory/search', 'PrescriptionApi@getadvicemedicalhistorysearch');

    $router->post('establishments/users/{esteblishmentusermapID}/lifestyle/search', 'PrescriptionApi@getLifestyleSearch');


    $router->post('establishments/users/{esteblishmentusermapID}/drug', 'DrugApi@adddrug');
    $router->put('establishments/users/drugupdate', 'DrugApi@updatedrugusermap');
    $router->delete('establishments/users/drugdelete/{id}', 'DrugApi@deletedrugusermap');




    $router->get('establishments/users/{esteblishmentusermapID}/drug/search/recent', 'DrugApi@recentsearch');
    $router->put('establishments/users/{esteblishmentusermapID}/drug/search/recent/{drugID}', 'DrugApi@recentsearchupdate');
    $router->get('drugs/{slug}', 'DrugApi@drugbyslug');
    $router->get('drug/filter', 'DrugApi@filter');
    $router->post('establishments/users/{esteblishmentusermapID}/addDoses', 'DrugApi@addDoses');
    $router->get('establishments/users/{esteblishmentusermapID}/getDoses', 'DrugApi@getDosesOfUserMap');

    $router->post('establishments/users/{esteblishmentusermapID}/addMedicine/timing', 'DrugApi@addMedicineTiming');




    $router->get('profile/statemedicalcouncil', 'MasterApi@StateMedicalCouncil');
    $router->get('profile/state', 'MasterApi@state');
    $router->get('profile/city/{stateID}', 'MasterApi@city');
    $router->get('prescription/sample', 'MasterApi@prescription_template');


    $router->get('establishments/users/{esteblishmentUserMapID}/specialization', 'SpecialityApi@specialitydetails');
    $router->put('establishments/users/{esteblishmentUserMapID}/specialization', 'SpecialityApi@updatespeciality');


    $router->put('establishments/users/{esteblishmentusermapID}/profile', 'UsersApi@updateprofile');
    $router->get('establishments/users/{esteblishmentusermapID}/profile', 'UsersApi@getprofile');
    $router->put('establishments/users/{esteblishmentusermapID}/prescription_config/{themeID}', 'UsersApi@setprescriptiontheme');
    $router->get('establishments/users/{esteblishmentusermapID}/prescription_config', 'UsersApi@getprescriptiontheme');

    $router->put('establishments/users/{esteblishmentusermapID}/profilesettings', 'UsersApi@updateprofilesettings');
    $router->get('establishments/users/{esteblishmentusermapID}/profilesettings', 'UsersApi@getprofilesettings');

    $router->delete('establishments/users/{esteblishmentusermapID}/profilesettings/experience/{id}', 'UsersApi@deleteexp');
    $router->delete('establishments/users/{esteblishmentusermapID}/profilesettings/awards/{id}', 'UsersApi@deleteawards');
    $router->delete('establishments/users/{esteblishmentusermapID}/profilesettings/education/{id}', 'UsersApi@deleteedu');

    $router->put('establishments/users/{esteblishmentusermapID}/medicalinfo', 'UsersApi@updatemedicalinfo');
    $router->get('establishments/users/{esteblishmentusermapID}/medicalinfo', 'UsersApi@getmedicalinfo');

    $router->put('establishments/users/{esteblishmentusermapID}/address', 'UsersApi@updateaddress');
    $router->get('establishments/users/{esteblishmentusermapID}/address', 'UsersApi@getaddress');

    $router->get('search/pincode/{pincode}', 'UsersApi@getaddressbypincode');

    $router->get('establishments/users/{esteblishmentusermapID}/transcation/paid', 'TranscationApi@paid');
    $router->get('establishments/users/{esteblishmentusermapID}/transcation/refund', 'TranscationApi@refund');
    $router->get('establishments/users/{esteblishmentusermapID}/transcation/withdraw', 'TranscationApi@withdraw');

    $router->get('establishments/users/{esteblishmentusermapID}/transcation/wallet/paid', 'TranscationApi@walletpaid');
    $router->post('establishments/users/{esteblishmentusermapID}/transcation/withdraw/request', 'TranscationApi@withdraw_request');

    $router->post('password/forgot-password', 'ForgotPasswordController@sendResetLinkResponse');
    $router->post('password/reset', 'ResetPasswordController@sendResetResponse');


    //vaccine
    $router->get('search/vaccine/doctors', 'DoctorsApi@searchdoctorforvaccine');
    $router->get('search/vaccine/city', 'DoctorsApi@searchcityforvaccine');

    // aggrement apis
    $router->post('establishments/users/{esteblishmentUserMapID}/aggrement', 'DoctorsApi@addaggrementFlag');
    $router->get('establishments/users/{esteblishmentUserMapID}/aggrement', 'DoctorsApi@getaggrementFlag');







    //auth and register apis
    $router->post('auth/loginforStaffAndDoctor', 'AuthApi@loginforStaffAndDoctor');




    $router->post('auth/public/register', 'RegisterApi@register');
    $router->post('auth/public/lead/register', 'RegisterApi@leadregister');
    $router->get('auth/public/lead/{mobileno}', 'RegisterApi@lead');
    $router->patch('auth/public/lead/{mobileno}/unsubscribe', 'RegisterApi@leadunsubscribe');
    $router->patch('auth/public/lead/{mobileno}/subscribe', 'RegisterApi@leadsubscribe');
    $router->put('auth/public/{esteblishmentusermapID}/profile', 'RegisterApi@updateprofile');
    $router->put('auth/public/{esteblishmentusermapID}/profilepicture', 'RegisterApi@updatepicture');
    $router->post('auth/public/{mobile}/verify', 'RegisterApi@verifymobile');

    $router->get('auth/public/generateOTP/{mobile}', 'AuthApi@generateOTP');
    $router->post('auth/public/confirmOTP', 'AuthApi@confirmOTP');
    $router->post('auth/public/login', 'AuthApi@login');
    $router->post('auth/public/staff/login', 'AuthApi@assistant_login');
    $router->post('auth/public/{esteblishmentusermapID}/changepassword', 'AuthApi@changepassword');
    $router->post('auth/public/verify-email', 'AuthApi@verifyemail');

    $router->delete('auth/public/{mobile}', 'AuthApi@deleteaccount');

    $router->post('/emr/patient/{loginID}/{profileID}/vital', 'EmrApi@setVital');
    $router->get('/emr/patient/{loginID}/{profileID}/vital', 'EmrApi@getVital');
    $router->put('/emr/patient/{loginID}/{profileID}/vital/{vitalID}', 'EmrApi@editVital');
    $router->delete('/emr/patient/{loginID}/{profileID}/vital/{vitalID}', 'EmrApi@deleteVital');
    $router->get('establishments/users/{esteblishmentusermapID}/vital', 'PrescriptionApi@getvitals');
    $router->post('establishments/users/{esteblishmentusermapID}/vital', 'PrescriptionApi@addvitals');
    $router->put('establishments/users/{esteblishmentusermapID}/vital/{vitalID}', 'PrescriptionApi@updatevitals');
    $router->delete('establishments/users/{esteblishmentusermapID}/vital/{vitalID}', 'PrescriptionApi@deletevitals');
    $router->get('vitals', 'PrescriptionApi@getvitalsAll');
    $router->get('establishments/users/{esteblishmentusermapID}/doses', 'PrescriptionApi@getdoses');
    $router->post('establishments/users/{esteblishmentusermapID}/patient/{patientID}/prescription/create', 'PrescriptionApi@createprecription');


    $router->post('/emr/patient/{loginID}/{profileID}/medicalrecord', 'EmrApi@setMedicalRecord');
    $router->get('/emr/patient/{loginID}/{profileID}/medicalrecord', 'EmrApi@getMedicalRecord');
    $router->put('/emr/patient/{loginID}/{profileID}/medicalrecord/{recordID}', 'EmrApi@editMedicalRecord');
    $router->delete('/emr/patient/{loginID}/{profileID}/medicalrecord/{recordID}', 'EmrApi@deleteMedicalRecord');

    // savePrescription
    $router->post('establishments/users/{esteblishmentusermapID}/prescription', 'PrescriptionApi@savePrescriptionV4');
    $router->post('establishments/users/{esteblishmentusermapID}/prescription/new', 'PrescriptionApi@savePrescriptionV5');
    $router->post('prescription/update', 'PrescriptionApi@updatePrescriptionV1');
    $router->post('savePrescriptionV6/{esteblishmentusermapID}', 'PrescriptionApi@savePrescriptionV6');


    $router->get('establishments/users/{esteblishmentusermapID}/prescription/{prescriptionID}', 'PrescriptionApi@getPrescription');
    $router->put('establishments/users/{esteblishmentusermapID}/prescription/{prescriptionID}', 'PrescriptionApi@updatePrescription');
    $router->get('establishments/users/{esteblishmentusermapID}/prescriptionbypatient/{patientID}', 'PrescriptionApi@getPrescriptionById');
    $router->get('establishments/users/{esteblishmentusermapID}/prescriptionbyappointment/{appointmentID}', 'PrescriptionApi@getPrescriptionByAppointmentID');
    $router->get('prescriptionbypatient/{patientID}', 'PrescriptionApi@getAllPrescription');
    $router->get('getsymptoms/{esteblishmentusermapID}', 'PrescriptionApi@getsymptoms');
    $router->get('getsymptoms', 'PrescriptionApi@getsymptoms');

    $router->post('establishments/users/{esteblishmentusermapID}/assistant/vital/add', 'PrescriptionApi@add_vital_assistant');

    $router->get('establishments/users/{esteblishmentusermapID}/assistant/vital/{appointment_id}', 'PrescriptionApi@fetch_vital_assistant');
    // $router->get('getsymptomss/{esteblishmentusermapID}', 'PrescriptionApi@getsymptomss');


    $router->get('establishments/users/{esteblishmentusermapID}/prescriptionbypatientId/{patientID}/{page}/{limit}', 'PrescriptionApi@getPrescriptionByPatientId');


    $router->get('generatepdf/{prescriptionID}', 'PrescriptionApi@createPdf');

    $router->post('establishments/users/{esteblishmentusermapID}/prescriptionTemplate', 'PrescriptionApi@savePrescriptionTemplate');
    $router->get('establishments/users/{esteblishmentusermapID}/prescriptionTemplateList', 'PrescriptionApi@getPrescriptionTemplateList');
    $router->get('establishments/users/{esteblishmentusermapID}/prescriptionTemplate/{prescriptionID}', 'PrescriptionApi@getPrescriptionTemplate');

    //    docexa_genie_apis
    $router->post('establishments/prescription/offlinesave/{usermapId}/{patientId}', 'PrescriptionApi@offlineSyncedDataSave');
    $router->get('establishments/prescription/offlinesave/{usermapId}/{patientId}', 'PrescriptionApi@getofflineSyncedDataSave');

    $router->post('strokes/save/{usermapId}', 'DocexaGenieApis@strokesSave');
    $router->get('getstrokes/save/{usermapId}/{patientId}', 'DocexaGenieApis@getSavedStrokes');


    $router->post('strokes/save/{usermapId}/new', 'DocexaGenieApis@strokesSaveNew');
    $router->get('strokes/{usermapId}/{patientId}/{pageId}', 'DocexaGenieApis@getNewStrokes');
    $router->post('/ocr', 'DocexaGenieApis@analyzeDocument');
    $router->post('addLanguage', 'DoctorsApi@addLanguage');




    // android
    $router->post('stroke/save', 'DocexaGenieApis@strokesSaveForAndroid');
    $router->get('getstroke/save/{usermapId}/{patientId}/{pageId}', 'DocexaGenieApis@getSavedStrokesfromAndroid');








    //   docexa_genie_apis



    $router->get('establishments/users/{esteblishmentusermapID}/bookingslots/{clinicID}', 'UsersApi@bookingslots');
    $router->put('establishments/users/{esteblishmentusermapID}/bookingslots/', 'UsersApi@updatebookingslots');
    $router->delete('establishments/users/{esteblishmentusermapID}/bookingslots/{id}', 'UsersApi@deletebookingslots');

    $router->get('establishments/users/{esteblishmentusermapID}/clinic', 'ClinicApi@getclinic');
    $router->put('establishments/users/{esteblishmentusermapID}/clinic/', 'ClinicApi@addclinic');
    $router->put('establishments/users/{esteblishmentusermapID}/clinic/{clinicID}', 'ClinicApi@updateclinic');
    $router->delete('establishments/users/{esteblishmentusermapID}/clinic/{clinicID}', 'ClinicApi@deleteclinic');

    $router->get('establishments/users/{esteblishmentusermapID}/task', 'UsersApi@gettask');

    $router->get('establishments/users/{esteblishmentusermapID}/permission/{roleID}', 'PermissionApi@getpermission');
    $router->post('establishments/users/{esteblishmentusermapID}/permission/{roleID}', 'PermissionApi@addpermission');
    $router->delete('establishments/users/{esteblishmentusermapID}/permission/{roleID}', 'PermissionApi@deletepermission');
    $router->put('establishments/users/{esteblishmentusermapID}/permission/{roleID}', 'PermissionApi@updatepermission');

    $router->get('establishments/users/{esteblishmentusermapID}/roles', 'PermissionApi@getroles');
    $router->post('establishments/users/{esteblishmentusermapID}/roles', 'PermissionApi@saveroles');
    $router->put('establishments/users/{esteblishmentusermapID}/roles/{roleID}', 'PermissionApi@updateroles');
    $router->delete('establishments/users/{esteblishmentusermapID}/roles/{roleID}', 'PermissionApi@deleteroles');


    $router->get('establishments/users/{esteblishmentusermapID}/staff/', 'StaffApi@getstaff');
    $router->put('establishments/users/{esteblishmentusermapID}/staff/', 'StaffApi@addstaff');
    $router->put('establishments/users/{esteblishmentusermapID}/staff/{staffID}', 'StaffApi@updatestaff');
    $router->delete('establishments/users/{esteblishmentusermapID}/staff/{staffID}', 'StaffApi@deletestaff');

    $router->get('establishments/users/{esteblishmentusermapID}/module', 'PermissionApi@getmodule');

    $router->get('admin/doctor', 'AdminApi@getdoctors');
    $router->put('admin/doctor/{medical_user_id}/active', 'AdminApi@updatedoctoractive');
    $router->put('admin/doctor/{medical_user_id}/inactive', 'AdminApi@updatedoctorinactive');

    $router->post('/emr/patient/{loginID}/{profileID}/share/vital', 'EmrApi@sharevital');
    $router->post('/emr/patient/{loginID}/{profileID}/share/medicalreport', 'EmrApi@sharemedicalreport');
    $router->get('/establishments/users/{esteblishmentusermapID}/medicalhistory/{appointment_id}', 'EmrApi@getsharinginfo');


    $router->get('hospital/{handle}', 'HospitalApi@hospitaldetails');
    $router->post('hospital/login', 'HospitalApi@hospitallogin');

    $router->get('/establishments/users/{esteblishmentusermapID}/treatment', 'TreatmentApi@treatmentlist');
    $router->get('/establishments/users/{esteblishmentusermapID}/treatment/{planID}', 'TreatmentApi@treatmentplan');
    $router->get('/establishments/users/{esteblishmentusermapID}/treatment/{planID}/details', 'TreatmentApi@treatmentrecord');
    $router->get('/establishments/users/{esteblishmentusermapID}/treatment/{planID}/invoices', 'TreatmentApi@treatmentinvoices');

    $router->post('/establishments/users/{esteblishmentusermapID}/certificate', 'CertificateApi@generate');
    $router->get('/establishments/users/{esteblishmentusermapID}/certificate', 'CertificateApi@list');
    $router->get('/establishments/users/{esteblishmentusermapID}/certificate/{certificateID}', 'CertificateApi@details');

    $router->get('sendmail', 'AppointmentApi@sendmail');

    /* for patient **/
    $router->get('patientExist/{patientId}', 'PatientApi@patientExist');

    /***************************** docexa emr api ************************************/
    $router->post('emr/users/{esteblishmentusermapID}/patient', 'DocEmrApi@createPatient');
    $router->post('emr/users/{esteblishmentusermapID}/listOfAppoinments', 'DocEmrApi@listappointment');

    /* diagnostic report */
    $router->post('emr/users/{esteblishmentusermapID}/patient/dignosticReport', 'DocEmrApi@createDiagnostic');
    $router->get('emr/users/patient/dignosticReport/{patientId}', 'DocEmrApi@diagnosticlist');

    /* OP consultation */
    $router->post('emr/users/{esteblishmentusermapID}/patient/opCosultation', 'DocEmrApi@createOpConsultation');
    $router->get('emr/users/patient/opCosultation/{patientId}', 'DocEmrApi@Opconsultationlist');

    /* Discharge summary */
    $router->post('emr/users/{esteblishmentusermapID}/patient/DischargeSummary', 'DocEmrApi@CreateDischargeSummary');
    $router->get('emr/users/patient/DischargeSummary/{patientId}', 'DocEmrApi@DischargeSummaryList');


    $router->post('send/mail', 'DocexaGenieApis@sendEmail');

    $router->get('withdraw/{usermapId}', 'DoctorsApi@getwithdrawDetails');


    $router->get('totalpatient/{esteblishmentusermapID}', 'PatientApi@getTotalCountOfPatient');
    $router->get('doctor/{esteblishmentusermapID}/patient/{mobileNumber}', 'DoctorsApi@getPatientByMobilenumber');
    $router->get('getpatientById/{patientid}', 'PatientApi@getPatientById');

    $router->post('upload/offlineSyncked/prescription/{esteblishmentusermapID}/{patientId}', 'PrescriptionApi@saveOfflineSynckedPrescription');
    $router->delete('delete/offlineSyncked/prescription/{prescriptionId}/{pageId}', 'PrescriptionApi@deleteOfflineSynckedPrescription');

    $router->post('add/bill', 'BillingApi@addBill');
    $router->put('edit/bill/{id}', 'BillingApi@EditBill');


    $router->get('retrive/bills/{usermap_id}/{clinic_id}', 'BillingApi@getBiilMaster');
    $router->get('retrive/bills/{usermap_id}/{clinic_id}/{page}/{limit}', 'BillingApi@getBiilMasterByPageLimit');
    $router->get('retrive/bill/patient/{id}', 'BillingApi@getBillById');

    // $router->get('search-bill-master/{usermap_id}/{clinic_id}/{key}:{value}/{page}/{limit}', 'BillingApi@billSearch');

    $router->get('search-bill-master', 'BillingApi@billSearchv2');
    $router->get('search-bill-master/overall', 'BillingApi@billSearchOverAll');


    $router->post('add/clinicTemplate', 'BillingApi@addclinicTemplate');
    $router->get('retrive/clinicTemplate/{usermap_id}/{clinic_id}', 'BillingApi@getclinicTemplate');
    $router->put('edit/clinicTemplate/{id}', 'BillingApi@editClinicTemplate');



    $router->post('createInvoice', 'BillingApi@createInvoice');
    $router->get('retrive/invoice/{usermap_id}/{clinic_id}', 'BillingApi@getInvoice');
    $router->get('retrive/lastinvoice/{usermap_id}/{clinic_id}', 'BillingApi@getLastInvoice');
    $router->get('retrive/patient/invoice/{usermap_id}/{clinic_id}/{patient_id}', 'BillingApi@getPatientInvoice');
    $router->get('retrive/patient/invoice/{usermap_id}/{clinic_id}/{patient_id}/{page}/{limit}', 'BillingApi@getPatientInvoiceByPagination');
    $router->get('retrive/patientInvoiceByAptId/{usermap_id}/{clinic_id}/{patient_id}/{apt_id}', 'BillingApi@getPatientInvoiceByaptId');
    $router->get('retrive/patient/invoices/{usermap_id}/{patient_id}/{page}/{limit}', 'BillingApi@getPatientIvoices');




    $router->get('dashboard/analysis/{esteblishmentUserMapID}', 'DoctorsApi@getDashboardAnalysis');
    $router->get('retrive/prescription/{usermapId}/{patientId}', 'PrescriptionApi@getPrescriptionSaved');

    // getPricedetails
    $router->post('retrive/pricedetails/{user_map_id}', 'BillingApi@getPricedetailsv3');
    $router->post('summary/transcation/{user_map_id}/{page}/{limit}', 'BillingApi@getSummaryOfTranscation');

    $router->put('update/billing/{id}', 'BillingApi@updateBillingDetails');

    $router->post('add/certificateTemplate/{user_map_id}', 'BillingApi@addCertificateTemplate');
    $router->get('retrive/certificateTemplate/{user_map_id}/{clinic_id}', 'BillingApi@getCertificateTemplate');

    $router->put('establishments/users/{esteblishmentusermapID}/prescriptionv1/{prescriptionID}', 'PrescriptionApi@updatePrescriptionv3');
    $router->get('precription/retrive/{usermapId}/{prescriptionId}', 'PrescriptionApi@getPrescriptionByPrescritpionId');
    $router->post('retrive/pricedetailsv3/{user_map_id}', 'BillingApi@getPricedetailsv3');
    $router->post('investigation', 'PrescriptionApi@addinvestigationOfPatient');
    $router->get('investigation/{usermapId}/{patientId}', 'PrescriptionApi@getinvestigationOfPatient');
    $router->get('investigation/{usermapId}', 'PrescriptionApi@getinvestigationMasterwrtousermap');
    $router->put('investigation/updatesequence', 'PrescriptionApi@updateinvestigationMasterwrtousermap');
    $router->put('patient/investigation/update', 'PrescriptionApi@updatepatientinvestigation');

    $router->post('add/investigation', 'PrescriptionApi@addInvestigationToUserMap');
    $router->delete('delete/investigation/{id}', 'PrescriptionApi@deleteInvestigationFromUserMap');

    $router->get('search/investigation', 'PrescriptionApi@getinvestigationMasterwrtousermapSearch');
    $router->post('uploadImageFromDoc', 'ApiController@uploadImageFromDoc');
    $router->get('getUploadedImages/{doctorId}/{patientNumber}', 'ApiController@getUploadedImages');
});

$router->group(['prefix' => 'api/v4/symptoms'], function () use ($router) {
    $router->get('{usermapId}', 'RxController@getSymptomsnew4');
    $router->post('{usermapId}', 'RxController@addSymptoms');
    $router->put('{usermapId}', 'PrescriptionApi@updateSymptoms');
});

$router->group(['prefix' => 'twilio'], function () use ($router) {

    $router->get('accesstoken', 'GenerateAccessTokenController@generate_token');
    $router->get('Room/{RoomNameOrSid}', 'TwilioVideoCallApi@getRoom');
    $router->post('Room/create', 'TwilioVideoCallApi@createRoom');
    $router->post('Room', 'TwilioVideoCallApi@completeRoom');
    $router->post('Rooms/{RoomNameOrSid}/Participants/{ParticipantIdentityOrSid}/', 'TwilioVideoCallApi@retrieveParticipant');
});
$router->group(['prefix' => 'api/agora'], function () use ($router) {

    $router->get('accesstoken', 'GenerateAccessTokenController@generate_token_agora');
    $router->get('generate_create_room', 'GenerateAccessTokenController@generate_create_room_agora');
    $router->get('generate_room_token/{uuid}', 'GenerateAccessTokenController@generate_room_token_agora');
    $router->get('generate_task_token/{uuid}', 'GenerateAccessTokenController@generate_task_token_agora');
    $router->get('token', 'GenerateAccessTokenController@token');
});

$router->group(['prefix' => 'v2'], function () use ($router) {

    $router->get('masterdata', 'DoctorsApi@masterdata');
    $router->post('fileupload', 'DoctorsApi@fileUpload');

    $router->get('doctors/{handle}', 'DoctorsApi@addPet');
    $router->post('doctors/registerprecheck', 'DoctorsApi@registerprecheck');
    $router->post('doctors/register', 'DoctorsApi@register');
    $router->post('doctors/update', 'DoctorsApi@doctorupdate');
    $router->post('doctors/updatehandle', 'DoctorsApi@updatehandle');
    $router->get('doctors/slotdetails/{esteblishmentUserMapID}', 'DoctorsApi@slotdetailsv2');
    $router->get('doctors/skudetails/{esteblishmentUserMapID}', 'DoctorsApi@skudetails');
    $router->post('doctors/getappointment', 'DoctorsApi@getappointmentv2');
    $router->post('doctors/scheduleappointment', 'DoctorsApi@scheduleAppointmentv2');
    $router->post('doctors/createappointment', 'DoctorsApi@createAppointmentv2');

    $router->get('auth/otp/{mobileno}', 'DoctorsApi@sendOtp');
    $router->post('auth/login', 'DoctorsApi@login');

    $router->post('appointment', 'DoctorsApi@listappointment');
    $router->get('appointment/{appointment_encrypted_id}', 'DoctorsApi@appointmentv2');
    $router->post('appointment/update', 'DoctorsApi@scheduleAppointmentv2');
    $router->post('appointment/create', 'DoctorsApi@createAppointmentv2');


    $router->get('payment/{appointment_encrypted_id}/charge', 'DoctorsApi@createpayment');
    $router->get('payment/{appointment_encrypted_id}/pay', 'DoctorsApi@payv2');
    $router->post('payment/{appointment_encrypted_id}/fail', 'DoctorsApi@payfailv2');
    $router->post('payment/{appointment_encrypted_id}/success', 'DoctorsApi@paysuccessv2');

    $router->get('establishments/users/{esteblishmentUserMapID}/skus', 'DoctorsApi@skudetails');
    $router->get('establishments/users/{esteblishmentUserMapID}/slots', 'DoctorsApi@slotdetailsv2');
    $router->get('establishments/users/{esteblishmentUserMapID}/dashboard', 'DoctorsApi@dashboard');
    $router->delete('establishments/users/{esteblishmentusermapID}', 'DoctorsApi@destroy');

    $router->get('videocall/{appointment_encrypted_id}/session/create', 'VideoCallApi@videocallsessioncreate');
    $router->get('videocall/{appointment_encrypted_id}/session/disconnect', 'VideoCallApi@videocallsessiondisconnect');
    $router->get('videocall/{appointment_encrypted_id}/signal', 'VideoCallApi@videocallsingal');
    $router->get('videocall/{appointment_encrypted_id}/test', 'VideoCallApi@videocalltest');
    $router->post('videocall/callbackUrl', 'VideoCallApi@callbackUrl');
});

$router->get('/fire', function (Request $request) {
    $id = $request->input('id');
    event(new \App\Events\ExampleEvent($id));
    return response()->json(['status' => 'success'], 200);
});
$router->group(['prefix' => 'api/v4'], function () use ($router): void {
    $router->post('auth/login', 'DoctorsApi@login');

    //Patient
    $router->get('totalpatient/{esteblishmentusermapID}', 'PatientApi@getTotalCountOfPatient');
    $router->get('establishments/users/patient/{esteblishmentusermapID}', 'PatientApi@patientlistV4');
    $router->get('establishments/users/patients/{esteblishmentusermapID}/{page}/{limit}', 'PatientApi@patientlistv2');
    $router->get('patients/search/{key}:{value}', 'PatientApi@search');
    $router->get('patientsoverall/search/{esteblishmentusermapID}/{key}:{value}', 'PatientApi@searchv3');
    $router->post('emr/users/{esteblishmentusermapID}/patient', 'DocEmrApi@createPatient');
    $router->put('establishments/users/{esteblishmentusermapID}/updatepatient/{patientId}', 'PatientApi@updatepatientDetailsv4');

    $router->get('patient/search/{esteblishmentusermapID}/{key}:{value}/{page}/{limit}', 'PatientApi@searchv2');
    $router->post('establishments/users/{esteblishmentusermapID}/patient', 'PatientApi@create');

    $router->post('record/upload/{id}/{patientId}', 'DoctorsApi@UploadRecordMicroSoftAzure');
    $router->post('record/uploadd/{id}/{patientId}', 'DoctorsApi@UploadRecordMicroSoftAzure');

    $router->get('record/upload/{id}/{patientId}', 'DoctorsApi@getrecord');
    $router->delete('patient/delete/{patientId}', 'PatientApi@deletePatient');
});

$router->group(['prefix' => 'api/v3/analytics'], function () use ($router): void {
    $router->post('patients/count', 'AnalyticsApi@getPatientAnalyticsCount');
    $router->post('revenue/bymodeofbilling', 'AnalyticsApi@getRevenueByModeOfBilling');
    $router->post('revenue/bytypeOfBilling', 'AnalyticsApi@getRevenueByTypeOfBilling');
    $router->post('symptoms', 'AnalyticsApi@getTopSymptoms');
    $router->post('diagnosis', 'AnalyticsApi@getTopdiagnosis');
    $router->post('medicines', 'AnalyticsApi@getTopMedicines');
    $router->post('investigation', 'AnalyticsApi@getTopInvestigation');
    $router->post('vaccinationbybrand', 'AnalyticsApi@getTopVaccinationbyBrand');
    $router->post('vaccinationByGroup', 'AnalyticsApi@getTopVaccinationbyGroup');
});


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('consumables', 'ConsumableController@index');
    $router->post('consumables', 'ConsumableController@store');
    $router->get('consumables/{id}', 'ConsumableController@show');
    $router->put('consumables/{id}', 'ConsumableController@update');
    $router->delete('consumables/{id}', 'ConsumableController@destroy');

    $router->get('service-consumables', 'ServiceConsumableController@index');
    $router->post('service-consumables', 'ServiceConsumableController@store');
    $router->get('service-consumables/{id}', 'ServiceConsumableController@show');
    $router->put('service-consumables/{id}', 'ServiceConsumableController@update');
    $router->delete('service-consumables/{id}', 'ServiceConsumableController@destroy');
    $router->post('store-service-consumables', 'ServiceConsumableController@storeServiceConsumable');
    $router->put('service-consumables/{id}/update', 'ServiceConsumableController@updateServiceConsumable');

    $router->get('consumable-usage', 'ConsumableUsageLogController@index');
    $router->post('consumable-usage', 'ConsumableUsageLogController@store');
    $router->get('consumable-usage/{id}', 'ConsumableUsageLogController@show');
    $router->put('consumable-usage/{id}', 'ConsumableUsageLogController@update');
    $router->delete('consumable-usage/{id}', 'ConsumableUsageLogController@destroy');



    $router->get('/service-categories', 'ServiceCategoryController@index');
    $router->post('/service-categories', 'ServiceCategoryController@store');
});


$router->post('/webhook', 'ApiController@handleJsonInput');
$router->get('/webhook', 'ApiController@getAllWebhookInputs');
$router->get('/webhook/{id}', 'ApiController@getWebhookInputById');
$router->put('/webhook/{id}', 'ApiController@updateWebhookInput');
$router->delete('/webhook/{id}', 'ApiController@deleteWebhookInput');
$router->post('/getSlots', 'ApiController@getAvailableSlots');
$router->post('/bookAppointment', 'ApiController@bookAppointment');
$router->post('/getAnalysis', 'ApiController@getAnalysis');
$router->post('/getAfterImages', 'ApiController@getAfterImages');
$router->post('/sendImage', 'ApiController@sendDocumentToWhatsApp');
$router->get('/checkPatient/{patientNo}/{doctorNumber}', 'ApiController@checkPatient');
$router->get('/', function () use ($router) {
    dispatch(new \App\Jobs\AfterImageStore(['mediaId' => '9850612045032553']));

    return "hii there";
});

$router->post('/checkAfterImage', 'SkinAnalysisController@afterImageAnalysis');
$router->post('/image', 'SkinAnalysisController@analyzeSkin');
// $router->post('/image', [SkinAnalysisController::class, 'analyzeSkin']);
$router->post('/chatbot', 'SkinAnalysisController@chatbot');
$router->post('/uploadpdf', 'ApiController@uploadPdf');

$router->get('services', 'ServiceMasterController@index');
$router->get('servicesPackages/{doctorId}/{patientId}', 'ServiceMasterController@getServiceAndPackages');
$router->post('services', 'ServiceMasterController@store');
$router->get('services/{id}', 'ServiceMasterController@show');
$router->put('services/{id}', 'ServiceMasterController@update');
$router->delete('services/{id}', 'ServiceMasterController@destroy');

$router->get('groups', 'ServiceGroupController@index');
$router->post('groups', 'ServiceGroupController@store');
$router->get('groups/{id}', 'ServiceGroupController@show');
$router->put('groups/{id}', 'ServiceGroupController@update');
$router->delete('groups/{id}', 'ServiceGroupController@destroy');
