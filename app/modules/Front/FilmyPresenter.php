<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI,
    IPub\VisualPaginator\Components as VisualPaginator;

class FilmyPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault($search = false, $name = '', $genre = '')
    {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 20;

        $this->template->filters = [];

        $page = 1;
        if (isset($paginator->page)) {
            $page = $paginator->getPage();
        }

        if (!$search) {
            $movies = $client->getMoviesApi()->getTopRated(array('page' => $page, 'language' => 'cs'));
        }
        else {
            if ($name) {
                $movies = $client->getSearchApi()->searchMovies($name, array('language' => 'cs'));
                $this->template->filters["name"] = $name;
            }
            elseif ($genre) {
                $movies = $client->getGenresApi()->getMovies($genre, array('language' => 'cs'));
                $this->template->filters["genre"] = $genre;
            }
        }

        $this->template->movies = $movies["results"];
        $this->template->moviesCount = $movies["total_results"];

        $paginator->itemCount = $movies["total_results"];
    }

    protected function createComponentVisualPaginator()
    {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();

        return $control;
    }
}
