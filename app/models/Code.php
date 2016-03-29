<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Code extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $code;

    /**
     * @ORM\Column(type="string")
     */
    protected $data;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $generatedDate;


    public function __construct()
    {
        $this->generatedDate = new \DateTime();
    }
}