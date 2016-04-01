<?php
namespace Remit;

use App\Movie;
use App\MovieCountry;
use App\MovieGenres;
use App\MovieHasCountries;
use App\MovieHasGenres;
use App\MovieNames;

class Movies
{
    public $csfdUrl = "http://csfdapi.cz/";

    public function __construct($id, \Kdyby\Doctrine\EntityManager $EntityManager)
    {
        $this->EntityManager = $EntityManager;

        $movieData = json_decode(file_get_contents($this->csfdUrl . 'movie/' . $id));

        $movie = new Movie();
        $movie->year = (isset($movieData->year) ? $movieData->year : 0);
        $movie->posterUrl = (isset($movieData->poster_url) ? $movieData->poster_url : '');
        $movie->rating = (isset($movieData->rating) ? $movieData->rating : 0);
        $movie->plot = (isset($movieData->plot) ? $movieData->plot : '');
        $movie->contentRating = (isset($movieData->content_rating) ? $movieData->content_rating : '');
        $movie->runtime = (isset($movieData->runtime) ? $movieData->runtime : '');
        $movie->csfdUrl = (isset($movieData->csfd_url) ? $movieData->csfd_url : '');
        $movie->apiUrl = (isset($movieData->api_url) ? $movieData->api_url : '');

        $this->EntityManager->persist($movie);
        $this->EntityManager->flush();

        if (isset($movieData->names)) {
            foreach ($movieData->names as $key => $name) {
                $names = new MovieNames();
                $names->movie = $movie->getId();
                $names->lang = $key;
                $names->name = $name;
                $this->EntityManager->persist($names);
            }
        }

        if (isset($movieData->countries)) {
            foreach ($movieData->countries as $country) {
                $countryDB = $this->EntityManager->getRepository(MovieCountry::class)->findOneBy(array('country' => $country));

                if (is_null($countryDB)) {
                    $countryDB = new MovieCountry();
                    $countryDB->country = $country;
                    $this->EntityManager->persist($countryDB);
                    $this->EntityManager->flush();

                    $countryDB = $this->EntityManager->getRepository(MovieCountry::class)->findOneBy(array('country' => $country));
                }

                $countriesDB = new MovieHasCountries();
                $countriesDB->movieId = $movie->getId();
                $countriesDB->countryId = $countryDB->getId();
                $this->EntityManager->persist($countriesDB);
            }
        }

        if (isset($movieData->genres)) {
            foreach ($movieData->genres as $genre) {
                $genreDB = $this->EntityManager->getRepository(MovieGenres::class)->findOneBy(array('genre' => $genre));

                if (is_null($genreDB)) {
                    $genreDB = new MovieGenres();
                    $genreDB->genre = $genre;
                    $this->EntityManager->persist($genreDB);
                    $this->EntityManager->flush();

                    $genreDB = $this->EntityManager->getRepository(MovieGenres::class)->findOneBy(array('genre' => $genre));
                }

                $genresDB = new MovieHasGenres();
                $genresDB->movieId = $genreDB->getId();
                $genresDB->genreId = $genreDB->getId();
                $this->EntityManager->persist($genresDB);
            }
        }

        $this->EntityManager->flush();
    }
}
