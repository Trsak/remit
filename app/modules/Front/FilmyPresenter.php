<?php

namespace Remit\Module\Front\Presenters;

use Nette\Application\UI;

class FilmyPresenter extends \Remit\Module\Base\Presenters\BasePresenter
{
    public $csfdUrl = 'http://csfdapi.cz/movie?';

    public function actionPridat() //TODO: Nastylovat, přidat loading button state
    {
        $this->template->formSubmit = false;
        $this->template->moviesFound = [];
    }

    protected function createComponentAddFilmForm()
    {
        $form = new UI\Form;
        $form->addText('name', 'Zadejte název filmu')
            ->setRequired('Musíte zadat název filmu!');
        $form->addSubmit('search', 'Hledat');
        $form->onSuccess[] = array($this, 'addFilmFormSucceeded');
        $form->getElementPrototype()->novalidate('novalidate');
        $this->template->formSubmit = true;

        return $form;
    }

    public function addFilmFormSucceeded(UI\Form $form, $values)
    {
        $url = $this->csfdUrl . http_build_query(array(
                'search' => $values["name"],
            ));
        $this->template->moviesFound = json_decode(file_get_contents($url));
    }

    public function handleAddFilm($id)
    {

    }
}
