<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MovieNames extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $movie;

    /**
     * @ORM\Column(type="string")
     */
    protected $lang;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

}