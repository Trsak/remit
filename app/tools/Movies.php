<?php
namespace Remit;

use App\Movie;

class Movies
{
    public $csfdUrl = "http://csfdapi.cz/movie/";

    public function __construct($id, \Kdyby\Doctrine\EntityManager $EntityManager)
    {
        $this->EntityManager = $EntityManager;

        $movieData = json_decode(file_get_contents($this->csfdUrl . $id));

        $movie = new Movie();
        $movie->year = (isset($movieData->year)  ? $movieData->year : 0);
        $movie->posterUrl = (isset($movieData->poster_url)  ? $movieData->poster_url : '');
        $movie->rating = (isset($movieData->rating)  ? $movieData->rating : 0);
        $movie->plot = (isset($movieData->plot)  ? $movieData->plot : '');
        $movie->contentRating = (isset($movieData->content_rating)  ? $movieData->content_rating : '');
        $movie->runtime = (isset($movieData->runtime)  ? $movieData->runtime : '');
        $movie->csfdUrl = (isset($movieData->csfd_url)  ? $movieData->csfd_url : '');


        $this->EntityManager->persist($movie);
        $this->EntityManager->flush();
    }
}
