<?php
namespace Remit;

use Nette\Application\UI\Control;

class MovieCardControl extends Control
{
    private $genres = [];

    public function setGenres($genres)
    {
        $this->genres = $genres;
    }

    public function render($movie)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/movieCard.latte');

        $template->addFilter('genre', function ($id) {
            return $this->genres[$id];
        });

        $template->movie = $movie;
        $template->render();
    }
}