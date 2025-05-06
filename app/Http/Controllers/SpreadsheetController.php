<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class SpreadsheetController extends Controller

{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        define('STDIN', fopen("php://stdin", "r"));

        //
    }
    public function oauth2callback()
    {
    }
    public function getClient()
    {
        $client = new Google_Client();
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/v3/oauth2callback');
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = storage_path('logs/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                echo '<script>window.open("' . $authUrl . '","_blank")</script>';
                print 'Enter verification code: ';
                $authCode = trim("4/0AY0e-g5eEq7Yf0iwFHBm3GvJEaXHHj_EbUw5wgFgxH4Mq2KRoNp3ikFbDM4k9UpN4UeVDw");

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0777, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
    public function masterdata($apptID = 'NA',$location = 'NA', $latlong = 'NA', $name = 'NA', $mobile_no = 'NA', $created_date, $amount = 0)
    {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        if($_ENV['ENVORNMENT'] == 'prod'){
        $spreadsheetId = '1WvnMh8UIfjYHfVdNKsCIy7rlhzxMhPXAB7KO1U-7HPU';
        $range = "Sheet1";
        }else{
            $spreadsheetId = '1tIQQz3GAnACdp7D_PBBF7hSJY9cG7xTRCtn4S_GcpbY';
            $range = "test";
        }
        $valueRange = new Google_Service_Sheets_ValueRange();
        $valueRange->setValues(["values" => [$apptID,$location, $latlong, $name, $mobile_no, $created_date, $amount, 'Not Paid']]);
        $conf = ["valueInputOption" => "RAW"];
        $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
        /*
$range = 'Sheet1';
$response = $service->spreadsheets_values->put($spreadsheetId, $range);
$values = $response->getValues();

if (empty($values)) {
    print "No data found.\n";
} else {
    var_dump($values);

}
*/
    }
    public function updatedata($apptID)
    {
        $client = $this->getClient();
        $service = new Google_Service_Sheets($client);

        // Prints the names and majors of students in a sample spreadsheet:
        // https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
        if($_ENV['ENVORNMENT'] == 'prod'){
            $spreadsheetId = '1WvnMh8UIfjYHfVdNKsCIy7rlhzxMhPXAB7KO1U-7HPU';
            $cell_range = "Sheet1";
            }else{
                $spreadsheetId = '1tIQQz3GAnACdp7D_PBBF7hSJY9cG7xTRCtn4S_GcpbY';
                $cell_range = "test";
            }
        $getData = $service->spreadsheets_values->get($spreadsheetId, $cell_range);
        $values_r = $getData->getValues();  
        if (empty($values_r)) {
           // print "None Found.\n";
        } else {
            //print "Data found\n";
            $range_index = '1';
            foreach ($values_r as $row) {
                // Show the results in array 
               
                // Match com id do banco de dados
                if ($row[0] === $apptID) {
                   // echo "ID found\n";
                  //  echo "$row[0]\n";
                   // echo "Cell ID G${range_index}\n";
                    $cell_id = "H${range_index}";
                    // in $cell_range set the effective range to change
                    // $cell_range = "A${range_index}:CM${range_index}";
                    break;
                }
                $range_index++;
            }
        }
        
        $values = [["Paid"]];
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
            ]);
            
             $append_sheet = $service->spreadsheets_values->update($spreadsheetId, $cell_range."!".$cell_id, $body,['valueInputOption' => 'RAW']);
            
    }
}
