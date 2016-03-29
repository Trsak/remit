<?php
namespace Remit;

class Sms
{
    public static function send($message, $to)
    {
        $url = 'http://www.poslatsms.cz/Send';
        $fields_string = "";
        $fields = array(
            'accountType' => urlencode(""),
            'timestamp' => urlencode(time()),
            'sendingProfile1' => urlencode(12),
            'sendingProfile2' => urlencode(20),
            'sendingProfile3' => urlencode(31),
            'textsms' => urlencode($message),
            'cislo-prijemce' => urlencode($to),
            'cislo-odesilatele' => urlencode(""),
            'odeslat' => urlencode("Odeslat SMS")
        );

        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        $result = curl_exec($ch);

        curl_close($ch);
    }
}