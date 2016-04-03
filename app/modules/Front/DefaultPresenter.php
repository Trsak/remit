<?php

namespace Remit\Module\Front\Presenters;

class DefaultPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault() {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $this->template->moviesPopular = $client->getMoviesApi()->getPopular(array('language' => 'cs'));
        $this->template->moviesPlaying = $client->getMoviesApi()->getNowPlaying(array('language' => 'cs'));
    }
}
