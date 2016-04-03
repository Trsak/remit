<?php

namespace Remit\Module\Front\Presenters;

class FilmPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault($id, $page = 'info')
    {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $this->template->movie = $client->getMoviesApi()->getMovie($id, array('language' => 'cs'));
        $this->template->movieVideos = $client->getMoviesApi()->getVideos($id);
        $this->template->movieImages = $client->getMoviesApi()->getImages($id);
        $this->template->movieAlternatives = $client->getMoviesApi()->getSimilar($id, array('language' => 'cs'));
        $this->template->movieCredits = $client->getMoviesApi()->getCredits($id, array('language' => 'cs'));

        $this->template->page = $page;
    }
}