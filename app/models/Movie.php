<?php
namespace App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Movie extends \Kdyby\Doctrine\Entities\BaseEntity
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
    protected $year;

    /**
     * @ORM\Column(type="string")
     */
    protected $posterUrl;

    /**
     * @ORM\OneToMany(targetEntity="MovieNames", mappedBy="movie")
     */
    private $names;

    /**
     * @ORM\ManyToMany(targetEntity="MovieGenres")
     * @ORM\JoinTable(name="movie_has_genres",
     *      joinColumns={@ORM\JoinColumn(name="movie_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="genre_id", referencedColumnName="id")}
     *      )
     */
    private $genres;

    /**
     * @ORM\ManyToMany(targetEntity="MovieCountry")
     * @ORM\JoinTable(name="movie_has_countries",
     *      joinColumns={@ORM\JoinColumn(name="movie_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="country_id", referencedColumnName="id")}
     *      )
     */
    private $countries;

    /**
     * @ORM\Column(type="integer")
     */
    protected $rating;

    /**
     * @ORM\Column(type="string")
     */
    protected $plot;

    /**
     * @ORM\Column(type="string")
     */
    protected $contentRating;

    /**
     * @ORM\Column(type="string")
     */
    protected $runtime;

    /**
     * @ORM\Column(type="string")
     */
    protected $csfdUrl;
}