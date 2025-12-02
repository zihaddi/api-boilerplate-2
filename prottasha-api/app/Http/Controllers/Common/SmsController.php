<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;

use Auth;

use  Aws\Laravel\AwsFacade as AWS;

class SmsController
{
    public static function sendTo($data)
    {
        $url = config('services.sms.url');
        $url = $url;
        $data = array(
            'api_key' => config('services.sms.api_key'),
            'senderid' => config('services.sms.senderid'),
            'type' => 'text',
            'number' => $data['number'],
            'message' => $data['msg']
        );
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        $data = json_decode($smsresult, true);
        $sendstatus = $data['response_code'];
        return $sendstatus;
    }

    public static function sendToAws($data)
    {
        $sms = AWS::createClient('sns');
        $sms->publish([
            'Message' => $data['msg'],
            'PhoneNumber' => '+' . $data['number'],
            'MessageAttributes' => [
                'AWS.SNS.SMS.SMSType'  => [
                    'DataType'    => 'String',
                    'StringValue' => 'Transactional',
                ]
            ],
        ]);
        return  $sendstatus = 1;
    }

    public static function smsStatus($code = '1000')
    {
        $arr = [
            '1000' => 'Invalid user or Password',
            '1002' => 'Empty Number',
            '1003' => 'Invalid message or empty message',
            '1004' => 'Invalid number',
            '1005' => 'All Number is Invalid',
            '1006' => 'insufficient Balance',
            '1009' => 'Inactive Account',
            '1010' => 'Max number limit exceeded',
            '1101' => 'Success'
        ];

        return $arr[$code];
    }
}
