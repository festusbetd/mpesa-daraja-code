<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MpesaController extends Controller
{
    /**
     *Generation of password for managing Stk push
     **/
    public function lipaNaMpesaPassword()
    {
        $timestamp = Carbon::rawParse('now')->format('YmdHms');
        $passKey = config('app.pass_key');
        $businessShortCode = config('app.short_Code');
        $mpesaPassword = base64_encode($businessShortCode . $passKey . $timestamp);

        return $mpesaPassword;

    }

    public function generateAccessToken()
    {
        $consumer_key = config('app.consumer_key');
        $consumer_secret = config('app.consumer_secret');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $url = config('app.saf_access_token_url');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials, "Content-Type:application/json"));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $access_token = json_decode($curl_response);
        curl_close($curl);

        return $access_token->access_token;

    }

    public function stkPush(Request $request)
    {

        $consumer_key = config('app.consumer_key');
        $consumer_secret = config('app.consumer_secret');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);

        $phone = config('app.phone'); //0722.... Nb: temporarily set in the .env file
        $formatedPhone = substr($phone, 1); //722....
        $code = "254";
        $phoneNumber ='254720651492';

        $url = config('app.stk_url');
        $callback_url = config('app.callback_url');
        $shortCode = config('app.short_Code');

        $curl_post_data = [
            'BusinessShortCode' => $shortCode,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => '6',
            'PartyA' => $phoneNumber,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callback_url,
            'AccountReference' => "Organic Input",
            'TransactionDesc' => "lipa Na M-PESA",
        ];

        $data_string = json_encode($curl_post_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $ch = curl_init();
        $auth_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . $credentials
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($ch);
        $access_token = json_decode($curl_response)->access_token;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization:Bearer ' . $this->generateAccessToken()
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $curl_res = curl_exec($ch);
     

        if ($curl_res = curl_exec($ch)) {

            return $curl_res;
        } else {
            return "STK push failed!";
        }

    }

    public function MpesaApiResponse(Request $request)
    {
        $response =file_get_contents("php://input");
       // $response = $request->getContent();
       // $response = $request->all();
    // $response ='{
    //     "Body": 
    //     {
    //         "stkCallback": 
    //         {
    //             "MerchantRequestID": "21605-295434-4",
    //             "CheckoutRequestID": "ws_CO_04112017184930742",
    //             "ResultCode": 0,
    //             "ResultDesc": "The service request is processed successfully.",
    //             "CallbackMetadata": 
    //             {
    //                 "Item": 
    //                 [
    //                     {
    //                         "Name": "Amount",
    //                         "Value": 1
    //                     },
    //                     {
    //                         "Name": "MpesaReceiptNumber",
    //                         "Value": "LK451H35OP"
    //                     },
    //                     {
    //                         "Name": "Balance"
    //                     },
    //                     {
    //                         "Name": "TransactionDate",
    //                         "Value": 20171104184944
    //                     },
    //                     {
    //                         "Name": "PhoneNumber",
    //                         "Value": 254727894083
    //                     }
    //                 ]
    //             }
    //         }
    //     }
    // }';

 // A cancelled request
//  $response='{

//     "Body":{
//       "stkCallback":{
//         "MerchantRequestID":"8555-67195-1",
//         "CheckoutRequestID":"ws_CO_27072017151044001",
//         "ResultCode":1032,
//         "ResultDesc":"[STK_CB - ]Request cancelled by user"
//       }
//     }
//   }';

   $object = json_decode($response, true);
  // $object = $response;
   $stkCallback= $object['Body']['stkCallback'];
  
    $resultCode =$stkCallback['ResultCode'];   
    $resultDesc = $stkCallback['ResultDesc'];
    $merchantRequestID = $stkCallback['MerchantRequestID'];
    $checkoutRequestID = $stkCallback['CheckoutRequestID'];

    //return $resultCode;

   if($resultCode==0){
    
    $CallbackMetadata=$stkCallback['CallbackMetadata']['Item'];
    $amount = $CallbackMetadata[0]['Value'];
    $mpesaReceiptNumber = $CallbackMetadata[1]['Value'];
    $transactionDate =$CallbackMetadata[3]['Value'];
    $phoneNumber = $CallbackMetadata[4]['Value'];

        $trn = new MpesaTransaction([
          
            'TransactionType' => '',
            'TransID' =>  $checkoutRequestID,
            'TransTime' => $transactionDate ,
            'TransAmount' =>$amount,
            'BusinessShortCode' =>config('app.short_Code') ,
            'BillRefNumber' => $mpesaReceiptNumber,
            'InvoiceNumber' =>'' ,
            'resultCode' =>$resultCode ,
            'resultDesc' =>$resultDesc ,
            'OrgAccountBalance' =>'' ,
            'ThirdPartyTransID' => $merchantRequestID,
            'MSISDN' => $phoneNumber,
            'FirstName' => '',
            'MiddleName' =>'',
            'LastName' => '',
        ]);

        $trn->save();

        return response()->json([
            "Success" => "Mpesa transaction has been added",
        ], 201);
   }
   else{
    $trn = new MpesaTransaction([
        'TransactionType' => '',
        'TransID' =>  $checkoutRequestID,
        'TransTime' => '' ,
        'TransAmount' =>'',
        'BusinessShortCode' =>config('app.short_Code') ,
        'BillRefNumber' => '',
        'InvoiceNumber' =>'' ,
        'resultCode' =>$resultCode ,
        'resultDesc' =>$resultDesc ,
        'OrgAccountBalance' =>'' ,
        'ThirdPartyTransID' => $merchantRequestID,
        'MSISDN' =>'',
        'FirstName' => '',
        'MiddleName' =>'',
        'LastName' => '',
    ]);

    $trn->save();

    return response()->json([
        "Success" => "Mpesa transaction has been added",
    ], 201);
   }
   
  
    }

    public function confirmation(Request $request)
    {
        // Compare the codes here
        // If the codes are similar, validate the pay
        // If the transactions are not equal, communicate the message

        $transId = $request->TransactionId;
        $trxn = MpesaTransaction::where('TransID', $transId);

        if ($transId) {
            # code...
        }
    }
}
