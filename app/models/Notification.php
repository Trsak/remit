<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Notification extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(type="text")
     */
    protected $data;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @ORM\Column(type="integer")
     */
    protected $email;

    /**
     * @ORM\Column(type="integer")
     */
    protected $facebook;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sms;

    /**
     * @ORM\Column(type="integer")
     */
    protected $done = 0;
}