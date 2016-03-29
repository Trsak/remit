<?php
namespace Remit;

use App\Code;

class Codes
{
    public $code;

    public function __construct($user, $type, $data, \Kdyby\Doctrine\EntityManager $EntityManager, $length = 8)
    {
        $this->code = $this->randomCode($length);
        $this->EntityManager = $EntityManager;

        $code = new Code();
        $code->user = $user;
        $code->code = $this->code;
        $code->type = $type;
        $code->data = $data;

        $this->EntityManager->persist($code);
        $this->EntityManager->flush();
    }

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