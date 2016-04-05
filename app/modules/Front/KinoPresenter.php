<?php

namespace Remit\Module\Front\Presenters;

use IPub\VisualPaginator\Components as VisualPaginator;

class KinoPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public function actionDefault()
    {
        $token = new \Tmdb\ApiToken($this->context->parameters["movies"]["apiKey"]);
        $client = new \Tmdb\Client($token, ['secure' => false]);

        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 20;

        $page = 1;
        if (isset($paginator->page)) {
            $page = $paginator->getPage();
        }

        $movies = $client->getMoviesApi()->getUpcoming(array('language' => 'cs', 'page' => $page));

        $this->template->movies = $movies["results"];
        $this->template->moviesFrom = $movies["dates"]["minimum"];
        $this->template->moviesTo = $movies["dates"]["maximum"];
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
