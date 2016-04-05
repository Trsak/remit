<?php
namespace Remit;

use Nette\Application\UI\Control;

class MovieCardControl extends Control
{
    public function render($movie)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/movieCard.latte');

        $template->movie = $movie;
        $template->render();
    }
}