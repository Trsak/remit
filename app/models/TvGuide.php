<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TvGuide extends \Kdyby\Doctrine\Entities\BaseEntity
{
    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    protected $channel;

    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    protected $start;

    /**
     * @ORM\Column(type="string")
     */
    protected $stop;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

}