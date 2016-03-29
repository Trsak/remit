<?php
namespace Remit;

class Codes
{
    public static function randomCode($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $code = array();
        $alphaLength = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $code[] = $chars[$n];
        }

        return implode($code);
    }
}