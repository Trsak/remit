<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MovieHasGenres extends \Kdyby\Doctrine\Entities\BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $movieId;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $genreId;
}