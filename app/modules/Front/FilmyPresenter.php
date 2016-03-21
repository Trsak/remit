<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI;

class FilmyPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public $csfdUrl = 'http://csfdapi.cz/movie?';

    public function actionPridat() {
        $this->template->moviesFound = [];
    }

    protected function createComponentAddFilmForm()
    {
        $form = new UI\Form;
        $form->addText('name', 'Zadejte název filmu');
        $form->addSubmit('search', 'Hledat');
        $form->onSuccess[] = array($this, 'addFilmFormSucceeded');

        return $form;
    }

    public function addFilmFormSucceeded(UI\Form $form, $values)
    {
        $url = $this->csfdUrl . http_build_query(array(
                'search' => $values["name"],
            ));
        $this->template->moviesFound = json_decode(file_get_contents($url));
    }
}
