<?php

namespace App\Libraries;

/**
 * Class Checkout
 * @package App\Library
 */
class SmartPhone
{
    public static function sendPhoneVerificationMessage($to_number, $user_name, $otp_code, $api_request_id)
    {
        try {
            $headers = [
                "X-Auth-smrtPhone" => config("constants.SMART_PHONE_API_KEY"),
                // "Content-Type" => "application/json",
                // "Accept" => "application/json"
            ];
            $url = config("constants.SMART_PHONE_URL");
            // $message = "Dear ' . $user_name . ',

            // Thank you for choosing our service. Your phone number verification code is ' . $otp_code . '. Please use this code to verify your phone number.

            // If you did not request this verification, please ignore this message.

            // Thank you.

            // Best regards,
            // PPC Lead to Deals";
            $message = $user_name . '! Your OTP code is ' . $otp_code;
            $payload = [
                'from' => config("constants.FROM_NUMBER"),
                'to' => $to_number,
                'message' => $message
            ];
            // dump($headers, ' headers');
            // dump($url, ' url');
            // dump($message, ' message');
            // dd($payload, ' payload');
            // $response = curlRequest(true, $payload, $url, $headers, false);
            // dd($response);
            // // dd($response);
            // saveThirdPartyLogs($payload, $response, 'SmartPhone', $api_request_id, $response['status_code']);
            // if ($response['status_code'] == 200) {
            //     return true;
            // }
            // return false;
            $response = httpRequest('post', $payload, $url, $headers, false);
            // return json_decode($response);
            return $response;
            saveThirdPartyLogs($payload, $response, 'SmartPhone', $api_request_id, $response->status());
            if ($response->successful()) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), 500);
        }
    }
}
