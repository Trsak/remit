<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone = 0;

    /**
     * @ORM\Column(type="string")
     */
    protected $facebookId = 0;

    /**
     * @ORM\Column(type="string")
     */
    protected $facebookToken = 0;

}