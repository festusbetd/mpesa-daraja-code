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
       // $phoneNumber = $code . $formatedPhone; //254722....
        $phoneNumber ='254722229862';

        $url = config('app.stk_url');
        $callback_url = config('app.callback_url');
        $shortCode = config('app.short_Code');

        $curl_post_data = [
            'BusinessShortCode' => $shortCode,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => '1',
            'PartyA' => $phoneNumber,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $callback_url,
            'AccountReference' => "Organic Input",
            'TransactionDesc' => "lipa Na M-PESA",
        ];

        $data_string = json_encode($curl_post_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

       // return $data_string;

        $ch = curl_init();

        // curl_setopt($curl, CURLOPT_URL, $url);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_POST, true);
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
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
    //    $response = json_decode($request->getContent());
    $callbackData='{
        "Body": 
        {
            "stkCallback": 
            {
                "MerchantRequestID": "21605-295434-4",
                "CheckoutRequestID": "ws_CO_04112017184930742",
                "ResultCode": 0,
                "ResultDesc": "The service request is processed successfully.",
                "CallbackMetadata": 
                {
                    "Item": 
                    [
                        {
                            "Name": "Amount",
                            "Value": 1
                        },
                        {
                            "Name": "MpesaReceiptNumber",
                            "Value": "LK451H35OP"
                        },
                        {
                            "Name": "Balance"
                        },
                        {
                            "Name": "TransactionDate",
                            "Value": 20171104184944
                        },
                        {
                            "Name": "PhoneNumber",
                            "Value": 254727894083
                        }
                    ]
                }
            }
        }
    }';
    
   // $callbackData = json_decode($request->getContent());
  // $callbackData = json_decode($callback);
   return $callbackData->body;
    //$callbackData = $request->all();
    //$callbackData = json_encode($callbackData);
   // $callbackData = json_decode($callbackData);

   
   $resultCode = 
    $resultCode = $callbackData
        ->Body
        ->stkCallback->ResultCode;
        
    $resultDesc = $callbackData
        ->Body
        ->stkCallback->ResultDesc;
    $merchantRequestID = $callbackData
        ->Body
        ->stkCallback->MerchantRequestID;
    $checkoutRequestID = $callbackData
        ->Body
        ->stkCallback->CheckoutRequestID;

    $amount = $callbackData
        ->Body
        ->stkCallback
        ->CallbackMetadata
        ->Item[0]->Value;
    $mpesaReceiptNumber = $callbackData
        ->Body
        ->stkCallback
        ->CallbackMetadata
        ->Item[1]->Value;
    $transactionDate = $callbackData
        ->Body
        ->stkCallback
        ->CallbackMetadata
        ->Item[3]->Value;
    $phoneNumber = $callbackData
        ->Body
        ->stkCallback
        ->CallbackMetadata
        ->Item[4]->Value;

        // $trn = new MpesaTransaction([
          
        //     'TransactionType' => $response->TransactionType,
        //     'TransID' => $response->TransID,
        //     'TransTime' => $response->TransTime,
        //     'TransAmount' => $response->TransAmount,
        //     'BusinessShortCode' => $response->BusinessShortCode,
        //     'BillRefNumber' => $response->BillRefNumber,
        //     'InvoiceNumber' => $response->InvoiceNumber,
        //     'OrgAccountBalance' => $response->OrgAccountBalance,
        //     'ThirdPartyTransID' => $response->ThirdPartyTransID,
        //     'MSISDN' => $response->MSISDN,
        //     'FirstName' => $response->FirstName,
        //     'MiddleName' => $response->MiddleName,
        //     'LastName' => $response->LastName,
        // ]);

        DB::table('mpesa_transactions')->insert(
            ['TransactionType' => '$response->TransactionType',
             'TransID' => '$response->TransID', 
            'TransTime' =>  '$response->TransTime',
             'TransAmount' => '$response->TransAmount', 
             'BusinessShortCode' => '$response->BusinessShortCode', 
             'BillRefNumber' => '$response->BillRefNumber', 
             'InvoiceNumber' => '$response->InvoiceNumber', 
             'OrgAccountBalance	' =>  '$response->OrgAccountBalance',
             'ThirdPartyTransID' => '$response->ThirdPartyTransID',
             'MSISDN' => '$response->MSISDN',
             'FirstName' =>  '$response->FirstName',
             'MiddleName' =>' $response->MiddleName',
             'LastName' =>  '$response->MiddleName',
             'response' => '$response->LastName',
            
            ]);


        // $trn->save();

        // return response()->json([
        //     "Success" => "Mpesa transaction has been added",
        // ], 201);
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
